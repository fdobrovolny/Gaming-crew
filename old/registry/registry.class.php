<?php
/**
 * Sociální síť v PHP
 * @author Michael Peacock 
 * Třída Registry 
 */
class Registry {
	
	/**
	 * Pole objektů 
	 */
	private $objects;
	
	/**
	 * Pole nastavení
	 */
	private $settings;
	
    public function __construct() {
    }
    
    /**
     * Vytvoří nový objekt a uloží ho do registru 
     * @param String $object prefix objektu 
     * @param String $key klíč, pod kterým bude objekt přístupný
     * @return void 
     */
    public function createAndStoreObject( $object, $key )
    {
    	require_once( $object . '.class.php' );
    	$this->objects[ $key ] = new $object( $this );
    }
    
    /**
     * Získá objekt z registru
     * @param String $key klíč v poli objektů
     * @return Object 
     */
    public function getObject( $key )
    {
    	return $this->objects[ $key ];
    }
    
    /**
     * Uloží nastavení
     * @param String $setting data 
     * @param String $key klíč v poli nastavení
     * @return void 
     */
    public function storeSetting( $setting, $key )
    {
    	$this->settings[ $key ] = $setting;
    }
    
    /**
     * Získá nastavení z registru 
     * @param String $key klíč v poli nastavení 
     * @return String nastavení
     */
    public function getSetting( $key )
    {
    	return $this->settings[ $key ];
    }
    
    public function errorPage( $heading, $content )
    {
    	$this->getObject('template')->buildFromTemplates('header.tpl.php', 'message.tpl.php', 'footer.tpl.php');
    	$this->getObject('template')->getPage()->addTag( 'heading', $heading );
    	$this->getObject('template')->getPage()->addTag( 'content', $content );
    }
        
    /**
     * Sestaví adresu URL
     * @param array $urlBits pole částí adresy
     * @param array $queryString parametry, které jsou součástí dotazu
     * @return String
     */
    public function buildURL( $urlBits, $queryString='' )
    {
    	return $this->getObject('url')->buildURL( $urlBits, $queryString, false );
    }
    
    /**
     * Přesměruje uživatele na novou lokaci a zobrazí průběžnou informační hlášku
     * @param String $url adresa URL, na kterou se má uživatel přesměrovat
     * @param String $heading nadpis zprávy
     * @param String $message obsah zprávy
     * @return void
     */
    public function redirectUser( $url, $heading, $message )
    {
    	$this->getObject('template')->buildFromTemplates('redirect.tpl.php');
    	$this->getObject('template')->getPage()->addTag( 'heading', $heading );
    	$this->getObject('template')->getPage()->addTag( 'message', $message );
    	$this->getObject('template')->getPage()->addTag( 'url', $url );
    	
    }   

}

?>