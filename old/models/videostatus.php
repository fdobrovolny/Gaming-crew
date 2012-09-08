<?php

/**
 * Třída stavu s videem
 * rozšiřuje základní třídu stavu
 */
class Videostatus extends status {
	
	private $video_id;
	
	/**
	 * Konstruktor
	 * @param Registry $registry
	 * @param int $id
	 * @return void
	 */
	public function __construct( Registry $registry, $id = 0 )
	{
		$this->registry = $registry;
		parent::setTypeReference('video');
		parent::__construct( $this->registry, $id );
	}
	
	public function setVideoId( $vid )
	{
		$this->video_id = $vid;
	}
	
	public function setVideoIdFromURL( $url )
	{
		$data = array();
		parse_str( parse_url($url, PHP_URL_QUERY), $data );
		$this->video_id = $this->registry->getObject('db')->sanitizeData( isset( $data['v'] ) ? $data['v'] : '7NzzzcOWPH0' );
	}
	
	/**
	 * Uloží stav s videem
	 * @return void
	 */
	public function save()
	{
		// vloží záznam do základní tabulky stavů
		parent::save();
		// získá identifikátor vloženého záznamu
		$id = $this->getID();
		// vložení záznamu do tabulky stavů s videem
		$extended = array();
		$extended['id'] = $id;
		$extended['video_id'] = $this->video_id;
		$this->registry->getObject('db')->insertRecords( 'statuses_videos', $extended );
	}
	
}

?>