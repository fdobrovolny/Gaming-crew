<?php
class Authenticatecontroller{
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Objekt modelu
	 */
	private $model;
	
	/**
	 * Konstruktor řadiče
	 * @param Registry $registry objekt registru
	 * @param bool $directCall příznak přímého volání konstruktoru z frameworku
	 */
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		
		$urlBits = $this->registry->getObject('url')->getURLBits();
			if( isset( $urlBits[1] ) )
			{
				switch( $urlBits[1] )
				{
					case 'logout':
						$this->logout();
						break;
					case 'login':
						$this->login();
						break;
					case 'username':
						$this->forgotUsername();
						break;
					case 'password':
						$this->forgotPassword();
						break;
					case 'reset-password':
						$this->resetPassword( intval($urlBits[2]), $this->registry->getObject('db')->sanitizeData($urlBits[3]) );
						break;
					case 'register':
						$this->registrationDelegator();
						break;
				}
				
			}
		
	}
	
	private function forgotUsername()
	{
		if( isset( $_POST['email'] ) && $_POST['email'] != '' )
		{
			$e = $this->registry->getObject('db')->sanitizeData( $_POST['email'] );
			$sql = "SELECT * FROM users WHERE email='{$e}'";
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				// odeslání e-mailu uživateli
				$this->registry->getObject('mailout')->startFresh();
				$this->registry->getObject('mailout')->setTo( $_POST['email'] );
				$this->registry->getObject('mailout')->setSender( $this->registry->getSetting('adminEmailAddress') );
				$this->registry->getObject('mailout')->setFromName( $this->registry->getSetting('cms_name') );
				$this->registry->getObject('mailout')->setSubject( 'Uživatelské jméno pro ' .$this->registry->getSetting('sitename') );
				$this->registry->getObject('mailout')->buildFromTemplates('authenticate/username.tpl.php');
				$tags = $this->values;
				$tags[ 'sitename' ] = $this->registry->getSetting('sitename');
				$tags['username'] = $data['username'];
				$tags['siteurl'] = $this->registry->getSetting('site_url');
				$this->registry->getObject('mailout')->replaceTags( $tags );
				$this->registry->getObject('mailout')->setMethod('sendmail');
				$this->registry->getObject('mailout')->send();
				
				// informujeme uživatele, že jsme mu odeslali e-mail
				$this->registry->errorPage('Uživatelské jméno odesláno', 'Na Vaši e-mailovou adresu jsme odeslali Vaše uživatelské jméno');
				
			}
			else
			{
				// takový uživatel neexistuje
				$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/username/main.tpl.php', 'footer.tpl.php');
				$this->registry->getObject('template')->addTemplateBit('error_message', 'authenticate/username/error.tpl.php');
			}
		}
		else
		{
			// šablona formuláře
			$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/username/main.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('error_message', '');
		}
	}
	
	private function generateKey( $len = 7 )
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		// 36 znaků
		$tor = '';
		for( $i = 0; $i < $len; $i++ )
		{
			$tor .= $chars[ rand() % 35 ];
		}
		return $tor;
	}
	
	private function forgotPassword()
	{
		if( isset( $_POST['username'] ) && $_POST['username'] != '' )
		{
			$u = $this->registry->getObject('db')->sanitizeData( $_POST['username'] );
			$sql = "SELECT * FROM users WHERE username='{$u}'";
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				// vyžádal uživatel nedávno resetování hesla?
				if( $data['reset_expires'] > date('Y-m-d h:i:s') )
				{
					// zobrazení chyby
					$this->registry->errorPage('Chyba při generování požadavku na resetování hesla', 'Z bezpečnostních důvodů je možné vyžádat další resetování hesla až po uplynutí zadaného časového intervalu.');
				}
				else
				{
					// aktualizace záznamu
					$changes = array();
					$rk = $this->generateKey();
					$changes['reset_key'] = $rk;
					$changes['reset_expires'] = date( 'Y-m-d h:i:s', time()+86400 );
					$this->registry->getObject('db')->updateRecords( 'users', $changes, 'ID=' . $data['ID'] );
					// odeslání e-mailu uživateli
					$this->registry->getObject('mailout')->startFresh();
					$this->registry->getObject('mailout')->setTo( $_POST['email'] );
					$this->registry->getObject('mailout')->setSender( $this->registry->getSetting('adminEmailAddress') );
					$this->registry->getObject('mailout')->setFromName( $this->registry->getSetting('cms_name') );
					$this->registry->getObject('mailout')->setSubject( 'Resetování hesla pro ' .$this->registry->getSetting('sitename') );
					$this->registry->getObject('mailout')->buildFromTemplates('authenticate/password.tpl.php');
					$tags = $this->values;
					$tags[ 'sitename' ] = $this->registry->getSetting('sitename');
					$tags['username'] = $data['username'];
					$url = $this->registry->buildURL( array('authenticate', 'reset-password', $data['ID'], $rk) );
					$tags['url'] = $url;
					$tags['siteurl'] = $this->registry->getSetting('site_url');
					$this->registry->getObject('mailout')->replaceTags( $tags );
					$this->registry->getObject('mailout')->setMethod('sendmail');
					$this->registry->getObject('mailout')->send();
					
					// informujeme uživatele, že jsme mu odeslali e-mail
					$this->registry->errorPage('Odkaz pro resetování hesla odeslán', 'Na Vaši e-mailovou adresu jsme odeslali odkaz pro resetování Vašeho hesla');
				}
				
			}
			else
			{
				// takový uživatel neexistuje
				$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/password/main.tpl.php', 'footer.tpl.php');
				$this->registry->getObject('template')->addTemplateBit('error_message', 'authenticate/password/error.tpl.php');
			}
		}
		else
		{
			// šablona formuláře
			$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/password/main.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag('error_message', '');
		}
	}
	
	private function resetPassword( $user, $key )
	{
		$this->registry->getObject('template')->getPage()->addTag( 'user', $user );
		$this->registry->getObject('template')->getPage()->addTag('key', $key );
		$sql = "SELECT * FROM users WHERE ID={$user} AND reset_key='{$key}'";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() == 1 )
		{
			$data = $this->registry->getObject('db')->getRows();
			if( $data['reset_expiry'] > date('Y-m-d h:i:s') )
			{
				$this->registry->errorPage('Platnost odkazu vypršela', 'Odkazy pro resetování hesla mají platnost pouze 24 hodin. Platnost tohoto odkazu už vypršela.');
				
			}
			else
			{
				if( isset( $_POST['password'] ) )
				{
					if( strlen( $_POST['password'] ) < 6 )
					{
						$this->registry->errorPage( 'Heslo je příliš krátké', 'Zadané heslo je příliš krátké. Heslo musí mít alespoň 6 znaků.');
					}
					else
					{
						if( $_POST['password'] != $_POST['password_confirm'] )
						{
							$this->registry->errorPage( 'Heslo a jeho potvrzení se neshodují', 'Heslo a jeho potvrzení se neshodují, opakujte prosím zadání.');
						}
						else
						{
							// resetování hesla
							$changes = array();
							$changes['password_hash'] = md5( $_POST['passowrd'] );
							$this->registry->getObject('db')->updateRecords( 'users', $changes, 'ID=' . $user );
							$this->registry->errorPage('Heslo bylo změněno', 'Nové heslo bylo úspěšně nastaveno');
							
						}
					}
				}
				else
				{
					// zobrazení formuláře
					$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/password/reset.tpl.php', 'footer.tpl.php');
			
				}
			}
		}
		else
		{
			$this->registry->errorPage('Neplatný klíč', 'Zadaný klíč pro resetování hesla je neplatný');
		}
	}
	
	private function login()
	{
		// šablona
		if( $this->registry->getObject('authenticate')->isJustProcessed() )
		{
			
			if( isset( $_POST['login'] ) && $this->registry->getObject('authenticate')->isLoggedIn() == false )
			{
				// neplatné údaje	
				//$this->registry->getObject('template')->addTemplateBit('error_message', 'authenticate/login/error.tpl.php');
			}
			else
			{
				// přesměrování uživatele
				if( $_POST['referer'] == '' )
				{
					$referer = $this->registry->getSetting('siteurl');
					$this->registry->redirectUser( $referer, 'Úspěšné přihlášení', 'Úspěšně jste se přihlásili. Nyní budete přesměrování zpět na stránku, ze které jste přišli');
				}
				else
				{
					$this->registry->redirectUser( $_POST['referer'], 'Úspěšné přihlášení', 'Úspěšně jste se přihlásili. Nyní budete přesměrování zpět na stránku, ze které jste přišli');
				}
			}
		}
		else
		{
			if( $this->registry->getObject('authenticate')->isLoggedIn() == true )
			{
				$this->registry->errorPage( 'Již jste přihlášení', 'Již jste přihlášený pod uživatelským jménem <strong>' . $this->registry->getObject('authenticate')->getUser()->getUsername() . '</strong>');	
			}
			else
			{
				$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/login/main.tpl.php', 'footer.tpl.php' );
				$this->registry->getObject('template')->getPage()->addTag( 'referer', ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER']  : '' ) );
			}
		}
		
	}
	
	private function logout()
	{
		$this->registry->getObject('authenticate')->logout();
		$this->registry->getObject('template')->addTemplateBit('userbar', 'userbar.tpl.php');
		//$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'login.tpl.php', 'footer.tpl.php');
		$this->login();
	}
	
	/**
	 * Předá řízení registračnímu řadiči
	 * @return void
	 */
	private function registrationDelegator()
	{
		require_once FRAMEWORK_PATH . 'controllers/authenticate/registrationcontroller.php';
		$rc = new Registrationcontroller( $this->registry );
		
	}
	
	
}
		
		
?>