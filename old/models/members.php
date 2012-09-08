<?php

class Members{
	
	private $registry;
	
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
	}
	
	/**
	 * Vytvoří stránkovaný seznam členů
	 * @param int $offset posunutí
	 * @return Object strákovací objekt
	 */
	public function listMembers( $offset=0 )
	{
		require_once( FRAMEWORK_PATH . 'lib/pagination/pagination.class.php');
		$paginatedMembers = new Pagination( $this->registry );
		$paginatedMembers->setLimit( 25 );
		$paginatedMembers->setOffset( $offset );
		$query = "SELECT u.ID, u.username, p.name, p.dino_name, p.dino_gender, p.dino_breed FROM users u, profile p WHERE p.user_id=u.ID AND u.active=1 AND u.banned=0 AND u.deleted=0";
		$paginatedMembers->setQuery( $query );
		$paginatedMembers->setMethod( 'cache' );
		$paginatedMembers->generatePagination();
		return $paginatedMembers;
		
	}
	
	/**
	 * Vytvoří stránkovaný seznam členů, jejichž příjmení začíná na zadané písmeno
	 * @param String $letter písmeno
	 * @param int $offset posunutí
	 * @return Object stránkovací objekt
	 */
	public function listMembersByLetter( $letter='A', $offset=0 )
	{
		
		$alpha = strtoupper( $this->registry->getObject('db')->sanitizeData( $letter ) );
		require_once( FRAMEWORK_PATH . 'lib/pagination/pagination.class.php');
		$paginatedMembers = new Pagination( $this->registry );
		$paginatedMembers->setLimit( 25 );
		$paginatedMembers->setOffset( $offset );
		$query = "SELECT u.ID, u.username, p.name, p.dino_name, p.dino_gender, p.dino_breed FROM users u, profile p WHERE p.user_id=u.ID AND u.active=1 AND u.banned=0 AND u.deleted=0 AND SUBSTRING_INDEX(p.name,' ', -1)LIKE'".$alpha."%' ORDER BY SUBSTRING_INDEX(p.name,' ', -1) ASC";
		$paginatedMembers->setQuery( $query );
		$paginatedMembers->setMethod( 'cache' );
		$paginatedMembers->generatePagination();
		return $paginatedMembers;
		
	}
	
	/**
	 * Vyhledá členy podle jména
	 * @param String $filter hledané jméno
	 * @param int $offset posunutí
	 * @return Object stránkovací objekt
	 */
	public function filterMembersByName( $filter='', $offset=0 )
	{
		$filter = ( $this->registry->getObject('db')->sanitizeData( urldecode( $filter ) ) );
		require_once( FRAMEWORK_PATH . 'lib/pagination/pagination.class.php');
		$paginatedMembers = new Pagination( $this->registry );
		$paginatedMembers->setLimit( 25 );
		$paginatedMembers->setOffset( $offset );
		$query = "SELECT u.ID, u.username, p.name, p.dino_name, p.dino_gender, p.dino_breed FROM users u, profile p WHERE p.user_id=u.ID AND u.active=1 AND u.banned=0 AND u.deleted=0 AND p.name LIKE'%".$filter."%' ORDER BY p.name ASC";
		$paginatedMembers->setQuery( $query );
		$paginatedMembers->setMethod( 'cache' );
		$paginatedMembers->generatePagination();
		return $paginatedMembers;
	}
	
	
	
}



?>