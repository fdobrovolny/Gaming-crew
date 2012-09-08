<?php

/**
 * Třída stavu s odkazem
 * rozšiřuje základní třídu stavu
 */
class Linkstatus extends status {
	
	private $url;
	private $description;
		
	/**
	 * Konstruktor
	 * @param Registry $registry
	 * @param int $id
	 * @return void
	 */
	public function __construct( Registry $registry, $id = 0 )
	{
		$this->registry = $registry;
		parent::__construct( $this->registry, $id );
		parent::setTypeReference('link');
	}
	
	/**
	 * Nastaví adresu URL odkazu
	 * @param String $url 
	 * @return void
	 */
	public function setURL( $url )
	{
		$this->url = $url;
	}
	
	/**
	 * Nastaví popis odkazu
	 * @param String $description
	 * @return void
	 */
	public function setDescription( $description )
	{
		$this->description = $description;
	}
	
	/**
	 * Uloží stav s odkazem
	 * @return void
	 */
	public function save()
	{
		// vloží záznam do základní tabulky stavů
		parent::save();
		// získá identifikátor vloženého záznamu
		$id = $this->getID();
		// vložení záznamu do tabulky stavů s odkazem
		$extended = array();
		$extended['id'] = $id;
		$extended['URL'] = $this->url;
		$extended['description'] = $this->description;
		$this->registry->getObject('db')->insertRecords( 'statuses_links', $extended );
	}
	
}

?>