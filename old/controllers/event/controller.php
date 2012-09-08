<?php

class Eventcontroller {
	
	/**
	 * Konstruktor řadiče
	 * @param Registry $registry objekt registru
	 * @param bool $directCall příznak přímého volání konstruktoru frameworkem
	 */
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			$urlBits = $this->registry->getObject('url')->getURLBits();
			switch( isset( $urlBits[1] )? $urlBits[1] : '')
			{
				case 'create':
					$this->createEvent();
					break;	
				case 'view':
					$this->viewEvent( intval( $urlBits[2] ) );
					break;
				case 'change-attendance':
					$this->changeAttendance( intval( $urlBits[2] ) );
					break;
				default:
					$this->listUpcomingInNetwork();
					break;
			}
		}		
	}
	
	/**
	 * Vytvoří událost
	 * @return void
	 */
	private function createEvent()
	{
		// existují-li nějaká data v poli _POST, vytváří se nová událost
		if( isset( $_POST ) && count( $_POST ) > 0 )
		{
			require_once( FRAMEWORK_PATH . 'models/event.php' );
			$event = new Event( $this->registry, 0 );
			$event->setName( $this->registry->getObject('db')->sanitizeData( $_POST['name'] ) );
			$event->setDescription( $this->registry->getObject('db')->sanitizeData( $_POST['description'] ) );
			$event->setDate( $this->registry->getObject('db')->sanitizeData( $_POST['date'] ), false );
			$event->setStartTime( $this->registry->getObject('db')->sanitizeData( $_POST['start_time'] ) );
			$event->setEndTime( $this->registry->getObject('db')->sanitizeData( $_POST['end_time'] ) );
			$event->setCreator( $this->registry->getObject('authenticate')->getUser()->getUserID() );
			$event->setType( $this->registry->getObject('db')->sanitizeData( $_POST['type'] ) );
			if( isset( $_POST['invitees'] ) && is_array( $_POST['invitees'] ) && count( $_POST['invitees'] ) > 0 )
			{
				// identifikátory pozvaných uživatelů jsou v poli _POST uložené ve formě pole 
				$is = array();
				foreach( $_POST['invitees'] as $i )
				{
					$is[] = intval( $i );
				}
				$event->setInvitees( $is );
			}
			$event->save();
			$this->registry->redirectUser( $this->registry->buildURL(array( 'event', 'view', $event->getID() )), 'Událost vytvořena', 'Událost byla úspěšně vytvořena');
			
		}
		else
		{
		  require_once( FRAMEWORK_PATH . 'models/relationships.php');
      $relationships = new Relationships( $this->registry );
      $all = $relationships->getByUser( $this->registry->getObject('authenticate')->getUser()->getUserID() );
		  $this->registry->getObject('template')->getPage()->addTag('all', array( 'SQL', $all ) );
			$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'events/create.tpl.php', 'footer.tpl.php' );
		}
	}
	
	/**
	 * Zobrazí událost
	 * @param int $id identifikátor události
	 * @return void
	 */
	private function viewEvent( $id ) 
  { 
    require_once( FRAMEWORK_PATH . 'models/event.php' ); 
    $event = new Event( $this->registry, $id ); 
    $show = true; 
    if( $event->getType() == 'private' ) 
    { 
      // sem můžete přidat kód pro soukromé události 
      $show = false; 
    } 
    if( $show == true ) 
    { 
      $event->toTags( 'event_' ); 
      $this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'events/view.tpl.php', 'footer.tpl.php' ); 
      $attendingCache = $event->getAttending(); 
      $this->registry->getObject('template')->getPage()->addTag( 'attending', array('SQL', $attendingCache) ); 
      $notAttendingCache = $event->getNotAttending(); 
      $this->registry->getObject('template')->getPage()->addTag( 'notattending', array('SQL', $notAttendingCache) ); 
      $maybeAttendingCache = $event->getMaybeAttending(); 
      $this->registry->getObject('template')->getPage()->addTag( 'maybeattending', array('SQL', $maybeAttendingCache) ); 
      $invitedCache = $event->getInvited(); 
      $this->registry->getObject('template')->getPage()->addTag( 'invited', array('SQL', $invitedCache) );
      $sql = "SELECT * FROM event_attendees WHERE event_id={$id} AND user_id=" . $this->registry->getObject('authenticate')->getUser()->getUserID(); 
      $this->registry->getObject('db')->executeQuery( $sql ); 
      if( $this->registry->getObject('db')->numRows() == 1 ) 
      { 
        $data = $this->registry->getObject('db')->getRows(); 
        if( $data['status'] == 'going' ) 
        { 
          $s = 'attending'; 
        } 
        elseif( $data['status'] == 'not going' ) 
        { 
          $s = 'notattending'; 
        } 
        elseif( $data['status'] == 'maybe' ) 
        { 
          $s = 'maybeattending'; 
        } 
        else 
        { 
          $s = 'unknown'; 
        } 
        $this->registry->getObject('template')->getPage()->addTag( $s . '_select', "selected='selected'"); 
      } 
      else 
      { 
        $this->registry->getObject('template')->getPage()->addTag('unknown_select', "selected='selected'"); 
      }
    } 
    else 
    { 
      // chyba
    } 
  }
	
	private function changeAttendance( $event )
	{
		$sql = "SELECT * FROM event_attendees WHERE event_id={$event} AND user_id=" . $this->registry->getObject('authenticate')->getUser()->getUserID();
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() == 1 )
		{
			$data = $this->registry->getObject('db')->getRows();
			$changes = array();
			$changes['status'] = $this->registry->getObject('db')->sanitizeData( $_POST['status'] );
			$this->registry->getObject('db')->updateRecords( 'event_attendees', $changes, 'ID=' . $data['ID'] );
			$this->registry->redirectUser( $this->registry->buildURL(array( 'home' )), 'Změny byly úspěšně uloženy', 'Děkujeme, nastavení Vaší účasti bylo úspěšně uloženo');
		}
		else
		{
			$this->registry->errorPage('Změny nebyly uloženy', 'Nemáte právo nastavit svou účast na této události, zkuste to prosím později');
		}
	}
	
	private function listUpcomingInNetwork()
	{
		require_once( FRAMEWORK_PATH . 'models/events.php' );
		$events = new Events( $this->registry );
		$cache = $events->listEventsFuture( $this->registry->getObject('authenticate')->getUser()->getUserID(), 30 );
		$this->registry->getObject('template')->getPage()->addTag( 'events', array( 'SQL', $cache ) );
		$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'events/upcoming.tpl.php', 'footer.tpl.php' );
	}
}


?>