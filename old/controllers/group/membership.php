<?php

class Membershipcontroller {
	
	private $registry;
	
	private $groupID;
	
	private $group;
	
	public function __construct( Registry $registry, $groupID )
	{
		$this->registry = $registry;
		$this->groupID = $groupID;
		require_once( FRAMEWORK_PATH . 'models/group.php');
		$this->group = new Group( $this->registry, $this->groupID );
	}
	
	public function join()
	{
		$type = $this->group->getType();
		switch( $type )
		{
			case 'public':
				$this->autoJoinGroup();
				break;
		}
				
	}
	
	private function autoJoinGroup()
	{
		require_once( FRAMEWORK_PATH . 'models/groupmembership.php');
		$gm = new Groupmembership( $this->registry, 0 );
		$user = $this->registry->getObject('authenticate')->getUser()->getUserID();
					
		$gm->getByUserAndGroup( $user, $this->groupID );
		
		if( $gm->isValid() )
		{
			$gm = new Groupmembership( $this->registry, $gm->getID() );
			
		
		}
		$gm->setApproved( 1 );
		$gm->save();
		$this->registry->errorPage('Nové členství', 'Vaše členství ve skupině bylo úspěšně nastaveno');
					
	}
	
	
	
}



?>