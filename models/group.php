<?php
/**
 * Model skupiny
 */
class Group {
	
	/**
	 * Dostupné typy skupin
	 */
	private $types = array('public', 'private', 'private-member-invite', 'private-self-invite');
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Identifikátor skupiny
	 */
	private $id;
	
	/**
	 * Název skupiny
	 */
	private $name;
	
	/**
	 * Popis skupiny
	 */
	private $description;
	
	/**
	 * Identifikátor uživatele, který skupinu vytvořil
	 */
	private $creator;
	
	/**
	 * Jméno uživatele, který skupinu vytvořil
	 */
	private $creatorName;
	
	/**
	 * Časová známka vytvoření skupiny
	 */
	private $created;
	
	/**
	 * Uživatelsky přívětivá reprezentace času vytvoření skupiny
	 */
	private $createdFriendly;
	
	/**
	 * Typ skupiny
	 */
	private $type;
	
	/**
	 * Příznak aktivity skupiny
	 */
	private $active=1;
	
	/**
	 * Příznak platnosti skupiny
	 */
	private $valid;
	
	/**
	 * Konstruktor
	 * @param Registry $registry objekt registru
	 * @param int $id identifikátor skupiny
	 * @return void
	 */
	public function __construct( Registry $registry, $id=0 )
	{
		$this->registry = $registry;
		if( $id > 0 )
		{
			$this->id = $id;
			$sql = "SELECT g.*, DATE_FORMAT(g.created, '%d.%m.%Y') as created_friendly, p.name as creator_name FROM groups g, profile p WHERE p.user_id=g.creator AND g.ID=" . $this->id;
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->name = $data['name'];
				$this->description = $data['description'];
				$this->creator = $data['creator'];
				$this->valid = true;
				$this->active = $data['active'];
				$this->type = $data['type'];
				$this->created = $data['created'];
				$this->createdFriendly = $data['created_friendly'];
				$this->creator = $data['creator'];
				$this->creatorName = $data['creator_name'];	
			}
			else
			{
				$this->valid = false;
			}
		}
		else
		{
			$this->id = 0;
		}
	}
	
	/**
	 * Nastaví název skupiny
	 * @param String $name
	 * @return void
	 */
	public function setName( $name )
	{
		$this->name = $name;
	}
	
	/**
	 * Nastaví popis skupiny
	 * @param String $description popis
	 * @return void
	 */
	public function setDescription( $description )
	{
		$this->description = $description;
	}
	
	/**
	 * Nastaví autora skupiny
	 * @param int $creator
	 * @return void
	 */
	public function setCreator( $creator )
	{
		$this->creator = $creator;
	}
	
	/**
	 * Nastaví typ skupiny
	 * @param String $type
	 * @return void
	 */
	public function setType( $type )
	{
		if( in_array( $type, $this->types ) )
		{
			$this->type = $type;
		}
	}
	
	/**
	 * Uloží skupinu
	 * @return void
	 */
	public function save()
	{
		if( $this->id > 0 )
		{
			$update = array();
			$update['description'] = $this->description;
			$update['name'] = $this->name;
			$update['type'] = $this->type;
			$update['creator'] = $this->creator;
			$update['active'] = $this->active;
			$update['created'] = $this->created;
			$this->registry->getObject('db')->updateRecords( 'groups', $update, 'ID=' . $this->id );
		}
		else
		{
			$insert = array();
			$insert['description'] = $this->description;
			$insert['name'] = $this->name;
			$insert['type'] = $this->type;
			$insert['creator'] = $this->creator;
			$insert['active'] = $this->active;
			$this->registry->getObject('db')->insertRecords( 'groups', $insert );
			$this->id = $this->registry->getObject('db')->lastInsertID();
		}
	}
	
	/**
	 * Získá seznam témat spojených se skupinou
	 * @return int identifikátor mezipaměti
	 */
	public function getTopics()
	{
		$sql = "SELECT t.*, (SELECT COUNT(*) FROM posts po WHERE po.topic=t.ID) as posts, DATE_FORMAT(t.created, '%d.%m.%Y') as created_friendly, p.name as creator_name FROM topics t, profile p WHERE p.user_id=t.creator AND t.group=" . $this->id . " ORDER BY t.ID DESC";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá identifikátor skupiny
	 */
	public function getID()
	{
		return $this->id;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Převede informace o skupině na značky šablony
	 * @param String $prefix prefix značek šablony
	 * @return void
	 */
	public function toTags( $prefix='' )
	{
		foreach( $this as $field => $data )
		{
			if( ! is_object( $data ) && ! is_array( $data ) )
			{
				$this->registry->getObject('template')->getPage()->addTag( $prefix.$field, $data );
			}
		}
	}
	
	public function isValid()
	{
		return $this->valid;
	}
	
	public function isActive()
	{
		return $this->active;
	}
	
	public function getCreator()
	{
		return $this->creator;
	}
	
	
	
	
	
}




?>