<?php
class Memberscontroller{
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Objekt modelu
	 */
	private $model;
	
	/**
	 * Konstruktor
	 * @param Registry $registry objekt registru
	 * @param bool $directCall příznak přímého volání konstruktoru z frameworku
	 */
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		
		$urlBits = $this->registry->getObject('url')->getURLBits();
		if( isset( $urlBits[1] ) )
		{
			switch( $urlBits[1] )
			{
				case 'list':
					$this->listMembers( intval( $urlBits[2] ) );
					break;
				case 'alpha':
					$this->listMembersAlpha( $urlBits[2] , intval( isset( $urlBits[3] ) ? $urlBits[3] : 0 ) );
					break;
				case 'search':
					$this->searchMembers( true, '', 0 );
					break;
				case 'search-results':
					$this->searchMembers( false, $urlBits[2] , intval( isset( $urlBits[3] ) ? $urlBits[3] : 0 )  );
					break;	
				default:
					$this->listMembers(0);
					break;
			}
			
		}
		else
		{
			$this->listMembers( 0 );
		}
		
	}
	
	private function listMembers( $offset )
	{
		require_once( FRAMEWORK_PATH . 'models/members.php');
		$members = new Members( $this->registry );
		$pagination = $members->listMembers( $offset );
		if( $pagination->getNumRowsPage() == 0 )
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'members/invalid.tpl.php', 'footer.tpl.php');
		}
		else
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'members/list.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag( 'members', array( 'SQL', $pagination->getCache() ) );
			$this->registry->getObject('template')->getPage()->addTag( 'letter', '' );
			
			$this->registry->getObject('template')->getPage()->addTag( 'page_number', $pagination->getCurrentPage() );
			$this->registry->getObject('template')->getPage()->addTag( 'num_pages', $pagination->getNumPages() );
			if( $pagination->isFirst() )
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', '');
				$this->registry->getObject('template')->getPage()->addTag( 'previous', '' );			
			}	
			else
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', "<a href='members/list/'>První stránka</a>" );
				$this->registry->getObject('template')->getPage()->addTag( 'previous', "<a href='members/list/" . ( $pagination->getCurrentPage() - 2 ) . "'>Předchozí stránka</a>" );
			}
			if( $pagination->isLast() )
			{
				$this->registry->getObject('template')->getPage()->addTag( 'next', '' );
				$this->registry->getObject('template')->getPage()->addTag( 'last', '' );			
			}
			else
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', "<a href='members/list/" . $pagination->getCurrentPage() . "'>Další stránka</a>" );
				$this->registry->getObject('template')->getPage()->addTag( 'previous', "<a href='members/list/" . ( $pagination->getNumPages() - 1 ) . "'>Poslední stránka</a>" );
			}
			$this->formRelationships();
		}
	}
	
	private function listMembersAlpha( $alpha='A', $offset=0 )
	{
		require_once( FRAMEWORK_PATH . 'models/members.php');
		$members = new Members( $this->registry );
		$pagination = $members->listMembersByLetter( $alpha, $offset );
		if( $pagination->getNumRowsPage() == 0 )
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'members/invalid.tpl.php', 'footer.tpl.php');
		}
		else
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'members/list.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag( 'members', array( 'SQL', $pagination->getCache() ) );
			$this->registry->getObject('template')->getPage()->addTag( 'letter', " - Písmeno: " . $alpha );
			
			$this->registry->getObject('template')->getPage()->addTag( 'page_number', $pagination->getCurrentPage() );
			$this->registry->getObject('template')->getPage()->addTag( 'num_pages', $pagination->getNumPages() );
			if( $pagination->isFirst() )
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', '');
				$this->registry->getObject('template')->getPage()->addTag( 'previous', '' );			
			}	
			else
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', "<a href='members/alpha/".$alpha."/'>První stránka</a>" );
				$this->registry->getObject('template')->getPage()->addTag( 'previous', "<a href='members/alpha/".$alpha."/" . ( $pagination->getCurrentPage() - 2 ) . "'>Předchozí stránka</a>" );
			}
			if( $pagination->isLast() )
			{
				$this->registry->getObject('template')->getPage()->addTag( 'next', '' );
				$this->registry->getObject('template')->getPage()->addTag( 'last', '' );			
			}
			else
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', "<a href='members/alpha/".$alpha."/" . $pagination->getCurrentPage() . "'>Další stránka</a>" );
				$this->registry->getObject('template')->getPage()->addTag( 'previous', "<a href='members/alpha/".$alpha."/" . ( $pagination->getNumPages() - 1 ) . "'>Poslední stránka</a>" );
			}
		}
	}
	
	private function searchMembers( $search=true, $name='', $offset=0 )
	{
		require_once( FRAMEWORK_PATH . 'models/members.php');
		$members = new Members( $this->registry );
		
		if( $search == true )
		{
			// provádí se prvotní vyhledání
			$pagination = $members->filterMembersByName( urlencode( $_POST['name'] ), $offset );
			$name = urlencode( $_POST['name']  );	
		}
		else
		{
			// provádí se stránkování výsledků vyhledávání
			$pagination = $members->filterMembersByName( $name, $offset );	
		}
		if( $pagination->getNumRowsPage() == 0 )
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'members/invalid.tpl.php', 'footer.tpl.php');
		}
		else
		{
			$this->registry->getObject('template')->buildFromTemplates('header.tpl.php', 'members/search.tpl.php', 'footer.tpl.php');
			$this->registry->getObject('template')->getPage()->addTag( 'members', array( 'SQL', $pagination->getCache() ) );
			$this->registry->getObject('template')->getPage()->addTag( 'public_name', urldecode( $name ) );
			$this->registry->getObject('template')->getPage()->addTag( 'encoded_name', $name );
			
			$this->registry->getObject('template')->getPage()->addTag( 'page_number', $pagination->getCurrentPage() );
			$this->registry->getObject('template')->getPage()->addTag( 'num_pages', $pagination->getNumPages() );
			if( $pagination->isFirst() )
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', '');
				$this->registry->getObject('template')->getPage()->addTag( 'previous', '' );			
			}	
			else
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', "<a href='members/search-results/".$name."/'>První stránka</a>" );
				$this->registry->getObject('template')->getPage()->addTag( 'previous', "<a href='members/search-results/".$name."/" . ( $pagination->getCurrentPage() - 2 ) . "'>Předchozí stránka</a>" );
			}
			if( $pagination->isLast() )
			{
				$this->registry->getObject('template')->getPage()->addTag( 'next', '' );
				$this->registry->getObject('template')->getPage()->addTag( 'last', '' );			
			}
			else
			{
				$this->registry->getObject('template')->getPage()->addTag( 'first', "<a href='members/search-results/".$name."/" . $pagination->getCurrentPage() . "'>Další stránka</a>" );
				$this->registry->getObject('template')->getPage()->addTag( 'previous', "<a href='members/search-results/".$name."/" . ( $pagination->getNumPages() - 1 ) . "'>Poslední stránka</a>" );
			}
		}
	}
	
	private function formRelationships()
	{
		if( $this->registry->getObject('authenticate')->isLoggedIn() == true )
		{
			require_once( FRAMEWORK_PATH . 'models/relationships.php');
			$relationships = new Relationships( $this->registry );
			$types = $relationships->getTypes( true );
			$this->registry->getObject('template')->addTemplateBit( 'form_relationship', 'members/form_relationship.tpl.php');
			$this->registry->getObject('template')->getPage()->addPPTag( 'relationship_types', array( 'SQL', $types ) );	
		}
		else
		{
			$this->registry->getObject('template')->getPage()->addTag( 'form_relationship', '<!-- nabídka typů vztahů -->' );
		}
	}
}

?>