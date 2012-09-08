<?php

/**
 * Řadič profilových informací
 */
class Profileinformationcontroller {
	
	/**
	 * Konstruktor
	 * @param Registry $registry objekt registru
	 * @param boolean $directCall příznak přímého volání konstruktoru frameworkem
	 * @param int $user identifikátor uživatele
	 * @return void
	 */
	public function __construct( $registry, $directCall=true, $user )
	{
		$this->registry = $registry;
		$urlBits = $this->registry->getObject('url')->getURLBits();
		if( isset( $urlBits[3] ) )
		{
			switch( $urlBits[3] )
			{
				case 'edit':
					$this->editProfile();
					break;
				default:
					$this->viewProfile( $user );
					break;
			}	
		}
		else
		{
			$this->viewProfile( $user );
		}
		
	}
	
	/**
	 * Zobrazí profilové informace
	 * @param int $user identifikátor uživatele
	 * @return void
	 */
	private function viewProfile( $user )
	{
		// načtení šablony
		$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'profile/information/view.tpl.php', 'footer.tpl.php' );
		// načtení profilových informací a jejich vložení do šablony
		require_once( FRAMEWORK_PATH . 'models/profile.php' );
		$profile = new Profile( $this->registry, $user );
		$profile->toTags( 'p_' ); 
	}
	
/**
 * Upraví profil
 * @return void
 */
private function editProfile()
{
	if( $this->registry->getObject('authenticate')->isLoggedIn() == true )
	{
		$user = $this->registry->getObject('authenticate')->getUser()->getUserID();
		if( isset( $_POST ) && count( $_POST ) > 0 )
		{
			// uživatel odeslal editační formulář
			$profile = new Profile( $this->registry, $user );
			$profile->setBio( $this->registry->getObject('db')->sanitizeData( $_POST['bio'] ) );
			$profile->setName( $this->registry->getObject('db')->sanitizeData( $_POST['name'] ) );
			$profile->setDinoName( $this->registry->getObject('db')->sanitizeData( $_POST['dino_name'] ) );
			$profile->setDinoBreed( $this->registry->getObject('db')->sanitizeData( $_POST['dino_breed'] ) );
			$profile->setDinoGender( $this->registry->getObject('db')->sanitizeData( $_POST['dino_gender'] ), false );
			$profile->setDinoDOB( $this->registry->getObject('db')->sanitizeData( $_POST['dino_dob'] ) );
			if( $_FILES['profile_picture']['size'] )
			{
				require_once( FRAMEWORK_PATH . 'lib/images/imagemanager.class.php' );
				$im = new Imagemanager();
				$im->loadFromPost( 'profile_picture', $this->registry->getSetting('upload_path') .'profile/', time() );
				if( $im == true )
				{
					$im->resizeScaleHeight( 150 );
					$im->save( $this->registry->getSetting('upload_path') .'profile/' . $im->getName() );
					$profile->setPhoto( $im->getName() );
				}
			}
			$profile->save();
			$this->registry->redirectUser( $this->registry->buildURL(array('profile', 'view', $profile->getID(), 'edit')), 'Profil uložen', 'Změny provedené v profilu byly úspěšně uloženy');
		}
		else
		{
			// zobrazení editačního formuláře
			$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'profile/information/edit.tpl.php', 'footer.tpl.php' );
			// vyplnění polí formuláře daty z profilu
			require_once( FRAMEWORK_PATH . 'models/profile.php' );
			$profile = new Profile( $this->registry, $user );
			$profile->toTags( 'p_' ); 
		}
	}
	else
	{
		$this->registry->errorPage('Přihlaste se prosím', 'Pouze přihlášení uživatelé mohou editovat svůj profil');
	}
}
	
}


?>