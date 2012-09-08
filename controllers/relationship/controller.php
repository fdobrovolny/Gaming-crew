<?php

class Relationshipcontroller {
	
	/**
	 * Konstruktor řadiče
	 * @param Registry $registry objekt registru
	 * @param bool $directCall volá konstruktor přímo framework (true) anebo jiný řadič (false)?
	 */
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		
		$urlBits = $this->registry->getObject('url')->getURLBits();
		if( isset( $urlBits[1] ) )
		{
			switch( $urlBits[1] )
			{
				case 'create':
					$this->createRelationship( intval( $urlBits[2] ) );
					break;	
				case 'approve':
					$this->approveRelationship( intval( $urlBits[2] ) );
					break;
				case 'reject':
					$this->rejectRelationship( intval( $urlBits[2] ) );
					break;
				default:
					break;
			}
			
		}
		else
		{
		}
		
	}
	
	private function createRelationship( $userb )
	{
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			$usera = $this->registry->getObject('authenticate')->getUser()->getUserID();
			$type = intval( $_POST['relationship_type'] );
			//echo '<pre>' . print_r( $_POST, true ) . '</pre>';
			require_once( FRAMEWORK_PATH . 'models/relationship.php');
			$relationship = new Relationship( $this->registry, 0, $usera, $userb, 0, $type );
			if( $relationship->isApproved() )
			{
				// odeslání e-mailu s informací o novém spojení
				/**
				 * Pamatujete si ještě, jak funguje třída pro odesílání e-mailů?
				 */
				 $this->registry->errorPage('Vztah vytvořen', 'Děkujeme za navázání spojení');
			}
			else
			{
				// odeslání e-mailu s informací o novém spojení čekajícím na schválení
				/**
				 * Pamatujete si ještě, jak funguje třída pro odesílání e-mailů?
				 */
				 $this->registry->errorPage('Požadavek odeslán', 'Děkujeme za žádost o spojení');
			}
			
		}
		else
		{
			$this->registry->errorPage('Přihlaste se prosím', 'Pouze přihlášení uživatelé mohou navazovat spojení s ostatními členy');
			// zobrazení chybové hlášky
		}
	}
	
	private function approveRelationship( $r )
	{
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			require_once( FRAMEWORK_PATH . 'models/relationship.php');
			$relationship = new Relationship( $this->registry, $r, 0, 0, 0, 0 );
			if( $relationship->getUserB() == $this->registry->getObject('authenticate')->getUser()->getUserID() )
			{
				// uživatel má právo vztah schválit
				$relationship->approveRelationship();
				$relationship->save();
				$this->registry->errorPage( 'Vztah úspěšně schválen', 'Děkujeme za schválení vztahu');
			}
			else
			{
				$this->registry->errorPage('Neplatný požadavek', 'Nemáte oprávnění schválit tento vztah');
			}
		}
		else
		{
			$this->registry->errorPage('Přihlaste se prosím', 'Pouze přihlášení uživatelé mohou schválit vztah');
		}
		
	}
	
	private function rejectRelationship( $r )
	{
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			require_once( FRAMEWORK_PATH . 'models/relationship.php');
			$relationship = new Relationship( $this->registry, $r, 0, 0, 0, 0 );
			if( $relationship->getUserB() == $this->registry->getObject('authenticate')->getUser()->getUserID() )
			{
				// uživatel má právo vztah odmítnout
				$relationship->delete();
				$this->registry->errorPage( 'Vztah odmítnut', 'Děkujeme za odmítnutí vztahu');
			}
			else
			{
				$this->registry->errorPage('Neplatný požadavek', 'Nemáte oprávnění tento vztah odmítnout');
			}
		}
		else
		{
			$this->registry->errorPage('Přihlašte se prosím', 'Pouze přihlášení uživatelé mohou odmítnout vztah');
		}
	}
	
	
	
}


?>