<?php

/**
 * Řadič registrace
 * Spravuje registrace uživatelů atd. 
 */
class Registrationcontroller{
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Standardní pole registrace
	 */
  private $fields = array( 'user' => 'uživatelské jméno', 'password' => 'heslo', 'password_confirm' => 'potvrzení hesla', 'email' => 'e-mailová adresa');
	
	/**
	 * Případné chyby v průběhu registrace
	 */
	private $registrationErrors = array();
	
	/**
	 * Pole tříd pro chybové hlášky - umožňují zvýraznit pole a indikovat tak chybu
	 */
	private $registrationErrorLabels = array();
	
	/**
	 * Hodnoty vyplněné uživatelem při registraci
	 */
	private $submittedValues = array();
	
	/**
	 * Očištěná verze hodnot zadaných uživatelem, připravených k uložení do databáze
	 */
	private $sanitizedValues = array();
	
	/**
	 * Má být nový účet uživatele ihned aktivní anebo je nutné ověření e-mailem? 
	 */
	private $activeValue = 1;
	
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
		require_once FRAMEWORK_PATH . 'controllers/authenticate/registrationcontrollerextention.php';
		$this->registrationExtention = new Registrationcontrollerextention( $this->registry );
		if( isset( $_POST['process_registration'] ) )
		{
			if( $this->checkRegistration() == true )
			{
				$userId = $this->processRegistration();
				if( $this->activeValue == 1 )
				{
					$this->registry->getObject('authenticate')->forceLogin( $this->submittedValues['register_user'], md5( $this->submittedValues['register_password'] ) );
				}
				$this->uiRegistrationProcessed();
			}
			else
			{
				$this->uiRegister( true );
			}
			
		}
		else
		{
			$this->uiRegister( false );
		}
	}
	
	/**
	 * Zajistí registraci uživatele a vytvoření jeho účtu a profilu
	 * @return int
	 */
	private function processRegistration()
	{
		// vložení záznamu do tabulky uživatelů
		$this->registry->getObject('db')->insertRecords( 'users', $this->sanitizedValues );
		// určení identifikátoru záznamu
		$uid = $this->registry->getObject('db')->lastInsertID();
		// vložení záznamu o profilu
		$this->registrationExtention->processRegistration( $uid );
		// vrátí identifikátor pro potřeby frameworku - např. pro automatické přihlášení
		return $uid;
	}
	
	private function checkRegistration()
	{
		$allClear = true;
		// prázdná pole
		foreach( $this->fields as $field => $name )
		{
			if( ! isset( $_POST[ 'register_' . $field ] ) || $_POST[ 'register_' . $field ] == '' )
			{
				$allClear = false;
				$this->registrationErrors[] = 'Vyplňte prosím pole ' . $name;
				$this->registrationErrorLabels['register_' . $field . '_label'] = 'error';
			}
		}
		
		// shoda hesel a jeho potvrzení
		if( $_POST[ 'register_password' ]!= $_POST[ 'register_password_confirm' ] )
		{
			$allClear = false;
			$this->registrationErrors[] = 'Heslo a jeho potvrzení se neshodují';
			$this->registrationErrorLabels['register_password_label'] = 'error';
			$this->registrationErrorLabels['register_password_confirm_label'] = 'error';
		}

		// délka hesla
		if( strlen( $_POST['register_password'] ) < 6 )
		{
			$allClear = false;
			$this->registrationErrors[] = 'Zadané heslo je příliš krátké, musí mít alespoň 6 znaků';
			$this->registrationErrorLabels['register_password_label'] = 'error';
			$this->registrationErrorLabels['register_password_confirm_label'] = 'error';
		}
		
		
		// nový řádek v e-mailu
		if( strpos( ( urldecode( $_POST[ 'register_email' ] ) ), "\r" ) !== false || strpos( ( urldecode( $_POST[ 'register_email' ] ) ), "\n" ) !== false )
		{
			$allClear = false;
			$this->registrationErrors[] = 'Zadaná e-mailová adresa není platná';
			$this->registrationErrorLabels['register_email_label'] = 'error';
		}
		
		// platný formát e-mailu
		if( ! preg_match( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})^", $_POST[ 'register_email' ] ) )
		{
			$allClear = false;
			$this->registrationErrors[] = 'Zadaná e-mailová adresa nemá platný formát';
			$this->registrationErrorLabels['register_email_label'] = 'error';

		}
		
		// souhlas s podmínkami 
		if( ! isset( $_POST['register_terms'] ) || $_POST['register_terms'] != 1 )
		{
			$allClear = false;
			$this->registrationErrors[] = 'Akceptujte prosím podmínky užití';
			$this->registrationErrorLabels['register_terms_label'] = 'error';
		}
		
		// kontrola dostupnosti uživatelského jména a e-mailové adresy
		$u = $this->registry->getObject('db')->sanitizeData( $_POST['register_user'] );
		$e = $this->registry->getObject('db')->sanitizeData( $_POST['register_email'] );
		$sql = "SELECT * FROM users WHERE username='{$u}' OR email='{$e}'";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() == 2 )
		{
			$allClear = false;
			// uživatelské jméno i e-mailová adresa jsou už obsazené	
			$this->registrationErrors[] = 'Zadané uživatelské jméno i e-mailová adresa jsou již obsazené';
			$this->registrationErrorLabels['register_user_label'] = 'error';
			$this->registrationErrorLabels['register_email_label'] = 'error';
		}
		elseif( $this->registry->getObject('db')->numRows() == 1 )
		{
			// je obsazené uživatelské jméno, e-mailová adresa anebo obojí
			$u = $this->registry->getObject('db')->sanitizeData( $_POST['register_user'] );
			$e = $this->registry->getObject('db')->sanitizeData( $_POST['register_email'] );
			$data = $this->registry->getObject('db')->getRows();
			if( $data['username'] == $u && $data['email'] == $e )
			{
				$allClear = false;
  			// uživatelské jméno i e-mailová adresa jsou už obsazené	
				$this->registrationErrors[] = 'Zadané uživatelské jméno i e-mailová adresa jsou již obsazené';
				$this->registrationErrorLabels['register_user_label'] = 'error';
				$this->registrationErrorLabels['register_email_label'] = 'error';
			}
			elseif( $data['username'] == $u )
			{
				$allClear = false;
				// uživatelské jméno je už obsazené	
				$this->registrationErrors[] = 'Zadané uživatelské jméno je už obsazené';
				$this->registrationErrorLabels['register_user_label'] = 'error';
				
			}
			else
			{
				$allClear = false;
				// e-mailová adresa je už obsazená	
				$this->registrationErrors[] = 'Zadaná e-mailová adresa je už obsazená';
				$this->registrationErrorLabels['register_email_label'] = 'error';
			}
		}
		
    // captcha
		if( $this->registry->getSetting('captcha.enabled') == 1 )
		{
			// kontrola
		}
		
		// rozšiřující modul
		if( $this->registrationExtention->checkRegistrationSubmission() == false )
		{
			$allClear = false;
		}
		
		if( $allClear == true )
		{
			$this->sanitizedValues['username'] = $u;
			$this->sanitizedValues['email'] = $e;
			$this->sanitizedValues['password_hash'] = md5( $_POST['register_password'] );
			$this->sanitizedValues['active'] = $this->activeValue;
			$this->sanitizedValues['admin'] = 0;
			$this->sanitizedValues['banned'] = 0;
			
			$this->submittedValues['register_user'] = $_POST['register_user'];
			$this->submittedValues['register_password'] = $_POST['register_password'];
			return true;
		}
		else
		{
			$this->submittedValues['register_user'] = $_POST['register_user'];
			$this->submittedValues['register_email'] = $_POST['register_email'];
			$this->submittedValues['register_password'] = $_POST['register_password'] ;
			$this->submittedValues['register_password_confirm'] = $_POST['register_password_confirm'] ;
			$this->submittedValues['register_captcha'] = ( isset( $_POST['register_captcha'] ) ? $_POST['register_captcha']  : '' );
			return false;
		}
		
		
		
	}
	
	private function uiRegistrationProcessed()
	{
		$this->registry->getObject('template')->getPage()->setTitle( 'Registrace na webu ' . $this->registry->getSetting('sitename') . ' byla úspěšně dokončena.');
		$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/register/complete.tpl.php', 'footer.tpl.php' );
		
	}
	
	private function uiRegister( $error )
	{
		$this->registry->getObject('template')->getPage()->setTitle( 'Registrace na webu ' . $this->registry->getSetting('sitename') );
		$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'authenticate/register/main.tpl.php', 'footer.tpl.php' );
		// vyprázdnění polí
		$fields = array_keys( $this->fields );
		$fields = array_merge( $fields, $this->registrationExtention->getExtraFields() );
		foreach( $fields as $field )
		{
			$this->registry->getObject('template')->getPage()->addTag( 'register_' . $field . '_label', '' );
			$this->registry->getObject('template')->getPage()->addTag( 'register_' . $field, '' );
		}
		if( $error == false )
		{
			$this->registry->getObject('template')->getPage()->addTag( 'error', '' );
		}
		else
		{
			$this->registry->getObject('template')->addTemplateBit( 'error', 'authenticate/register/error.tpl.php');
			$errorsData = array();
			$errors = array_merge( $this->registrationErrors, $this->registrationExtention->getRegistrationErrors() );
			foreach( $errors as $error )
			{
				$errorsData[] = array( 'error_text' => $error );
			}
			$errorsCache = $this->registry->getObject('db')->cacheData( $errorsData );
			$this->registry->getObject('template')->getPage()->addTag( 'errors', array( 'DATA', $errorsCache ) );
			$toFill = array_merge( $this->submittedValues, $this->registrationExtention->getRegistrationValues(), $this->registrationErrorLabels, $this->registrationExtention->getErrorLabels() );
			foreach( $toFill as $tag => $value )
			{
				$this->registry->getObject('template')->getPage()->addTag( $tag, $value );
			}
		}
	}
	
	
}



?>