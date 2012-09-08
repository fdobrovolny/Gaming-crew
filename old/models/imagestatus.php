<?php

/**
 * Třída stavu s obrázkem
 * rozšiřuje základní třídu stavu
 */
class Imagestatus extends status {
	
	private $image;
	
	/**
	 * Konstruktor
	 * @param Registry $registry
	 * @param int $id
	 * @return void
	 */
	public function __construct( Registry $registry, $id = 0 )
	{
		$this->registry = $registry;
		parent::setTypeReference('image');
		parent::__construct( $this->registry, $id );
	}
	
	/**
	 * Zpracuje nahraný obrázek a uloží jeho název do vlastnosti image
	 * @param String $postfield klíč, pod kterým je obrázek přístupný v poli _FILES
	 * @return boolean
	 */
	public function processImage( $postfield )
	{
		require_once( FRAMEWORK_PATH . 'lib/images/imagemanager.class.php' );
		$im = new Imagemanager();
		$prefix = time() . '_';
		if( $im->loadFromPost( $postfield, $this->registry->getSetting('upload_path') . 'statusimages/', $prefix ) )
		{
			$im->resizeScaleWidth( 150 );
			$im->save( $this->registry->getSetting('upload_path') . 'statusimages/' . $im->getName() );
			$this->image = $im->getName();
			return true;
		}
		else
		{
			return false;
		}
		
	}
	
	/**
	 * Uloží stav s obrázkem
	 * @return void
	 */
	public function save()
	{
		// vloží záznam do základní tabulky stavů
		parent::save();
		// získá identifikátor vloženého záznamu
		$id = $this->getID();
		// vložení záznamu do tabulky stavů s obrázkem
		$extended = array();
		$extended['id'] = $id;
		$extended['image'] = $this->image;
		$this->registry->getObject('db')->insertRecords( 'statuses_images', $extended );
	}
	
}

?>