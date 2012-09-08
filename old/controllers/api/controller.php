<?php
/**
 * Řadič aplikačního rozhraní
 */
class Apicontroller{
	
	/**
	 * Dostupné řadiče, kterým je možné předat řízení
	 */
	private $allowableAPIControllers = array( 'profiles' );
	
	/**
	 * Data požadavku
	 */
	 private $requestData = array();
	
	/**
	 * Konstruktor
	 * @param Registry $registry objekt registru
	 * @param boolean $directCall
	 * @return void
	 */
	public function __construct( Registry $registry, $directCall=true )
	{
		$this->registry = $registry;
		$apiController = $registry->getObject('url')->getURLBit(1);
		$this->delegateControl( $apiController );
	}
	
	/**
	 * Předá řízení jinému řadiči
	 * @param String $apiController řadič, kterému se má řízení předat
	 * @return void
	 */
	private function delegateControl( $apiController )
	{
		
		if( $apiController != ''  && in_array( $apiController, $this->allowableAPIControllers ) )
		{
			require_once( FRAMEWORK_PATH . 'controllers/api/' . $apiController . '.php' );
			$api = new APIDelegate( $this->registry, $this );
		}	
		else
		{
			header('HTTP/1.0 404 Not Found');
       		exit();
		}
	}
	
	/**
	 * Vyžádá autentizaci pro přístup k aplikačnímu rozhraní - tuto metodu volají ostatní řadiče
	 * @return void
	 */
	public function requireAuthentication()
	{
		if( !isset( $_SERVER['PHP_AUTH_USER'] ) ) 
		{
    		header('WWW-Authenticate: Basic realm="DinoSpace API Login"');
    		header('HTTP/1.0 401 Unauthorized');
       		exit();
		} 
		else 
		{
			$user = $_SERVER['PHP_AUTH_USER'];
			$password = $_SERVER['PHP_AUTH_PW'];
			$this->registry->getObject('authenticate')->postAuthenticate( $user, $password, false );
			if( ! $this->registry->getObject('authenticate')->isLoggedIn() )
			{
				header('HTTP/1.0 401 Unauthorized');
       			exit();
			}
		}
	}
	
	/**
	 * Získá typ požadavku
	 * @return array
	 */
	public function getRequestData()
	{
		if( $_SERVER['REQUEST_METHOD'] == 'GET' )
		{
			$this->requestData = $_GET;
		}
		elseif( $_SERVER['REQUEST_METHOD'] == 'POST' )
		{
			$this->requestData = $_POST;
		}
		elseif( $_SERVER['REQUEST_METHOD'] == 'PUT' ) 
		{  
		    parse_str(file_get_contents('php://input'), $this->requestData );  
		} 
		elseif( $_SERVER['REQUEST_METHOD'] == 'DELETE' )
		{
			parse_str(file_get_contents('php://input'), $this->requestData );  
		}
		return $this->requestData;
	}
	
	
	
	
}




?>