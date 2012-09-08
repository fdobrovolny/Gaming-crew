<?php

/**
 * Model události
 */
class Event{
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Identifikátor události
	 */
	private $ID;
	
	/**
	 * Identifikátor uživatele, který událost vytvořil
	 */
	private $creator;
	
	/**
	 * Název události
	 */
	private $name;
	
	/**
	 * Popis události
	 */
	private $description;
	
	/**
	 * Datum události
	 */
	private $event_date;
	
	/**
	 * Čas zahájení
	 */
	private $start_time;
	
	/**
	 * Čas ukončení
	 */
	private $end_time;
	
	/**
	 * Typ
	 */
	private $type;
	
	/**
	 * Aktivní
	 */
	private $active;
	
	/**
	 * Pozvaní
	 */
	private $invitees = array();
		
	/**
	 * Konstruktor události
	 * @param Registry $registry objekt registru
	 * @param int $ID identifikátor události
	 * @return void
	 */
	public function __construct( Registry $registry, $ID=0 )
	{
		$this->registry = $registry;
		if( $ID != 0 )
		{
			$this->ID = $ID;
			// je-li zadaný identifikátor, nastaví se hodnoty vlastností na základě odpovídajícího záznamu databáze
			$sql = "SELECT * FROM events WHERE ID=" . $this->ID;
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$data = $this->registry->getObject('db')->getRows();
				// nastavení hodnot vlastností
				foreach( $data as $key => $value )
				{
					$this->$key = $value;
				}
			}
			
		}
	}
	
	/**
	 * Nastaví název události
	 * @param String $name název
	 * @return void
	 */
	public function setName( $name )
	{
		$this->name = $name;
	}
	
	/**
	 * Nastaví uživatele, který událost vytvořil
	 * @param int $creator identifikátor uživatele
	 * @return void
	 */
	public function setCreator( $ID )
	{
		$this->creator = $ID;
	}
	
	public function setInvitees( $invitees )
	{
		$this->invitees = $invitees;
	}
	
	/**
	 * Nastaví popis události
	 * @param String $description popis
	 */
	public function setDescription( $description )
	{
		$this->description = $description;
	}
	
	/**
	 * Nastaví datum události
	 * @param String $date datum
	 * @param boolean $formatted příznak naformátování data řadičem
	 */
	public function setDate( $date, $formatted=true )
	{
		if( $formatted == true )
		{
			$this->event_date = $date;
		}
		else
		{
			$temp = explode('/', $date );
			$this->event_date = $temp[2].'-'.$temp[1].'-'.$temp[0];
		}
	}
	
	/**
	 * Nastaví čas zahájení události
	 * @param String $time čas
	 * return void
	 */
	public function setStartTime( $time )
	{
		$this->start_time = $time;
	}
	
	/**
	 * Nastaví čas ukončení události
	 * @param String $time čas
	 * return void
	 */
	public function setEndTime( $time )
	{
		$this->end_time = $time;
	}
	
	/**
	 * Nastaví typ události
	 * @param String $type typ
	 * @param boolean $checked příznak ověření zadaného typu
	 * @return void
	 */
	public function setType( $type, $checked=true )
	{
		if( $checked == true )
		{
			$this->type = $type;
		}
		else
		{
			$types = array( 'public', 'private' );
			if( in_array( $type, $types ) )
			{
				$this->type = $type;
			}
		}
	}
	
	/**
	 * Nastaví, jestli je událost aktivní
	 * @param bool $active
	 * @return void
	 */
	public function setActive( $active )
	{
		$this->active = $active;
	}
	
	
	
	/**
	 * Uloží událost
	 * @return bool
	 */
	public function save()
	{
		// ověření práv uživatele
		if( $this->registry->getObject('authenticate')->isLoggedIn() && ( $this->registry->getObject('authenticate')->getUser()->getUserID() ==  $this->creator || $this->registry->getObject('authenticate')->getUser()->isAdmin() == true  || $this->ID == 0 ) )
		{
			// uživatel je buďto autorem události, administrátorem anebo právě vytváří novou událost
			$event = array();
			foreach( $this as $field => $data )
			{
				if( ! is_array( $data ) && ! is_object( $data ) && $field != 'ID'  )
				{
					$event[ $field ] = $this->$field;
				}
				
			}
			if( $this->ID == 0 )
			{
				$this->registry->getObject('db')->insertRecords( 'events', $event );
				$this->ID = $this->registry->getObject('db')->lastInsertID();
				if( is_array( $this->invitees ) && count( $this->invitees ) > 0 )
				{
					foreach( $this->invitees as $invitee )
					{
						$insert = array();
						$insert['event_id'] = $this->ID;
						$insert['user_id'] = $invitee;
						$insert['status'] = 'invited';
						$this->registry->getObject('db')->insertRecords( 'event_attendees', $insert );
					}
				}
				return true;
			}
			else
			{
				$this->registry->getObject('db')->updateRecords( 'events', $event, 'ID=' . $this->ID );
				if( $this->registry->getObject('db')->affectedRows() == 1 )
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Převede data události na značky šablony
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
	 * Získá název události
	 * @return String
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Získá identifikátor události
	 * @return int
	 */
	public function getID()
	{
		return $this->ID;
	}

	/**
	 * Získá typ události
	 * @return String
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Získá uživatele, kteří se události účastní
	 * @return int identifikátor mezipaměti
	 */
	public function getAttending()
	{
		$sql = "SELECT p.* FROM profile p, event_attendees a WHERE p.user_id=a.user_id AND a.status='attending' AND a.event_id=" . $this->ID;
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá uživatele, kteří se události neúčastní
	 * @return int identifikátor mezipaměti
	 */
	public function getNotAttending()
	{
		$sql = "SELECT p.* FROM profile p, event_attendees a WHERE p.user_id=a.user_id AND a.status='not attending' AND a.event_id=" . $this->ID;
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá uživatele, kteří se události možná zúčastní
	 * @return int identifikátor mezipaměti
	 */
	public function getMaybeAttending()
	{
		$sql = "SELECT p.* FROM profile p, event_attendees a WHERE p.user_id=a.user_id AND a.status='maybe' AND a.event_id=" . $this->ID;
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá uživatele pozvané na událost
	 * @return int identifikátor mezipaměti
	 */
	public function getInvited()
	{
		$sql = "SELECT p.* FROM profile p, event_attendees a WHERE p.user_id=a.user_id AND a.status='invited' AND a.event_id=" . $this->ID;
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	
}

?>