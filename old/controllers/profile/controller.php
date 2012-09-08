<?php

/**
 * Řadič profilu
 * Předává řízení řadičům profilu oddělujícím jednotlivé funkce profilu
 */
class Profilecontroller {
	
	/**
	 * Konstruktor
	 * @param Object $registry objektu registru
	 * @param bool $directCall příznak přímého přístupu k řadiči
	 */
	public function __construct( $registry, $directCall=true )
	{
		$this->registry = $registry;
		
		$urlBits = $this->registry->getObject('url')->getURLBits();
		switch( isset( $urlBits[1] ) ? $urlBits[1] : '' )
		{
			case 'view':
				$this->staticContentDelegator( intval( $urlBits[2] ) );
				break;
			case 'statuses':
				$this->statusesDelegator( intval( $urlBits[2] ) );
				break;
			default:				
				if ($this->registry->getObject('authenticate')->isLoggedIn()) 
        {
          $this->staticContentDelegator( $this->registry->getObject('authenticate')->getUser()->getUserID() );
        }
        else 
        {
          $this->profileError();
        }
				break;
		}	
	}
	
	/**
	 * Předá řízení řadiči statického obsahu profilu
	 * @param int $user identifikátor uživatele, jehož profil se má zobrazit
	 * @return void
	 */
	private function staticContentDelegator( $user )
	{
		$this->commonTemplateTags( $user );
		require_once( FRAMEWORK_PATH . 'controllers/profile/profileinformationcontroller.php' );
		$sc = new Profileinformationcontroller( $this->registry, true, $user );	
	}
	
	/**
	 * Předá řízení řadiči stavových informací profilu
	 * @param int $user identifikátor uživatele, jehož profil se má zobrazit
	 * @return void
	 */
	private function statusesDelegator( $user )
	{
		$this->commonTemplateTags( $user );
		require_once( FRAMEWORK_PATH . 'controllers/profile/profilestatusescontroller.php' );
		$sc = new Profilestatusescontroller( $this->registry, true, $user );	
	}
	
	/**
	 * Zobrazí chybovou hlášku - k profilům není možné přímo přistupovat přes profile/
	 * @return void
	 */
	private function profileError()
	{
		$this->registry->errorPage( 'Došlo k chybě', 'Zadaný odkaz je neplatný, zkuste to prosím znovu');
	}
	
/**
 * Nahradí značky šablony společné pro všechny části profilu
 * @param int $user identifikátor uživatele
 * @return void
 */
private function commonTemplateTags( $user )
{
	// získání náhodného vzorku 6ti přátel
	require_once( FRAMEWORK_PATH . 'models/relationships.php' );
	$relationships = new Relationships( $this->registry );
	$cache = $relationships->getByUser( $user, true, 6 );
	$this->registry->getObject('template')->getPage()->addTag( 'profile_friends_sample', array( 'SQL', $cache ) );
	
	// získání jméno a fotografii uživatele
	require_once( FRAMEWORK_PATH . 'models/profile.php' );
	$profile = new Profile( $this->registry, $user );
	$name = $profile->getName();
	$photo = $profile->getPhoto(); 
	$uid = $profile->getID();
	$this->registry->getObject('template')->getPage()->addTag( 'profile_name', $name );
	$this->registry->getObject('template')->getPage()->addTag( 'profile_photo', $photo );
	$this->registry->getObject('template')->getPage()->addTag( 'profile_user_id', $uid );
	// úklid
	$profile = "";
}

	
}



?>