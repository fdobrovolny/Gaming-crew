<?php
/**
 * Model členství ve skupině
 */
class Groupmembership{
	
	/**
	 * Identifikátor členství
	 */
	private $id;
	
	private $valid = false;
	
	/**
	 * Identifikátor uživatele
	 */
	private $user;
	
	/**
	 * Identifikátor skupiny
	 */
	private $group;
	
	/**
	 * Příznak schválení členství
	 */
	private $approved = 0;
	
	/**
	 * Příznak pozvání uživatele
	 */
	private $invited;
	
	/**
	 * Příznak vyžádání členství uživatelem
	 */
	private $requested;
	
	/**
	 * Datum pozvání uživatele do skupiny
	 */
	private $invitedDate;
	
	/**
	 * Datum požadavku na členství
	 */
	private $requestedDate;
	
	/**
	 * Datum přidání do skupiny
	 */
	private $joinDate;
	
	/**
	 * Identifikátor uživatele, který nového člena pozval
	 */
	private $inviter;
	
	/**
	 * Konstruktor
	 * @param Registry $registry
	 * @param int $id
	 * @return void
	 */
	public function __construct( Registry $registry, $id=0 )
	{
		$this->registry = $registry;
		if( $id > 0 )
		{
			$sql = "SELECT * FROM group_membership WHERE ID={$id} LIMIT 1";
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$this->valid = true;
				$data = $this->registry->getObject('db')->getRows();
				$this->approved = $data['approved'];
				$this->invited = $data['invited'];
				$this->requested = $data['requested'];	
				$this->invitedDate = $data['invited_date'];
				$this->requestedDate = $data['requested_date'];
				$this->joinDate = $data['join_date'];
				$this->inviter = $data['inviter'];
			}
		}
		else
		{
			$this->id = 0;
		}
	}
	
	public function getID()
	{
		return $this->id;
	}
	
	/**
	 * Získá informace o členství uživatele ve skupině
	 * @param int $user
	 * @param int $group
	 * @return void
	 */
	public function getByUserAndGroup( $user, $group )
	{
		$this->user = $user;
		$this->group = $group;
		$sql = "SELECT * FROM group_membership WHERE user={$user} AND `group`={$group} LIMIT 1";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() == 1 )
		{
			$data = $this->registry->getObject('db')->getRows();
			$this->valid = true;
			$this->approved = $data['approved'];
			$this->invited = $data['invited'];
			$this->requested = $data['requested'];	
		}
		
	}
	
	public function isValid()
	{
		return $this->valid;
	}
	
	/**
	 * Získá příznak schválení členství
	 * @return boolean
	 */
	public function getApproved()
	{
		return $this->approved;
	}
	
	/**
	 * Získá příznak pozvání uživatele
	 * @return boolean
	 */
	public function getInvited()
	{
		return $this->invited;
	}
	
	/**
	 * Získá příznak požadavku uživatele na členství
	 * @return boolean
	 */
	public function getRequested()
	{
		return $this->requested;
	}
	
	
	/**
	 * Získá identifikátor uživatele, který pozval nového člena do skupiny
	 * @return int
	 */
	public function getInviter()
	{
		return $this->inviter;
	}
	
	/**
	 * Nastaví příznak schválení členství
	 * @param boolean $approved
	 * @return void
	 */
	public function setApproved( $approved )
	{
		$this->approved = $approved;
	}
	
	/**
	 * Nastaví příznak vyžádání členství uživatelem
	 * @param boolean $requested
	 * @return void
	 */
	public function setRequested( $requested )
	{
		$this->requested = $requested;
	}
	
	/**
	 * Nastaví příznak pozvání uživatele
	 * @param boolean $invited
	 * @return void
	 */
	public function setInvited( $invited )
	{
		$this->invited = $invited;
	}
	
	/**
	 * Nastaví identifikátor uživatele, který nového člena do skupiny pozval
	 * @param int $inviter
	 * @return void
	 */
	public function setInviter( $inviter )
	{
		$this->inviter = $inviter;
	}
	

	/**
	 * Uloží členství do databáze
	 * @return void
	 */
	public function save()
	{
		if( $this->id > 0 )
		{
			$update = array();
			$update['user'] = $this->user;
			$update['group'] = $this->group;
			$update['approved'] = $this->approved;
			$update['requested'] = $this->requested;
			$update['invited'] = $this->invited;
			$update['invited_date'] = $this->invitedDate;
			$update['requested_date'] = $this->requestedDate;
			$update['join_date'] = $this->joinDate;
			$update['inviter'] = $this->inviter;
			$this->registry->getObject('db')->updateRecords( 'group_membership', $update, 'ID=' . $this->id );
			
		}
		else
		{
			$insert = array();
			$insert['user'] = $this->user;
			$insert['group'] = $this->group;
			$insert['approved'] = $this->approved;
			$insert['requested'] = $this->requested;
			$insert['invited'] = $this->invited;
			$insert['invited_date'] = $this->invitedDate;
			$insert['requested_date'] = $this->requestedDate;
			$insert['join_date'] = $this->joinDate;
			$insert['inviter'] = $this->inviter;
			$this->registry->getObject('db')->insertRecords( 'group_membership', $insert );
			$this->id = $this->registry->getObject('db')->lastInsertID();
		}
	}
	
	
	
	
}



?>