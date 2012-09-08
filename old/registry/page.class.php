<?php

/**
 * Třída stránky
 *
 * @author Michael Peacock
 * @version 1.0
 */
class page {


	// elementy stránky
	
	// titulek stránky
	private $title = '';
	// značky
	private $tags = array();
	// vnořené značky - nahradí se po zpracování stránky
	// důvod: co když budou značky uložené v obsahu načteném např. z databáze - stránku musíme zpracovat a poté znovu kvůli vnořeným značkám 
	private $postParseTags = array();
	// šablony
	private $bits = array();
	// obsah stránky
	private $content = "";
	private $apd =  array();
	
	/**
	 * Vytvoří objekt stránky
	 */
    function __construct( Registry $registry ) 
    {
    	$this->registry = $registry;
    }
    
    /**
     * Získá titulek stránky
     * @return String
     */
    public function getTitle()
    {
    	return $this->title;
    }
    
    /**
     * Nastaví titulek stránky
     * @param String $title titulek stránky
     * @return void
     */
    public function setTitle( $title )
    {
	    $this->title = $title;
    }
    
    /**
     * Nastaví obsah stránky
     * @param String $content obsah stránky
     * @return void
     */
    public function setContent( $content )
    {
	    $this->content = $content;
    }
    
    /**
     * Přidá značku a hodnotu, kterou se má nahradit
     * @param String $key klíč, pod kterým se má značka uložit v poli značek
     * @param String $data data (může se jednat i o pole)
     * @return void
     */
    public function addTag( $key, $data )
    {
	    $this->tags[$key] = $data;
    }
    
    public function removeTag( $key )
    {
    	unset( $this->tags[$key] );
    } 
    
    /**
     * Získá značky spojené se stránkou
     * @return void
     */
    public function getTags()
    {
	    return $this->tags;
    }
    
    /**
     * Přidá vnořenou značku
     * @param String $key klíč, pod kterým se má značka uložit
     * @param String $data data
     * @return void
     */
    public function addPPTag( $key, $data )
    {
	    $this->postParseTags[$key] = $data;
    }
    
    /**
     * Získá vnořené značky ke zpracování
     * @return array
     */
    public function getPPTags()
    {
	    return $this->postParseTags;
    }
    
    /**
     * Přidá šablonu do stránky - samotný obsah nevkládá
     * @param String $tag značka identifikující místo, kam se má šablona vložit
     * @param String $bit název souboru šablony
     * @return void
     */
    public function addTemplateBit( $tag, $bit, $replacements=array() )
    {
    	$this->bits[ $tag ] = array( 'template' => $bit, 'replacements' => $replacements);
    }
    
    /**
	 * Přidá data k dodatečnému zpracování
	 * Používá se pro cyklické zpracování množin dat. Data tak mohou být závislá na iteraci - například určitá položka nabídky se může označit
	 * @param String $block blok, na který se podmínka vztahuje
	 * @param String $tag značka v rámci daného bloku
	 * @param String $condition podmínka - čemu se má značka rovnat
	 * @param String $extratag značka, která se nahradí, je-li podmínka splněna
	 * @param String $data data, kterými se značka nahradí v případě splnění podmínky
	 */
	public function addAdditionalParsingData($block, $tag, $condition, $extratag, $data)
	{
		$this->apd[$block] = array($tag => array('condition' => $condition, 'tag' => $extratag, 'data' => $data));
	}
    
    /**
     * Získá šablony, které se mají zpracovat
     * @return array pole šablon
     */
    public function getBits()
    {
	    return $this->bits;
    }
    
    public function getAdditionalParsingData()
    {
    	return $this->apd;
    }
    
    /**
     * Získá blok obsahu stránky
     * @param String $tag značka bloku ( <!-- START znacka --> blok <!-- END znacka --> )
     * @return String blok obsahu
     */
    public function getBlock( $tag )
    {
    	//echo $tag;
		preg_match ('#<!-- START '. $tag . ' -->(.+?)<!-- END '. $tag . ' -->#si', $this->content, $tor);	
		$tor = str_replace ('<!-- START '. $tag . ' -->', "", $tor[0]);
		$tor = str_replace ('<!-- END '  . $tag . ' -->', "", $tor);
		
		return $tor;
    }
    
    public function getContent()
    {
    	return $this->content;
    }
    
    public function getContentToPrint()
    {
    	$this->content = preg_replace ('#{form_(.+?)}#si', '', $this->content);	
    	$this->content = preg_replace ('#{nbd_(.+?)}#si', '', $this->content);	
    	$this->content = str_replace('</body>', '<!-- Vygenerováno fantastickou sociální sítí -->
</body>', $this->content );
	    return $this->content;
    }
  
}
?>