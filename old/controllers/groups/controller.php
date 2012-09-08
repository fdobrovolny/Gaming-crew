<?php

class Groupscontroller {
	
	/**
	 * Konstruktor
	 * @param Registry $registry objekt registru
	 * @param bool $directCall příznak přímého volání konstruktoru z frameworku
	 */
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		$urlBits = $this->registry->getObject('url')->getURLBits();
		
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			if( isset( $urlBits[1] ) )
			{
				switch( $urlBits[1] )
				{
					case 'create':
						$this->createGroup();
						break;
					case 'mine':
						$this->listMyGroups();
						break;
					default:
						$this->listPublicGroups(0);
						break;
				}
				
			}
			else
			{
				$this->listPublicGroups( 0 );
			}
		}
		else
		{
			if( isset( $urlBits[1] ) )
			{
				$this->listPublicGroups( intval( $urlBits[1] ) );
			}
			else
			{
				$this->listPublicGroups( 0 );
			}
			
		}
		
		
	}
	
	
	/**
	 * Vytvoří skupinu
	 * @return void
	 */
	private function createGroup()
	{
		if( isset( $_POST ) && is_array( $_POST ) && count( $_POST ) > 0 )
		{
			require_once( FRAMEWORK_PATH . 'models/group.php');
			$group = new Group( $this->registry, 0 );
			$group->setCreator( $this->registry->getObject('authenticate')->getUser()->getUserID() );
			$group->setName( $this->registry->getObject('db')->sanitizeData( $_POST['name'] ) );
			$group->setDescription( $this->registry->getObject('db')->sanitizeData( $_POST['description'] ) );
			$group->setType( $_POST['type'] );
			$group->save();
			$this->registry->errorPage('Skupina vytvořena', 'Vaše nová skupina byla úspěšně vytvořena');
		}
		else
		{
			$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'groups/create.tpl.php', 'footer.tpl.php' );
		}
	}
	
	private function listPublicGroups( $offset )
	{
		$sql = "SELECT * FROM groups WHERE active=1 AND type <> 'private' ";
		require_once( FRAMEWORK_PATH . 'lib/pagination/pagination.class.php');
		$pagination = new Pagination( $this->registry );
		$pagination->setQuery( $sql );
		$pagination->setOffset( $offset );
		$pagination->setLimit( 20 );
		$pagination->setMethod('cache');
		$pagination->generatePagination();
		if( $pagination->getNumRowsPage() == 0 )
		{
			$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'groups/no-public.tpl.php', 'footer.tpl.php' );
		
		}
		else
		{
			$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'groups/public.tpl.php', 'footer.tpl.php' );
			$this->registry->getObject('template')->getPage()->addTag( 'groups', array( 'SQL', $pagination->getCache() ) );
			
			$this->registry->getObject('template')->getPage()->addTag( 'page_number', $pagination->getCurrentPage() );
			$this->registry->getObject('template')->getPage()->addTag( 'num_pages', $pagination->getNumPages() );
			if( $pagination->isFirst() )
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', '');
				$this->registry->getObject('template')->getPage()->addTag( 'previous', '' );			
			}	
			else
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', "<a href='groups/'>První stránka</a>" );
				$this->registry->getObject('template')->getPage()->addTag( 'previous', "<a href='groups/" . ( $pagination->getCurrentPage() - 2 ) . "'>Předchozí stránka</a>" );
			}
			if( $pagination->isLast() )
			{
				$this->registry->getObject('template')->getPage()->addTag( 'next', '' );
				$this->registry->getObject('template')->getPage()->addTag( 'last', '' );			
			}
			else
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', "<a href='groups/" . $pagination->getCurrentPage() . "'>Další stránka</a>" );
				$this->registry->getObject('template')->getPage()->addTag( 'previous', "<a href='groups/" . ( $pagination->getNumPages() - 1 ) . "'>Poslední stránka</a>" );
			}

		}
		
	}
	
	private function listMyGroups()
	{
		$user = $this->registry->getObject('authenticate')->getUser()->getUserID();
		$sql = "SELECT * FROM groups WHERE creator={$user} OR ID IN (SELECT m.group FROM group_membership m WHERE m.user={$user} and m.approved=1 ) ";
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		$this->registry->getObject('template')->getPage()->addTag( 'my-groups', array('SQL', $cache ) );
		$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'groups/mine.tpl.php', 'footer.tpl.php' );
	}
	
	
	
}



?>