<?php
/**
 * Třída šablony 
 * Obsah a strukturu stránky spravuje samostatná třída.
 *
 * @version 1.0
 * @author Michael Peacock
 */
class Template {

	private $page;
	
	/**
	 * Připojí třídu stránky a vytvoří objekt stránky zajišťující správu obsahu a struktury stránky
	 * @param Object objekt registru
	 */
    public function __construct( Registry $registry ) 
    {
    	$this->registry = $registry;
	    include( FRAMEWORK_PATH . '/registry/page.class.php');
	    $this->page = new Page( $registry );
    }
    
    /**
     * Přidá šablonu do objektu stránky
     * @param String $tag značka, která určuje, kam šablonu vložit např. {ahoj}
     * @param String $bit šablona (cesta k souboru)
     * @return void
     */
    public function addTemplateBit( $tag, $bit, $data=array() )
    {
      if( strpos( $bit, 'views/' ) === false )
		  {
		    $bit = 'views/' . $this->registry->getSetting('view') . '/templates/' . $bit;
		  }
  		$this->page->addTemplateBit( $tag, $bit, $data );
    }
    
    /**
     * Vloží šablony do obsahu stránky
     * Aktualizuje obsah stránky
     * @return void
     */
    private function replaceBits()
    {
	    $bits = $this->page->getBits();
	    // cyklus přes šablony
	    foreach( $bits as $tag => $template )
	    {
		    $templateContent = file_get_contents( $template['template'] );
		    $tags = array_keys( $template['replacements'] );
		    $tagsNew = array();
		    foreach( $tags as $taga )
		    {
		    	$tagsNew[] = '{' . $taga . '}';
		    }
		    $values = array_values( $template['replacements'] );
		    $templateContent = str_replace( $tagsNew, $values, $templateContent );
		    $newContent = str_replace( '{' . $tag . '}', $templateContent, $this->page->getContent() );
		    $this->page->setContent( $newContent );
	    }
    }
    
    /**
     * Nahradí značky ve stránce patřičným obsahem
     * @return void
     */
    private function replaceTags( $pp = false )
    {
	    // určí značky ve stránce
	    if( $pp == false )
	    {
		     $tags = $this->page->getTags();
	    }
	    else
	    {
		     $tags = $this->page->getPPTags();
	    }
	   
	    // cyklus přes značky
	    foreach( $tags as $tag => $data )
	    {
		    // jedná-li se o pole, jednoduché vyhledání a nahrazení nestačí
		    if( is_array( $data ) )
		    {
			    if( $data[0] == 'SQL' )
			    {
				    // jedná se o dotaz s výsledkem uloženým v mezipaměti, značka se nahradí daty z databáze
				    $this->replaceDBTags( $tag, $data[1] );
			    }
			    elseif( $data[0] == 'DATA' )
			    {
				     // jedná se o data uložená v mezipaměti, značka se nahradí těmito daty
				    $this->replaceDataTags( $tag, $data[1] );
			    }
	    	}
	    	else
	    	{	
		    	// nahrazení obsahu	    	
		    	$newContent = str_replace( '{' . $tag . '}', $data, $this->page->getContent() );
		    	// aktualizace obsahu stránky
		    	$this->page->setContent( $newContent );
	    	}
	    }
    }
    
