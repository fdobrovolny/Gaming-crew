<?php

/**
 * Model zpráv
 */
class Messages {
	
	/**
	 * Konstruktor zpráv
	 * @param Registry $registry objekt registru
	 * @return void
	 */
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
	}
	
	/**
	 * Získá soukromé zprávy uživatele
	 * @param int $user identifikátor uživatele
	 * @return int index v mezipaměti
	 */
	public function getInbox( $user )
	{
		$sql = "SELECT IF(m.read=0,'unread','read') as read_style, m.subject, m.ID, m.sender, m.recipient, DATE_FORMAT(m.sent, '%d.%m.%Y') as sent_friendly, psender.name as sender_name FROM messages m, profile psender WHERE psender.user_id=m.sender AND m.recipient=" . $user . " ORDER BY m.ID DESC";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
		
	}
}
?>