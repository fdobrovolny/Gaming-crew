<?php
/**
 * Řadič zpráv
 * Základní systém soukromých zpráv pro web Dino Space
 */
class Messagescontroller {
	
	/**
	 * Konstruktor zpráv
	 * @param Registry $registry
	 * @param boolean $directCall
	 * @return void
	 */
	public function __construct( Registry $registry, $directCall=true )
	{
		$this->registry = $registry;
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			$urlBits = $this->registry->getObject('url')->getURLBits();
			if( isset( $urlBits[1] ) )
			{
				switch( $urlBits[1] )
				{
					case 'view':
						$this->viewMessage( intval( $urlBits[2] ) );
						break;
					case 'delete':
						$this->deleteMessage( intval( $urlBits[2] ) );
						break;
					case 'create':
						$this->newMessage( isset( $urlBits[2] ) ? intval( $urlBits[2] ) : 0 );
						break;	
					default:
						$this->viewInbox();
						break;
				}
				
			}
			else
			{
				$this->viewInbox();
			}
		}
		
	}
	
	/**
	 * Získá doručené zprávy přihlášeného uživatele
	 * @return void
	 */
	private function viewInbox()
	{
		require_once( FRAMEWORK_PATH . 'models/messages.php' );
		$messages = new Messages( $this->registry );
		$cache = $messages->getInbox( $this->registry->getObject('authenticate')->getUser()->getUserID() );
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'messages/inbox.tpl.php', 'footer.tpl.php');
		$this->registry->getObject('template')->getPage()->addTag( 'messages', array( 'SQL', $cache ) );
			
	}
	
	/**
	 * Zobrazí zprávu
	 * @param int $message identifikátor zprávy
	 * @return void
	 */
	private function viewMessage( $message )
	{
		require_once( FRAMEWORK_PATH . 'models/message.php' );
		$message = new Message( $this->registry, $message );
		if( $message->getRecipient() == $this->registry->getObject('authenticate')->getUser()->getUserID() )
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'messages/view.tpl.php', 'footer.tpl.php');
			$message->toTags( 'inbox_' );
			$message->setRead(1);
			$message->save();
		}
		else
		{
			$this->registry->errorPage( 'Přístup zamítnut', 'K této zprávě nemáte právo přístupu');
		}
	}
	
	/**
	 * Vytvoří novou zprávu
	 * @parm int $reply identifikátor zprávy, na kterou tato zpráva odpovídá (volitelné) - slouží k doplnění předmětu a příjemce
	 * @return void
	 */
	private function newMessage( $reply=0 )
	{
		$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'messages/create.tpl.php', 'footer.tpl.php');
			
		require_once( FRAMEWORK_PATH . 'models/relationships.php' );
		$relationships = new Relationships( $this->registry );
		
		if( isset( $_POST ) && count( $_POST ) > 0 )
		{
			$network = $relationships->getNetwork( $this->registry->getObject('authenticate')->getUser()->getUserID() );
			$recipient = intval( $_POST['recipient'] );
			if( in_array( $recipient, $network ) )
			{
				// tato dodatečná kontrola není pro soukromé zprávy nezbytná	
				require_once( FRAMEWORK_PATH . 'models/message.php' );
				$message = new Message( $this->registry, 0 );
				$message->setSender( $this->registry->getObject('authenticate')->getUser()->getUserID() );
				$message->setRecipient( $recipient );
				$message->setSubject( $this->registry->getObject('db')->sanitizeData( $_POST['subject'] ) );
				$message->setMessage( $this->registry->getObject('db')->sanitizeData( $_POST['message'] ) );
				$message->save();
				// upozornění příjemce e-mailem
				
				// potvrzení a přesměrování
				$url = $this->registry->getObject('url')->buildURL( array('messages'), '', false );
			$this->registry->redirectUser( $url, 'Zpráva odeslána', 'Zpráva byla úspěšně odeslána');
			}
			else
			{
				$this->registry->errorPage('Neplatný příjemce', 'Zprávy můžete odesílat pouze svým kontaktům');
			}
		}
		else
		{
			
			$cache = $relationships->getByUser( $this->registry->getObject('authenticate')->getUser()->getUserID() );
			$this->registry->getObject('template')->getPage()->addTag( 'recipients', array( 'SQL', $cache ) );
			if( $reply > 0 )
			{
				require_once( FRAMEWORK_PATH . 'models/message.php' );
				$message = new Message( $this->registry, $reply );
				if( $message->getRecipient() == $this->registry->getObject('authenticate')->getUser()->getUserID() )
				{
					$this->registry->getObject('template')->getPage()->addAdditionalParsingData( 'recipients', 'ID', $message->getSender(), 'opt', "selected='selected'");
					$this->registry->getObject('template')->getPage()->addTag( 'subject', 'Re: ' . $message->getSubject() );
				}
				else
				{
					$this->registry->getObject('template')->getPage()->addTag( 'subject', '' );
				}
				
			}
			else
			{
				$this->registry->getObject('template')->getPage()->addTag( 'subject', '' );
			}
		}
		
	}
	
	/**
	 * Odstraní zprávu
	 * @param int $message identifikátor zprávy
	 * @return void
	 */
	private function deleteMessage( $message )
	{
		require_once( FRAMEWORK_PATH . 'models/message.php' );
		$message = new Message( $this->registry, $message );
		if( $message->getRecipient() == $this->registry->getObject('authenticate')->getUser()->getUserID() )
		{
			if( $message->delete() )
			{
				$url = $this->registry->getObject('url')->buildURL( array('messages'), '', false );
				$this->registry->redirectUser( $url, 'Zpráva odstraněna', 'Zpráva byla úspěšně odstraněna z doručených zpráv');
			}
			else
			{
				$this->registry->errorPage( 'Je nám líto...', 'Při pokusu o odstranění zprávy došlo k chybě');
			}
		}
		else
		{
			$this->registry->errorPage( 'Přístup zamítnut', 'Tuto zprávu nemáte právo odstranit');
		}
	}
	
	
	
}


?>