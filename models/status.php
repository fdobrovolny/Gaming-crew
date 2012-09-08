<?php
/**
 * Model stavu
 */
class Status {
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Identifikátor stavu
	 */
	private $id;
	
	/**
	 * Uživatel, který vytvořil aktualizaci stavu / zprávu profilu
	 */
	private $poster;
	
	/**
	 * Profil, na kterém byla aktualizace / zpráva publikována
	 */
	private $profile;
	
	/**
	 * Identifikátor typu stavu
	 */
	private $type;
	
	/**
	 * Samotná aktualizace / zpráva
	 */
	private $update;
	
	/**
	 * Označení typu stavu
	 */
	private $typeReference = 'update';
		
	/**
	 * Konstruktor
	 * @param Registry $registry objekt registru
	 * @param int $id identifikátor stavové aktualizace / zprávy
	 * @return void
	 */
	public function __construct( Registry $registry, $id=0 )
	{
		$this->registry = $registry;
		$this->id = 0;
	}
	
	/**
	 * Nastaví odesilatele stavu / zprávy
	 * @param int $poster identifikátor uživatele
	 * @return void
	 */
	public function setPoster( $poster )
	{
		$this->poster = $poster;
	}
	
	/**
	 * Nastaví profil, kde se má stav / zpráva publikovat
	 * @param int $profile identifikátor profilu
	 * @return void
	 */
	public function setProfile( $profile )
	{
		$this->profile = $profile;
	}
	
	/**
	 * Nastaví obsah stavu / zprávy
	 * @param String $status obsah
	 * @return void
	 */
	public function setStatus( $status )
	{
		$this->status = $status;
	}
	
	/**
	 * Nastaví typ stavu / zprávy
	 * @param int $type identifikátor typu
	 * @return void
	 */
	public function setType( $type )
	{
		$this->type = $type;
	}
	
	/**
	 * Nastaví označení typu, na jehož základě je možné určit identifikátor typu z databáze
	 * @param String $typeReference označení typu
	 * @return void
	 */
	public function setTypeReference( $typeReference )
	{
		$this->typeReference = $typeReference;
	}
	
	/**
	 * Získá identifikátor typu na základě označení typu
	 * @return void
	 */
	public function generateType()
	{
		$sql = "SELECT * FROM status_types WHERE type_reference='{$this->typeReference}'";
		$this->registry->getObject('db')->executeQuery( $sql );
		$data = $this->registry->getObject('db')->getRows();
		$this->type = $data['ID'];
	}
	
	/**
	 * Uloží stav / zprávu profilu
	 * @return void
	 */
	public function save()
	{
		if( $this->id == 0 )
		{
			$insert = array();
			$insert['update'] = $this->status;
			$insert['type'] = $this->type;
			$insert['poster'] = $this->poster;
			$insert['profile'] = $this->profile;
			$this->registry->getObject('db')->insertRecords( 'statuses', $insert );
			$this->id = $this->registry->getObject('db')->lastInsertID();
		}
	}
	
	public function getID()
	{
		return $this->id;
	}
}


?>