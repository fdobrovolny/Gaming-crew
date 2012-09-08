<?php

/**
 * Stránkovací třída
 * Usnadňuje stránkování výsledků dotazu na databázi
 */
class Pagination {
	
	/**
	 * Dotaz, jehož výsledky se budou stránkovat
	 */
	private $query = "";
	
	/**
	 * Výsledný dotaz, který se provede
	 */
	private $executedQuery = "";
	
	/**
	 * Maximální počet výsledků na stránku
	 */
	private $limit = 25;
	
	/**
	 * Posunutí v rámci výsledků - tj. stránka, na které jsme (-1)
	 */
	private $offset = 0;
	
	/**
	 * Metoda stránkování
	 */
	private $method = 'query';
	
	/**
	 * Identifikátor dotazu v mezipaměti, stránkují-li se výsledky uložené v mezipaměti
	 */
	private $cache;
	
	/**
	 * Výsledek dotazu, stránkuje-li se přímo výsledek dotazu
	 */
	private $results;
	
	/**
	 * Počet záznamů původního dotazu
	 */
	private $numRows;
	
	/**
	 * Počet záznamů na aktulní stránce (slouží především pro poslední stránku výpisu, která nemusí obsahovat maximální počet záznamů)
	 */
	private $numRowsPage;
	
	/**
	 * Celkový počet stránek
	 */
	private $numPages;
	
	/**
	 * Jedná se o první stránku?
	 */
	private $isFirst;
	
	/**
	 * Jedná se o poslední stránku?
	 */
	private $isLast;
	
	/**
	 * Aktuální stránka
	 */
	private $currentPage;
	
	/**
	 * Konstruktor
	 * @param Object objekt registru
	 * @return void
	 */
    function __construct( Registry $registry) 
    {
    	$this->registry = $registry;
    }
    
    /**
     * Nastaví dotaz, který se má stránkovat
     * @param String $sql dotaz
     * @return void
     */
    public function setQuery( $sql )
    {
    	$this->query = $sql;
    }
    
    /**
     * Nastaví limit počtu záznamů na stránku
     * @param int $limit limit
     * @return void
     */
    public function setLimit( $limit )
    {
    	$this->limit = $limit;	
    }
    
    /**
     * Nastaví posunutí (pokud je posunutí 1, přesuneme se na další stránku výsledků)
     * @param int $offset posunutí
     * @return void
     */
    public function setOffset( $offset )
    {
    	$this->offset = $offset;
    }
    
    /**
     * Nastaví metodu stránkování
     * @param String $method [cache|do]
     * @return void
     */
    public function setMethod( $method )
    {
    	$this->method = $method;
    }
    
    /**
     * Zpracuje dotaz a nastaví parametry stránkování
     * @return bool
     */
    public function generatePagination()
    {
    	$temp_query = $this->query;
    	
    	// kolik je výsledků?
    	$this->registry->getObject('db')->executeQuery( $temp_query );
    	$nums = $this->registry->getObject('db')->numRows();
    	$this->numRows = $nums;
    	
    	// nastavení limitu
    	$limit = " LIMIT ";
    	$limit .= ( $this->offset * $this->limit ) . ", " . $this->limit;
    	$temp_query = $temp_query . $limit;
    	$this->executedQuery = $temp_query;
    	if( $this->method == 'cache' )
    	{
    		$this->cache = $this->registry->getObject('db')->cacheQuery( $temp_query );
    	}
    	elseif( $this->method == 'do' )
    	{
    		$this->registry->getObject('db')->executeQuery( $temp_query );
    		$this->results = $this->registry->getObject('db')->getRows();
    	}
    	
    	// výpočet několika hodnot, které se budou hodit řadiči
		
		// počet stránek
		$this->numPages = ceil($this->numRows / $this->limit);
		
		// jedná se o první stránku?
		$this->isFirst = ( $this->offset == 0 ) ? true : false;
		
		// jedná se o poslední stránku?		
		$this->isLast = ( ( $this->offset + 1 ) == $this->numPages ) ? true : false;
		
		// aktuální stránka
		$this->currentPage = ( $this->numPages == 0 ) ? 0 : $this->offset +1;
		$this->numRowsPage = $this->registry->getObject('db')->numRows();
		if( $this->numRowsPage == 0 )
		{
			return false;
		}
		else
		{
			return true;
		}
    	
    }
    
    /**
     * Získá obsah mezipaměti
     * @return int
     */
    public function getCache()
    {
    	return $this->cache;
    }
    
    /**
     * Získá výsledky
     * @return array
     */
    public function getResults()
    {
    	return $this->results;
    }
    
    /**
     * Získá počet stránek výsledků
     * @return int
     */
    public function getNumPages()
    {
    	return $this->numPages;
    }
    
    /**
     * Jedná se o první stránku výsledků?
     * @return bool
     */
    public function isFirst()
    {
    	return $this->isFirst;
    }
    
    /**
     * Jedná se o poslední stránku výsledků?
     * @return bool
     */
    public function isLast()
    {
    	return $this->isLast;
    }
    
    /**
     * Získá aktuální stránku výsledků
     * @return int
     */
    public function getCurrentPage()
    {
    	return $this->currentPage;
    }
    
    public function getNumRowsPage()
    {
    	return $this->numRowsPage;    	
    }
}
?>