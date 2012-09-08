<?php

class Relationship{
	
	private $registry;
	private $usera;
	private $userb;
	private $accepted;
	private $id = 0;
	private $type;
	
	/**
	 * Konstruktor vztahu
	 * @param Registry $registry objekt registru
	 * @param int $id identifikátor vztahu
	 * @param int $usera identifikátor uživatele a
	 * @param int $userb identifikátor uživatele b
	 * @param bool $approved příznak schválení vztahu
	 * @param int $type identifikátor typu vztahu
	 * @return void
	 */
	public function __construct( Registry $registry, $id=0, $usera, $userb, $approved=0, $type=0 )
	{
		$this->registry = $registry;
		// pokud není identifikátor zadaný, vytvoří se nový vztah
		if( $id == 0 )
		{
			$this->createRelationship( $usera, $userb, $approved, $type );
		}
		else
		{
			// identifikátor je zadaný, nastavíme hodnoty vlastností na základě údajů z databáze
			$sql = "SELECT * FROM relationships WHERE ID=" . $id;
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->populate( $data['ID'], $data['usera'], $data['userb'], $data['type'], $data['accepted'] );
			}
			
		}
	}
	
	/**
	 * Vytvoří nový vztah pokud neexistuje, v opačném případě nastaví hodnoty vlastností
	 */
	public function createRelationship( $usera, $userb, $approved=0, $type=0 )
	{
		// ověření existence vztahu
		$sql = "SELECT * FROM relationships WHERE (usera={$usera} AND userb={$userb}) OR (usera={$userb} AND userb={$usera})";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() == 1 )
		{
			// vztah existuje, nastavíme hodnoty vlastností
			$data = $this->registry->getObject('db')->getRows();
			$this->populate( $data['ID'], $data['usera'], $data['userb'], $data['type'], $data['accepted'] );
		}
		else
		{
			// vztah neexistuje
			if( $type != 0 )
			{
				// jedná se o oboustranný vztah?
				$sql = "SELECT * FROM relationship_types WHERE ID=" . $type;
				$this->registry->getObject('db')->executeQuery( $sql );
				if( $this->registry->getObject('db')->numRows() == 1 )
				{
					$data = $this->registry->getObject('db')->getRows();
					// vztahy, které nejsou oboustranné, se automaticky schválí
					if( $data['mutual'] == 0 )
					{
						$approved = 1;
					}
				}
				$this->accepted = $approved;
				// vytvoření vztahu
				$insert = array();
				$insert['usera'] = $usera;
				$insert['userb'] = $userb;
				$insert['type'] = $type;
				$insert['accepted'] = $approved;
				$this->registry->getObject('db')->insertRecords( 'relationships', $insert );
				$this->id = $this->registry->getObject('db')->lastInsertID();
			}
		}
		
	}
	
	/**
	 * Schválí vztah
	 * @return void
	 */
	public function approveRelationship()
	{
		$this->accepted = true;
	}
	
	
	/** 
	 * Odstraní vztah
	 * @return void
	 */
	public function delete()
	{
		$this->registry->getObject('db')->deleteRecords( 'relationships', 'ID=' . $this->id, 1 );
		$this->id = 0;
	}
	
	/**
	 * Uloží vztah
	 * @return void
	 */
	public function save()
	{
		$changes = array();
		$changes['usera'] = $this->usera;
		$changes['userb'] = $this->userb;
		$changes['type'] = $this->type;
		$changes['accepted'] = $this->accepted;
		$this->registry->getObject('db')->updateRecords( 'relationships', $changes, "ID=" . $this->id );
	}
	
	/** 
	 * Nastaví hodnoty vlastností vztahového objektu
	 * @param int $id identifikátor uživatele
	 * @param int $usera uživatel a
	 * @param int $userb uživatel b
	 * @param int $type typ
	 * @param bool $approved příznak schválení 
	 * @return void
	 */
	private function populate( $id, $usera, $userb, $type, $approved )
	{
		$this->id = $id;
		$this->type = $type;
		$this->usera = $usera;
		$this->userb = $userb;
		$this->accepted = $approved;
	}
	
	public function isApproved()
	{
		return $this->accepted;
	}
	
	public function getUserB()
	{
		return $this->userb;
	}
}

?>