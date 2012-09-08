<?php

class mailout {


	private $message;
	private $headers;
	private $to;
	private $from;
	private $lock;
	private $type;
	private $error;
	private $subject;
	private $fromName;
	private $method;
	
	
     public function __construct( Registry $registry ) 
    {
		$this->registry = $registry;
    	$this->startFresh();
    }
    
    public function startFresh()
	{
		// není v konstruktoru, protože je třeba tuto metodu volat pro každý nový e-mail
		$this->lock = false;
		$this->error = 'Zpráva se neodeslala: ';
		$this->message = '';
	}
	
	/**
	 * Nastaví příjemce
	 * @param String příjemce
	 * @return bool
	 */
	public function setTo( $to )
	{
		if(eregi("\r",(urldecode($to))) || eregi("\n",(urldecode($to))))
		{
				
			// chyba, pokus o vložení hlavičky
				
			$this->lock();
			$this->error .= ' Pokus o vložení hlavičky do e-mailu, nejspíše snaha o spam';
			return false;
			
				
		}
		elseif( ! eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $to) )
		{
			// chyba, neplatný formát adresy
				
			$this->lock();
			$this->error .= ' Adresa příjemce nemá platný formát';
			return false;
				
		}
		else
		{
			// v pořádku, jdeme na to
			$this->to = $to;
			return true;
			
		}
		
	}
	
	/**
	 * Vytvoří e-mail na základě zprávy (místo šablony)
	 * @param String zpráva
	 * @return void
	 */
	public function buildFromText( $message )
	{
		$this->message .= $message;
	}
	
	/**
	 * Nastaví odesilatele (nutné provést před připojením za hlavičku e-mailu)
	 * @param String e-mailová adresa (není-li zadaná, použije se adresa uložená v registru)
	 * @return bool
	 */
	public function setSender( $email )
	{
		if( $email == '' )
		{
			// e-mail není zadaný, použije se e-mail uložený v registru
			$this->headers = 'From: '.$this->registry->getSetting('adminEmailAddress');
			$this->from = $this->registry->getSetting('adminEmailAddress');
			return true;
		}
		else
		{
			if( strpos( ( urldecode( $email ) ), "\r" ) === true || strpos( ( urldecode( $email ) ), "\n" ) === true )
			{
				// pokus o vložení hlavičky
				$this->lock();
				$this->error .= ' Pokus o vložení hlavičky, pravděpodobně za účelem spamu';
				return false;
				
			}
			elseif( ! preg_match( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})^", $email ) )
			{
				// neplatná adresa
				$this->lock();
				$this->error .= ' E-mailová adresa je neplatná';
				return false;
			}
			else
			{
				// vše v pořádku, můžeme pokračovat
				$this->headers = 'From: '.$email;
				$this->from = $email;
				return true;
			}

		}
	}
	
	public function setSenderIgnoringRules( $email )
	{
		$this->headers = 'From: ' . $email;
	}
	
	/**
	 * Připojí obsah za hlavičku e-mailu - nejdříve je však třeba zavolat setSender
	 * @param String data, která se mají připojit
	 * @return void
	 */
	public function appendHeader( $toAppend )
	{
		$this->headers .= "\r\n" .	$toAppend;
	}
	
	/**
	 * Uzamče e-mail a zabrání jeho odeslání
	 * @return void
	 */
	public function lock()
	{
		$this->lock = true;
	}
	
	public function buildFromTemplates()
    {
	    $bits = func_get_args();
	    $content = "";
	    foreach( $bits as $bit )
	    {
		    
		    if( strpos( $bit, 'emailtemplates/' ) === false )
		    {
			    $bit = 'emailtemplates/' . $bit;
		    }
		    if( file_exists( $bit ) == true )
		    {
			    $content .= file_get_contents( $bit );
		    }
		    
	    }
	    $this->message =  $content;
    }
    
    public function replaceTags( $tags )
    {
	    // projdeme přes všechny značky
	    if( sizeof($tags) > 0 )
	    {
	    	foreach( $tags as $tag => $data )
		    {
			    // jedná-li se o pole, pouhé vyhledání a nahrazení nestačí
			    if( ! is_array( $data ) )
			    {
			    	// nahrazení obsahu
			    	$newContent = str_replace( '{' . $tag . '}', $data, $this->message );
			    	// aktualizace obsahu zprávy
			    	$this->message = $newContent;
		    	}
		    }
	    }
	    
    }
    
    public function setMethod( $method )
	{
		$this->method = $method;
	}
	
	public function setSubject( $subject )
	{
		$this->subject = $subject;
	}
	
	/** 
	 * Odešle e-mail
	 * @return void
	 */
	public function send()
	{
		switch( $this->method )
		{
			case 'sendmail':
				return $this->sendWithSendmail();
				break;
			case 'smtp':
				return $this->sendWithSmtp();
				break;
			default:
				return $this->sendWithSendmail();
				
		}
	}
	
	/**
	 * Odešle e-mail pomocí funkce mail
	 * @return void
	 */
	public function sendWithSendmail()
	{
		if($this->lock == true)
		{
			return false;
		}
		else
		{
			if( ! @mail($this->to, $this->subject, $this->message, $this->headers) )
			{
				$this->error .= ' chyba při odesílání e-mailu pomocí funkce mail';
				return false;
			}
			else
			{
				return true;
			}
		}
	}
	
	public function setFromName( $name )
	{
		$this->fromName = $name;
	}
	
	public function sendWithSMTP()
	{
  		
	}
	
	

    
    
}
?>