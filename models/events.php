<?php
/**
 * Model událostí
 */
class Events{
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Konstruktor
	 * @param Registry $registry
	 * @return void
	 */
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
	}
	
	/**
	 * Získá události kontaktů uživatele v zadaném měsíci / roce
	 * @param int $connectedTo identifikátor uživatele
	 * @param int $month
	 * @param int $year
	 * @return int identifikátor mezipaměti
	 */
	public function listEventsMonthYear( $connectedTo, $month, $year )
	{
		require_once( FRAMEWORK_PATH . 'models/relationships.php');
		$relationships = new Relationships( $this->registry );
		$idsSQL = $relationships->getIDsByUser( $connectedTo );
		$sql = "SELECT p.name as creator_name, e.* FROM events e, profile p WHERE p.user_id=e.creator AND e.event_date LIKE '{$year}-{$month}-%' AND e.creator IN ($idsSQL) ";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá události kontaktů uživatele v následujících x dnech
	 * @param int $connectedTo identifikátor uživatele
	 * @param int $days počet dnů
	 * @return int identifikátor mezipaměti
	 */
	public function listEventsFuture( $connectedTo, $days )
	{
		require_once( FRAMEWORK_PATH . 'models/relationships.php');
		$relationships = new Relationships( $this->registry );
		$idsSQL = $relationships->getIDsByUser( $connectedTo );
		$sql = "SELECT p.name as creator_name, e.* FROM events e, profile p WHERE p.user_id=e.creator AND e.event_date >= CURDATE() AND e.event_date <= DATE_ADD(CURDATE(), INTERVAL {$days} DAY ) AND e.creator IN ($idsSQL) ";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá události zadaného uživatele v následujících x dnech
	 * @param int $user identifikátor uživatele
	 * @param int $days počet dnů
	 * @return int identifikátor mezipaměti
	 */
	public function listEventsUserFuture( $user, $days )
	{
		$sql = "SELECT p.name as creator_name, e.* FROM events e, profile p WHERE p.user_id=e.creator AND e.event_date >= CURDATE() AND e.event_date <= DATE_ADD(CURDATE(), INTERVAL {$days} DAY ) AND e.creator={$user} ";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá události, na které je uživatel v budoucnu pozvaný
	 * @param int $user identifikátor uživatele
	 * @return int identifikátor mezipaměti
	 */
	public function listEventsInvited( $user )
	{
		$sql = "SELECT p.name as creator_name, e.* FROM events e, profile p WHERE p.user_id=e.creator AND e.event_date >= CURDATE() AND ( SELECT COUNT(*) FROM events_attendees a WHERE a.event_id=e.ID AND a.user_id={$user} AND a.status='invited' ) > 0";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá události, kterých se uživatel v budoucnu účastní 
	 * @param int $user identifikátor uživatele
	 * @return int identifikátor mezipaměti
	 */
	public function listEventsAttending( $user )
	{
		$sql = "SELECT p.name as creator_name, e.* FROM events e, profile p WHERE p.user_id=e.creator AND e.event_date >= CURDATE() AND ( SELECT COUNT(*) FROM events_attendees a WHERE a.event_id=e.ID AND a.user_id={$user} AND a.status='attending' ) > 0";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá události, kterých se uživatel v budoucnu neúčastní 
	 * @param int $user identifikátor uživatele
	 * @return int identifikátor mezipaměti
	 */
	public function listEventsNotAttending( $user )
	{
		$sql = "SELECT p.name as creator_name, e.* FROM events e, profile p WHERE p.user_id=e.creator AND e.event_date >= CURDATE() AND ( SELECT COUNT(*) FROM events_attendees a WHERE a.event_id=e.ID AND a.user_id={$user} AND a.status='not attending' ) > 0";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá události, kterých se uživatel v budoucnu možná zúčastní
	 * @param int $user identifikátor uživatele
	 * @return int identifikátor mezipaměti
	 */
	public function listEventsMaybeAttending( $user )
	{
		$sql = "SELECT p.name as creator_name, e.* FROM events e, profile p WHERE p.user_id=e.creator AND e.event_date >= CURDATE() AND ( SELECT COUNT(*) FROM events_attendees a WHERE a.event_id=e.ID AND a.user_id={$user} AND a.status='maybe' ) > 0";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
		
	
	
}



?>