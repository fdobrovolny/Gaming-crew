<?php
/**
 * Třída pro přístup k databázi: základní abstrakce
 * 
 * @author Michael Peacock
 * @version 1.0
 */
class Mysqldb {
	
	/**
	 * Umožňuje více spojení s databází
	 * každé spojení se uloží jako prvek pole, aktivní spojení identifikuje samostatná proměnná (viz níže)
	 */
	private $connections = array();
	
	/**
	 * Specifikuje spojení, které se má použít
	 * voláním setActiveConnection($id) je možné aktivní spojení změnit
	 */
	private $activeConnection = 0;
	
	/**
	 * Provedené dotazy, jejichž výsledky se uložily do mezipaměti pro pozdější použití, primárně pro potřeby šablonového systému 
	 */
	private $queryCache = array();
	
	/**
	 * Připravená data uložená do mezipaměti pro pozdější použití, primárně pro potřeby šablonového systému
	 */
	private $dataCache = array();
	
	/**
	 * Počet provedených dotazů
	 */
	private $queryCounter = 0;
	
	/**
	 * Výsledek posledního provedeného dotazu
	 */
	private $last;
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Konstruktor databázového objektu
	 */
    public function __construct( Registry $registry ) 
    {
    	$this->registry = $registry;	
    }
    
    /**
     * Vytvoří nové spojení s databází
     * @param String adresa hostitele
     * @param String uživatelské jméno
     * @param String heslo
     * @param String požadovaná databáze
     * @return int the id of the new connection
     */
    public function newConnection( $host, $user, $password, $database )
    {
    	$this->connections[] = new mysqli( $host, $user, $password, $database );
    	$connection_id = count( $this->connections )-1;
    	if( mysqli_connect_errno() )
    	{
    		trigger_error('Chyba při pokusu o připojení k databázi. '.$this->connections[$connection_id]->error, E_USER_ERROR);
		}
    $this->executeQuery( "SET CHARACTER SET utf8"); 	

    	
    	return $connection_id;
    }
    
    /**
     * Ukončí aktivní spojení
     * @return void
     */
    public function closeConnection()
    {
    	$this->connections[$this->activeConnection]->close();
    }
    
    /**
     * Změní aktivní spojení pro následující dotazy
     * @param int identifikátor nového spojení
     * @return void
     */
    public function setActiveConnection( int $new )
    {
    	$this->activeConnection = $new;
    }
    
    /**
     * Uloží dotaz do mezipaměti dotazů pro pozdější zpracování
     * @param String dotaz
     * @return index dotazu v mezipaměti
     */
    public function cacheQuery( $queryStr )
    {
    	if( !$result = $this->connections[$this->activeConnection]->query( $queryStr ) )
    	{
		    trigger_error('Chyba při provádění dotazu a jeho ukládání do mezipaměti: '.$this->connections[$this->activeConnection]->error, E_USER_ERROR);
		    return -1;
		}
		else
		{
			$this->queryCache[] = $result;
			return count($this->queryCache)-1;
		}
    }
    
    /**
     * Získá počet záznamů v mezipaměti
     * @param int index dat v mezipaměti
     * @return int počet záznamů
     */
    public function numRowsFromCache( $cache_id )
    {
    	return $this->queryCache[$cache_id]->num_rows;	
    }
    
    /**
     * Získá záznamy z mezipaměti
     * @param int index dat v mezipaměti
     * @return array záznamy
     */
    public function resultsFromCache( $cache_id )
    {
    	return $this->queryCache[$cache_id]->fetch_array(MYSQLI_ASSOC);
    }
    
    /**
     * Uloží data do mezipaměti
     * @param array data
     * @return int index v poli dat mezipaměti
     */
    public function cacheData( $data )
    {
    	$this->dataCache[] = $data;
    	return count( $this->dataCache )-1;
    }
    
    /**
     * Získá data z mezipaměti
     * @param int index dat v mezipaměti
     * @return array data
     */
    public function dataFromCache( $cache_id )
    {
    	return $this->dataCache[$cache_id];
    }
    
