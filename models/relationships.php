<?php

class Relationships{
	
	
	public function __construct( Registry $registry )
	{
		$this->registry = $registry; 
	}
	
	/**
	 * Získá typy vztahù
	 * @param $cache má se výsledek uložit do mezipamìti?
	 * @return mixed [int|array]
	 */
	public function getTypes( $cache=false )
	{
		$sql = "SELECT ID as type_id, name as type_name, plural_name as type_plural_name, mutual as type_mutual FROM relationship_types WHERE active=1";
		if( $cache == true )
		{
			$cache = $this->registry->getObject('db')->cacheQuery( $sql );
			return $cache;
		}
		else
		{
			$types = array();
			while( $row = $this->registry->getObject('db')->getRows() )
			{
				$types[] = $row;
			}
			return $types;
		}
	}
	
	/**
	 * Získá vztahy mezi uživateli
	 * @param int $usera 
	 * @param int $userb
	 * @param int $approved
	 * @return int cache
	 */
	public function getRelationships( $usera, $userb, $approved=0 )
	{
		$sql = "SELECT t.name as type_name, t.plural_name as type_plural_name, uap.name as usera_name, ubp.name as userb_name, r.ID FROM relationships r, relationship_types t, profile uap, profile ubp WHERE t.ID=r.type AND uap.user_id=r.usera AND ubp.user_id=r.userb AND r.accepted={$approved}";
		if( $usera != 0 && $userb == 0)
		{
			$sql .= " AND ( r.usera={$usera} OR r.userb={$usera} )";
		}
		elseif( $usera == 0 && $userb != 0)
		{
			$sql .= " AND ( r.usera={$userb} OR r.userb={$userb} )";
		}
		elseif( $userb != 0 )
		{
			$sql .= " AND ( ( r.usera={$usera} OR r.userb={$userb} ) OR ( ( r.usera={$userb} OR r.userb={$usera} ) ) ";
		}
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá vztahy uživatele
	 * @param int $user identifikátor uživatele, jehož vztahy se mají získat
	 * @param boolean $obr mají se výsledky náhodnì seøadit?
	 * @param int $limit má se omezit poèet výsledkù? ( 0 znamená ne, > 0 znamená omezit na zadaný poèet)
	 * @return int identifikátor v mezipamìti
	 */
	public function getByUser( $user, $obr=false, $limit=0 )
	{
		// standardní dotaz pro získání vztahù uživatele
		$sql = "SELECT t.plural_name, p.name as users_name, u.ID FROM users u, profile p, relationships r, relationship_types t WHERE t.ID=r.type AND r.accepted=1 AND (r.usera={$user} OR r.userb={$user}) AND IF( r.usera={$user},u.ID=r.userb,u.ID=r.usera) AND p.user_id=u.ID";
		// náhodnì seøadit?
		if( $obr == true )
		{
			$sql .= " ORDER BY RAND() ";
		}
		// omezit poèet výsledkù?
		if( $limit != 0 )
		{
			$sql .= " LIMIT " . $limit;
		}
		// uložení výsledku do mezipamìti
		$cache = $this->registry->getObject('db')->cacheQuery( $sql );
		return $cache;
	}
	
	/**
	 * Získá identifikátory kontaktù uživatele
	 * @param int $user identifikátor, jehož kontakty se mají získat
	 * @return array
	 */
	public function getNetwork( $user )
	{
		$sql = "SELECT u.ID FROM users u, profile p, relationships r, relationship_types t WHERE t.ID=r.type AND r.accepted=1 AND (r.usera={$user} OR r.userb={$user}) AND IF( r.usera={$user},u.ID=r.userb,u.ID=r.usera) AND p.user_id=u.ID";
		$this->registry->getObject('db')->executeQuery( $sql );
		$network = array();
		if( $this->registry->getObject('db')->numRows() > 0 )
		{
			while( $r = $this->registry->getObject('db')->getRows() )
			{
				$network[] = $r['ID'];
			}
		}
		return $network;
	}
	
	/**
	 * Získá identifikátory uživatelù, se kterými je uživatel v kontaktu
	 * @param int $user identifikátor uživatele
	 * @param bool $cache mají se výsledky uložit do mezipamìti?
	 * @return String / int
	 */
	public function getIDsByUser( $user, $cache=false )
	{
		$sql = "SELECT u.ID FROM users u, profile p, relationships r, relationship_types t WHERE t.ID=r.type AND r.accepted=1 AND (r.usera={$user} OR r.userb={$user}) AND IF( r.usera={$user},u.ID=r.userb,u.ID=r.usera) AND p.user_id=u.ID";
		if( $cache == false )
		{
			return $sql;
		}
		else
		{
			$cache = $this->registry->getObject('db')->cacheQuery( $sql );
			return $cache;
		}
	}
}

?>