    /**
     * Nahradí obsah stránky daty z databáze
     * @param String $tag značka definující místo ve stránce
     * @param int $cacheId identifikátor dotazu v mezipaměti
     * @return void
     */
    private function replaceDBTags( $tag, $cacheId )
    {
	    $block = '';
		$blockOld = $this->page->getBlock( $tag );
		$apd = $this->page->getAdditionalParsingData();
		$apdkeys = array_keys( $apd );
		// cyklus přes záznamy, které jsou výsledkem dotazu
		while ($tags = $this->registry->getObject('db')->resultsFromCache( $cacheId ) )
		{
			$blockNew = $blockOld;
			
			// má dojít k dodatečnému zpracování dat?
			if( in_array( $tag, $apdkeys ) )
			{
				// ano, má
		        foreach ($tags as $ntag => $data) 
		       	{
		        	$blockNew = str_replace("{" . $ntag . "}", $data, $blockNew);
		        	// jedná se o značku vyžadující další zpracování?
		        	if( array_key_exists( $ntag, $apd[ $tag ] ) )
		        	{
			        	// ano, jedná
			        	$extra = $apd[ $tag ][$ntag];
			        	// splňuje značka podmínku?
			        	if( $data == $extra['condition'] )
			        	{
			        		
				        	// ano, splňuje - značka se nahradí daty
				        	$blockNew = str_replace("{" . $extra['tag'] . "}", $extra['data'], $blockNew);
			        	}
			        	else
			        	{
				        	// odstranění značky
				        	$blockNew = str_replace("{" . $extra['tag'] . "}", '', $blockNew);
			        	}
		        	} 
		        }
			}
			else
			{
				// vytvoření nového bloku s výsledky
				foreach ($tags as $ntag => $data) 
		       	{
		        	$blockNew = str_replace("{" . $ntag . "}", $data, $blockNew); 
		        }
			}
			
	        $block .= $blockNew;
		}
		$pageContent = $this->page->getContent();
		// odstraní oddělovač ze šablony => čistší kód HTML
		$newContent = str_replace( '<!-- START ' . $tag . ' -->' . $blockOld . '<!-- END ' . $tag . ' -->', $block, $pageContent );
		// aktualizace obsahu stránky
		$this->page->setContent( $newContent );
	}
    
	/**
     * Nahradí obsah stránky daty z mezipaměti
     * @param String $tag značka definující místo ve stránce
     * @param int $cacheId identifikátor dat v mezipaměti
     * @return void
     */
    private function replaceDataTags( $tag, $cacheId )
    {

	    $blockOld = $this->page->getBlock( $tag );
		$block = '';
		$tags = $this->registry->getObject('db')->dataFromCache( $cacheId );
		
		foreach( $tags as $key => $tagsdata )
		{
			$blockNew = $blockOld;
			foreach ($tagsdata as $taga => $data) 
	       	{
	        	$blockNew = str_replace("{" . $taga . "}", $data, $blockNew); 
	        }
	        $block .= $blockNew;
		}


		$pageContent = $this->page->getContent();
		$newContent = str_replace( '<!-- START '.$tag.' -->'.$blockOld.'<!-- END '.$tag.' -->', $block, $pageContent );
		$this->page->setContent( $newContent );
    }
    
    /**
     * Získá objekt stránky
     * @return Object 
     */
    public function getPage()
    {
	    return $this->page;
    }
    
    /**
     * Nastaví obsah stránky na základě souborů šablony, jejichž umístění se předává pomocí parametrů
     * @return void
     */
    public function buildFromTemplates()
    {
	    $bits = func_get_args();
	    $content = "";
	    foreach( $bits as $bit )
	    {
		    
		    if( strpos( $bit, 'views/' ) === false )
		    {
			    $bit = 'views/' . $this->registry->getSetting('view') . '/templates/' . $bit;
		    }
		    if( file_exists( $bit ) == true )
		    {
			    $content .= file_get_contents( $bit );
		    }
		    
	    }
	    $this->page->setContent( $content );
    }
    
    /**
     * Převede pole dat ve značky
     * @param array data
     * @param string prefix, který se připojí ke značce
     * @return void
     */
    public function dataToTags( $data, $prefix )
    {
	    foreach( $data as $key => $content )
	    {
		    $this->page->addTag( $prefix.$key, $content);
	    }
    }
    
    /**
     * Vloží titulek nastavený v rámci objektu stránky do obsahu stránky
     */
    public function parseTitle()
    {
	    $newContent = str_replace('<title>', '<title>'. $this->page->getTitle(), $this->page->getContent() );
	    $this->page->setContent( $newContent );
    }
    
    /**
     * Zpracuje objekt stránky a vytvoří výsledný obsah
     * @return void
     */
    public function parseOutput()
    {
	    $this->replaceBits();
	    $this->replaceTags(false);
	    $this->replaceBits();
	    $this->replaceTags(true);
	    $this->parseTitle();
    }
    
}
?>