    /**
     * Odstraní záznamy z databáze
     * @param String název tabulky, ze které se mají záznamy odstranit
     * @param String podmínka, kterou musí odstraňované záznamy splnit
     * @param int počet odstraňovaných záznamů
     * @return void
     */
    public function deleteRecords( $table, $condition, $limit )
    {
    	$limit = ( $limit == '' ) ? '' : ' LIMIT ' . $limit;
    	$delete = "DELETE FROM {$table} WHERE {$condition} {$limit}";
    	$this->executeQuery( $delete );
    }
    
    /**
     * Aktualizuje záznamy v databázi
     * @param String název tabulky
     * @param array asociativní pole změn
     * @param String podmínka
     * @return bool
     */
    public function updateRecords( $table, $changes, $condition )
    {
    	$update = "UPDATE " . $table . " SET ";
    	foreach( $changes as $field => $value )
    	{
    		$update .= "`" . $field . "`='{$value}',";
    	}
    	   	
    	// odstranění nadbytečného znaku "," na konci
    	$update = substr($update, 0, -1);
    	if( $condition != '' )
    	{
    		$update .= "WHERE " . $condition;
    	}
    	$this->executeQuery( $update );
    	
    	return true;
    	
    }
    
    /**
     * Vloží záznamy do databáze
     * @param String název tabulky
     * @param array asociativní pole vkládaných dat
     * @return bool
     */
    public function insertRecords( $table, $data )
    {
    	// inicializace proměnných pro názvy a hodnoty sloupců
    	$fields  = "";
		$values = "";
		
		// zaplnění proměnných
		foreach ($data as $f => $v)
		{
			
			$fields  .= "`$f`,";
			$values .= ( is_numeric( $v ) && ( intval( $v ) == $v ) ) ? $v."," : "'$v',";
		
		}
		
		// odstranění nadbytečného znaku "," na konci
    	$fields = substr($fields, 0, -1);
		// odstranění nadbytečného znaku "," na konci
    	$values = substr($values, 0, -1);
    	
		$insert = "INSERT INTO $table ({$fields}) VALUES({$values})";
		//echo $insert;
		$this->executeQuery( $insert );
		return true;
    }
    
    public function lastInsertID()
    {
	    return $this->connections[ $this->activeConnection]->insert_id;
    }
    
    /**
     * Provede dotaz
     * @param String dotaz
     * @return void
     */
    public function executeQuery( $queryStr )
    {
    	if( !$result = $this->connections[$this->activeConnection]->query( $queryStr ) )
    	{
		    trigger_error('Chyba při provádění dotazu: ' . $queryStr .' - '.$this->connections[$this->activeConnection]->error, E_USER_ERROR);
		  }
		  else
		  {
  			$this->last = $result;
	  	}
    }
    
    /**
     * Získá záznamy vrácené posledním provedeným dotazem
     * @return array 
     */
    public function getRows()
    {
    	return $this->last->fetch_array(MYSQLI_ASSOC);
    }
    
    public function numRows()
    {
	    return $this->last->num_rows;
    }
    
    /**
     * Získá počet záznamů ovlivněných posledním provedeným dotazem
     * @return int počet ovlivněných záznamů
     */
    public function affectedRows()
    {
    	return $this->last->affected_rows;
    }
    
    /**
     * Vyčistí data
     * @param String data, která se mají vyčistit
     * @return String vyčištěná data
     */
    public function sanitizeData( $value )
    {
    	// případné volání funkce stripslashes 
		if ( get_magic_quotes_gpc() ) 
		{ 
			$value = stripslashes ( $value ); 
		} 
		
		// nahrazení řídících znaků zástupnou sekvencí 
		if ( version_compare( phpversion(), "4.3.0" ) == "-1" ) 
		{
			$value = $this->connections[$this->activeConnection]->escape_string( $value );
		} 
		else 
		{
			$value = $this->connections[$this->activeConnection]->real_escape_string( $value );
		}
    	return $value;
    }
    
    /**
     * Destruktor objektu
     * ukončí všechna spojení navázaná s databázemi
     */
    public function __deconstruct()
    {
    	foreach( $this->connections as $connection )
    	{
    		$connection->close();
    	}
    }
}
?>