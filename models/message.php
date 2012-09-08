<?php
/**
 * Model zprávy
 */
class Message {

	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Identifikátor zprávy
	 */
	private $id=0;
	
	/**
	 * Identifikátor odesilatele
	 */
	private $sender;
	
	/**
	 * Jméno odesilatele
	 */
	private $senderName;
	
	/**
	 * Identifikátor příjemce
	 */
	private $recipient;
	
	/**
	 * Jméno příjemce
	 */
	private $recipientName;
	
	/**
	 * Předmět zprávy
	 */
	private $subject;
	
	/**
	 * Časová známka odeslání zprávy
	 */
	private $sent;
	
	/**
	 * Uživatelsky přívětivější verze času odeslání zprávy
	 */
	private $sentFriendlyTime;
	
	/**
	 * Příznak přečtení zprávy
	 */
	private $read=0;
	
	/**
	 * Samotný obsah zprávy
	 */
	private $message;
	
	/**
	 * Konstruktor zprávy
	 * @param Registry $registry objekt registru
	 * @param int $id identifikátor zprávy
	 * @return void
	 */
	public function __construct( Registry $registry, $id=0 )
	{
		$this->registry = $registry;
		$this->id = $id;
		if( $this->id > 0 )
		{
			$sql = "SELECT m.*, DATE_FORMAT(m.sent, '%d.%m.%Y') as sent_friendly, psender.name as sender_name, precipient.name as recipient_name FROM messages m, profile psender, profile precipient WHERE precipient.user_id=m.recipient AND psender.user_id=m.sender AND m.ID=" . $this->id;
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() > 0 )
			{
				$data = $this->registry->getObject('db')->getRows();
				$this->sender = $data['sender'];
				$this->recipient = $data['recipient'];
				$this->sent = $data['sent'];
				$this->read = $data['read'];
				$this->subject = $data['subject'];
				$this->message = $data['message'];
				$this->sentFriendlyTime = $data['sent_friendly'];
				$this->senderName = $data['sender_name'];
				$this->recipientName = $data['recipient_name'];
				
			}
			else
			{
				$this->id = 0;
			}
		}
	}
	
	/**
	 * Nastaví odesilatele zprávy
	 * @param int $sender identifikátor odesilatele
	 * @return void
	 */
	public function setSender( $sender )
	{
		$this->sender = $sender;	
	}
	
	/**
	 * Nastaví příjemce zprávy
	 * @param int $recipient identifikátor příjemce
	 * @return void
	 */
	public function setRecipient( $recipient )
	{
		$this->recipient = $recipient;
	}
	
	/**
	 * Nastaví předmět zprávy
	 * @param String $subject předmět
	 * @return void
	 */
	public function setSubject( $subject )
	{
		$this->subject = $subject;
	}
	
	/**
	 * Nastaví příznak přečtení zprávy
	 * @param boolean $read příznak přečtení
	 * @return void
	 */
	public function setRead( $read )
	{
		$this->read = $read;
	}
	
	/**
	 * Nastaví obsah zprávy
	 * @param String $message obsah zprávy
	 * @return void
	 */
	public function setMessage( $message )
	{
		$this->message = $message;
	}
	
	/**
	 * Uloží zprávu do databáze
	 * @return void
	 */
	public function save()
	{
		if( $this->id > 0 )
		{
			$update = array();
			$update['sender'] = $this->sender;
			$update['recipient'] = $this->recipient;
			$update['read'] = $this->read;
			$update['subject'] = $this->subject;
			$update['message'] = $this->message;
			$this->registry->getObject('db')->updateRecords( 'messages', $update, 'ID=' . $this->id );
		}
		else
		{
			$insert = array();
			$insert['sender'] = $this->sender;
			$insert['recipient'] = $this->recipient;
			$insert['read'] = $this->read;
			$insert['subject'] = $this->subject;
			$insert['message'] = $this->message;
			$this->registry->getObject('db')->insertRecords( 'messages', $insert );
			$this->id = $this->registry->getObject('db')->lastInsertID();
		}
	}
	
	/**
	 * Získá příjemce zprávy
	 * @return int
	 */
	public function getRecipient()
	{
		return $this->recipient;
	}
	
	/**
	 * Získá odesilatele zprávy
	 * @return int
	 */
	public function getSender()
	{
		return $this->sender;
	}
	
	/**
	 * Získá předmět zprávy
	 */
	public function getSubject()
	{
		return $this->subject;
	}
	
	/**
	 * Převede zprávu na značky
	 * @param String $prefix prefix značek
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
	 * Odstraní zprávu
	 * @return boolean
	 */
	public function delete()
	{
		$sql = "DELETE FROM messages WHERE ID=" . $this->id;
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->affectedRows() > 0 )
		{
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