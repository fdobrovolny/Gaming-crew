<?php
/**
 * Model příspěvku
 */
class Post{
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Identifikátor příspěvku
	 */
	private $id;
	
	/**
	 * Identifikátor uživatele, který příspěvek vytvořil
	 */
	private $creator;
	
	/**
	 * Jméno uživatele, který příspěvek vytvořil
	 */
	private $creatorName;
	
	/**
	 * Časová známka vytvoření příspěvku
	 */
	private $created;
	
	/**
	 * Uživatelsky přívětivá reprezentace času vytvoření příspěvku
	 */
	private $createdFriendly;
	
	/**
	 * Identifikátor tématu, ke kterému se příspěvek vztahuje
	 */
	private $topic;
	
	/**
	 * Samotný příspěvek
	 */
	private $post;
	
	/**
	 * Konstruktor
	 * @param Registry $registry objekt registru
	 * @param int $id identifikátor příspěvku
	 * @return void
	 */
	public function __construct( Registry $registry, $id=0 )
	{
		$this->registry = $registry;
		$this->id = $id;
		if( $this->id > 0 )
		{
			$sql = "SELECT p.*, DATE_FORMAT(p.created, '%d.%m.%Y') as created_friendly, pr.name as creator_name FROM posts p, profile pr WHERE pr.user_id=p.creator AND p.ID=" . $this->id;
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() > 0 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->creator = $data['creator'];
				$this->creatorName = $data['creator_name'];
				$this->createdFriendly = $data['created_friendly'];
				$this->topic = $data['topic'];
				$this->post = $data['post'];
				
			}
			else
			{
				$this->id = 0;
			}
		}
	}
	
	/**
	 * Nastaví autora příspěvku
	 * @param int $c identifikátor uživatele
	 * @return void
	 */
	public function setCreator( $c )
	{
		$this->creator = $c;
	}
	
	/**
	 * Nastaví téma, ke kterému se příspěvek vztahuje
	 * @param int $t identifikátor tématu
	 * @return void
	 */
	public function setTopic( $t )
	{
		$this->topic = $t;
	}
	
	/**
	 * Nastaví obsah příspěvku
	 * @param String $p obsah příspěvku
	 * @return void
	 */
	public function setPost( $p )
	{
		$this->post = $p;
	}
	
	/**
	 * Uloží příspěvek do databáze
	 * @return void
	 */
	public function save()
	{
		if( $this->id > 0 )
		{
			$update = array();
			$update['topic'] = $this->topic;
			$update['post'] = $this->post;
			$update['creator'] = $this->creator;
			$this->registry->getObject('db')->updateRecords( 'posts', $update, 'ID=' . $this->id );
		}
		else
		{
			$insert = array();
			$insert['topic'] = $this->topic;
			$insert['post'] = $this->post;
			$insert['creator'] = $this->creator;
			$this->registry->getObject('db')->insertRecords( 'posts', $insert );
			$this->id = $this->registry->getObject('db')->lastInsertID();
		}
		
	}
	
	
}



?>