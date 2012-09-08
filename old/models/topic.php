<?php
/**
 * Model tématu
 */
class Topic {

	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Identifikátor tématu
	 */
	private $id=0;
	
	/**
	 * Identifikátor uživatele, který téma vytvořil
	 */
	private $creator;
	
	/**
	 * Jméno uživatele, který téma vytvořil
	 */
	private $creatorName;
	
	
	/**
	 * Název tématu
	 */
	private $name;
	
	/**
	 * Časová známka vytvoření tématu
	 */
	private $created;
	
	/**
	 * Uživatelsky přívětivá reprezentace času vytvoření tématu
	 */
	private $createdFriendly;
	
	/**
	 * Počet příspěvků v tématu
	 */
	 private $numPosts;
	
	/**
	 * Příznak uložení prvního příspěvku
	 */
	private $includeFirstPost;
	
	/**
	 * Objekt příspěvku
	 */
	private $post;
	
	/**
	 * Skupina, do které téma patří
	 */
	private $group;
	
	
	/**
	 * Konstruktor
	 * @param Registry $registry objekt registru
	 * @param int $id identifikátor tématu
	 * @return void
	 */
	public function __construct( Registry $registry, $id=0 )
	{
		$this->registry = $registry;
		$this->id = $id;
		if( $this->id > 0 )
		{
			$sql = "SELECT t.*, (SELECT COUNT(*) FROM posts po WHERE po.topic=t.ID) as posts, DATE_FORMAT(t.created, '%d.%m.%Y') as created_friendly, p.name as creator_name FROM topics t, profile p WHERE p.user_id=t.creator AND t.ID=" . $this->id;
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() > 0 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->creator = $data['creator'];
				$this->creatorName = $data['creator_name'];
				$this->createdFriendly = $data['created_friendly'];
				$this->name = $data['name'];
				$this->numPosts = $data['posts'];
				$this->group = $data['group'];
				
			}
			else
			{
				$this->id = 0;
			}
		}
	}
	
	/**
	 * Získá dotaz pro určení příspěvků tématu
	 */
	public function getPostsQuery()
	{
		$sql = "SELECT p.*, DATE_FORMAT(p.created, '%d.%m.%Y') as friendly_created_post, pr.name as creator_friendly_post FROM posts p, profile pr WHERE pr.user_id=p.creator AND p.topic=" . $this->id;
		return $sql;
	}
	
	/**
	 * Vytvoří objekt prvního příspěvku a nastaví příznak pro jeho uložení 
	 * @param bool $ifp
	 * @return void
	 */
	public function includeFirstPost( $ifp )
	{
		$this->includeFirstPost = $ifp;
		require_once( FRAMEWORK_PATH . 'models/post.php' );
		$this->post = new Post( $this->registry, 0 );
	}
	
	/**
	 * Získá objekt prvního příspěvku
	 * @return Object
	 */
	public function getFirstPost()
	{
		return $this->post;
	}
	
	/**
	 * Nastaví skupinu, do které téma patří
	 * @param int $group
	 * @return void
	 */
	public function setGroup( $group )
	{
		$this->group = $group;
	}
	
	
	/**
	 * Nastaví autora tématu
	 * @param int $creator
	 * @return void
	 */
	public function setCreator( $creator )
	{
		$this->creator = $creator;	
	}
	
	/**
	 * Nastaví název tématu
	 * @param String $name
	 * @return void
	 */
	public function setName( $name )
	{
		$this->name = $name;
	}
	
	
	/**
	 * Uloží téma do databáze
	 * @return void
	 */
	public function save()
	{
		if( $this->id > 0 )
		{
			$update = array();
			$update['creator'] = $this->creator;
			$update['name'] = $this->name;
			$update['group'] = $this->group;
			$this->registry->getObject('db')->updateRecords( 'topics', $update, 'ID=' . $this->id );
		}
		else
		{
			$insert = array();
			$insert['creator'] = $this->creator;
			$insert['name'] = $this->name;
			$insert['group'] = $this->group;
			$this->registry->getObject('db')->insertRecords( 'topics', $insert );
			$this->id = $this->registry->getObject('db')->lastInsertID();
			if( $this->includeFirstPost == true )
			{
				$this->post->setTopic( $this->id );
				$this->post->save();
			}
		}
	}
	
	/**
	 * Získá název tématu
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Převede informace o tématu na značky šablony
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
	
	/**
	 * Získá skupinu, do které téma patří
	 * @return int
	 */
	public function getGroup()
	{
		return $this->group;
	}
	
	/**
	 * Odstraní téma
	 * @return boolean
	 */
	public function delete()
	{
		$sql = "DELETE FROM topics WHERE ID=" . $this->id;
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->affectedRows() > 0 )
		{
			$sql = "DELETE FROM posts WHERE topic=" . $this->id;
			$this->registry->getObject('db')->executeQuery( $sql );
			$this->id =0;
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	
}


?>