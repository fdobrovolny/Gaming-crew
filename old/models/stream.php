<?php
/**
 * Správa stavů ve formě proudu
 */
class Stream{
	
	/**
	 * Pole typů umožňující pozdější rozšíření této třídy (viz kapitola 7)
	 */
	private $types = array();
	
	/**
	 * Příznak jestli je proud prázdný
	 */
	private $empty = true;
	
	/**
	 * Samotný proud
	 */
	private $stream = array();
	
	/**
	 * Identifikátory stavů proudu
	 */
	private $IDs = array();
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	
	/**
	 * Konstruktor
	 * @param Registry $registry objekt registru
	 * @return void
	 */
	public function __construct( Registry $registry )
	{
		$this->registry = $registry;
	}	
	
	/**
	 * Vytvoří proud uživatelů
	 * @param int $user identifikátor uživatele, z jehož sítě kontaktů se bude vytvářet proud
	 * @param int $offset parametr užitečný pro funkci "zobrazit další stavy"
	 * @return void
	 */
	public function buildStream( $user, $offset=0 )
	{
		// příprava pole
		$network = array();
		// získání vztahů prostřednictvím vztahového modelu
		require_once( FRAMEWORK_PATH . 'models/relationships.php' );
		$relationships = new Relationships( $this->registry );
		$network = $relationships->getNetwork( $user );
		// přidá se nula, aby neselhala klauzule IN dotazu, je-li síť kontaktů prázdná
		$network[] = 0;
		$network = implode( ',', $network );
		// dotaz na databázi
		$sql = "SELECT t.type_reference, t.type_name, s.*, UNIX_TIMESTAMP(s.posted) as timestamp, p.name as poster_name, r.name as profile_name FROM statuses s, status_types t, profile p, profile r WHERE t.ID=s.type AND p.user_id=s.poster AND r.user_id=s.profile AND ( p.user_id={$user} OR r.user_id={$user} OR ( p.user_id IN ({$network}) AND r.user_id IN ({$network}) ) ) ORDER BY s.ID DESC LIMIT {$offset}, 20";
		$this->registry->getObject('db')->executeQuery( $sql );
		if( $this->registry->getObject('db')->numRows() > 0 )
		{
			$this->empty = false;
			// průchod přes stavy - jejich identifikátory se uloží do pole, časy převedou do uživatelsky přívětivější podoby a záznam uloží do proudu
			while( $row = $this->registry->getObject('db')->getRows() )
			{
				$row['friendly_time'] = $this->generateFriendlyTime( $row['timestamp'] );
				$this->IDs[] = $row['ID'];
				$this->stream[] = $row;			
			}
		}
	}
	
	/**
	 * Získá proud
	 * @return array
	 */
	public function getStream()
	{
		return $this->stream;
	}
	
	/**
	 * Získá identifikátory stavů proudu
	 * @return array
	 */
	public function getIDs()
	{
		return $this->IDs;
	}
	
	/**
	 * Je proud prázdný?
	 * @return bool
	 */
	public function isEmpty()
	{
		return $this->empty;
	}
	
	/**
	 * Vytvoří uživatelsky přívětivý časový údaj
	 * @param int $time časová známka
	 * @return String
	 */
	private function generateFriendlyTime( $time )
	{
		$current_time = time();
		if( $current_time < ( $time + 60 ) )
		{
			// aktualizace proběhla před méně než minutou
			return "před méně než minutou";
		}
		elseif( $current_time < ( $time + 120 ) )
		{
			// před méně než dvěma minutami, ale více jak jednou - neříká se přece "před 1 minutami", že ano?
			return "před něco málo více jak minutou";
		}
		elseif( $current_time < ( $time + ( 60*60 ) ) )
		{
			// bylo to před méně než 60ti minutami, uvedeme tedy počet minut
			return "před " . round( ( $current_time - $time ) / 60 ) . " minutami";
		}
		elseif( $current_time < ( $time + ( 60*120 ) ) )
		{
			// před více jak jednou hodinou, ale méně jak dvěma - neříká se přece "před 1 hodinami", že ano?
			return "před něco málo více jak hodinou";
		}
		elseif( $current_time < ( $time + ( 60*60*24 ) ) )
		{
			// před méně než 24mi hodinami, uvedeme tedy počet hodin
			return "před " . round( ( $current_time - $time ) / (60*60) ) . " hodinami";
		}
		else
		{
			// před více jak jedním dnem, vzdáváme to a uvedeme datum a čas
			return date( 'H:i j.n.Y',$time);
		}
	}
	
	
	
	
	
}


?>