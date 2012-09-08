<?php
/**
 * Řadič kalendáře
 */
class Calendarcontroller {
	
	public function __construct( $registry, $directCall=true )
	{
		$this->registry = $registry;
		$urlBits = $this->registry->getObject('url')->getURLBits();
		switch( isset( $urlBits[1] )? $urlBits[1] : '' )
		{
			case 'birthdays':
				$this->birthdaysCalendar();
				break;
			default:
   		  $this->generateTestCalendar();
				break;
		}
	}
	
	private function birthdaysCalendar()
	{
		// připojení souboru třídy
		require_once( FRAMEWORK_PATH . 'lib/calendar/calendar.class.php' );
		// nastavení výchozího měsíce a roku na aktuální měsíc resp. rok
		$m = date('m');
		$y = date('Y');
		// nenastavil uživatel jiný měsíc nebo rok?
		if( isset( $_GET['month'] ) )
		{
			$m = intval( $_GET['month']);
			if( $m > 0 && $m < 13 )
			{
				
			}
			else
			{
				$m = date('m');
			}
		}
		if( isset( $_GET['year'] ) )
		{
			$y = intval( $_GET['year']);
		}
		// vytvoření instance objektu
		$calendar = new Calendar( '', $m, $y );
		// určení předchozího a následujícího měsíce a roku
		$nm = $calendar->getNextMonth()->getMonth();
		$ny = $calendar->getNextMonth()->getYear();
		$pm = $calendar->getPreviousMonth()->getMonth();
		$py = $calendar->getPreviousMonth()->getYear();
		
		// vložení informací o předchozím a následujícím roce a měsíci do šablony		
		$this->registry->getObject('template')->getPage()->addTag('nm', $nm );
		$this->registry->getObject('template')->getPage()->addTag('pm', $pm );
		$this->registry->getObject('template')->getPage()->addTag('ny', $ny );
		$this->registry->getObject('template')->getPage()->addTag('py', $py );
		// vložení názvu aktuálního měsíce a roku do šablony
		$this->registry->getObject('template')->getPage()->addTag('month_name', $calendar->getMonthName() );
		$this->registry->getObject('template')->getPage()->addTag('the_year', $calendar->getYear() );
		// nastavení počátečního dne týdne
		$calendar->setStartDay(1);		
		
		require_once( FRAMEWORK_PATH . 'models/relationships.php');
		$relationships = new Relationships( $this->registry );
		$idsSQL = $relationships->getIDsByUser( $this->registry->getObject('authenticate')->getUser()->getUserID() );

		$sql = "SELECT DATE_FORMAT(pr.user_dob, '%d' ) as profile_dob, pr.name as profile_name, pr.user_id as profile_id, ( ( YEAR( CURDATE() ) ) - ( DATE_FORMAT(pr.user_dob, '%Y' ) ) ) as profile_new_age FROM profile pr WHERE pr.user_id IN (".$idsSQL.") AND pr.user_dob LIKE '%-".($m < 10? '0' : '').$m."-%'";
		$this->registry->getObject('db')->executeQuery( $sql );
		$dates = array();
		$data = array();
		if( $this->registry->getObject('db')->numRows() > 0 )
		{
			while( $row = $this->registry->getObject('db')->getRows() )
			{
				$dates[] = $row['profile_dob'];
				$data[ intval($row['profile_dob']) ] = "<br />".$row['profile_name']." (". $row['profile_new_age'] . ")<br />";
			}
		}
		
		$calendar->setData( $data );
		// nastavení dnů, které se mají v kalendáři zvýraznit
		$calendar->setDaysWithEvents($dates);
		$calendar->buildMonth();
		// uspořádané dny týdne
		$this->registry->getObject('template')->dataToTags( $calendar->getDaysInOrder(),'cal_0_day_' ); 
		// dny měsíce
		$this->registry->getObject('template')->dataToTags( $calendar->getDates(),'cal_0_dates_' ); 
		// styly
		$this->registry->getObject('template')->dataToTags( $calendar->getDateStyles(),'cal_0_dates_style_' ); 
		// data
		$this->registry->getObject('template')->dataToTags( $calendar->getDateData(),'cal_0_dates_data_' ); 
		
		$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'bd-calendar.tpl.php', 'footer.tpl.php' );	
		
	}
	
	private function generateTestCalendar()
	{
		// připojení souboru třídy
		require_once( FRAMEWORK_PATH . 'lib/calendar/calendar.class.php' );
		// nastavení výchozího měsíce a roku na aktuální měsíc resp. rok
		$m = date('m');
		$y = date('Y');
		// nenastavil uživatel jiný měsíc nebo rok?
		if( isset( $_GET['month'] ) )
		{
			$m = intval( $_GET['month']);
			if( $m > 0 && $m < 13 )
			{
				
			}
			else
			{
				$m = date('m');
			}
		}
		if( isset( $_GET['year'] ) )
		{
			$y = intval( $_GET['year']);
		}
		// vytvoření instance objektu
		$calendar = new Calendar( '', $m, $y );
		// určení předchozího a následujícího měsíce a roku
		$nm = $calendar->getNextMonth()->getMonth();
		$ny = $calendar->getNextMonth()->getYear();
		$pm = $calendar->getPreviousMonth()->getMonth();
		$py = $calendar->getPreviousMonth()->getYear();
		
		// vložení informací o předchozím a následujícím roce a měsíci do šablony		
		$this->registry->getObject('template')->getPage()->addTag('nm', $nm );
		$this->registry->getObject('template')->getPage()->addTag('pm', $pm );
		$this->registry->getObject('template')->getPage()->addTag('ny', $ny );
		$this->registry->getObject('template')->getPage()->addTag('py', $py );
		// vložení názvu aktuálního měsíce a roku do šablony
		$this->registry->getObject('template')->getPage()->addTag('month_name', $calendar->getMonthName() );
		$this->registry->getObject('template')->getPage()->addTag('the_year', $calendar->getYear() );
		// nastavení počátečního dne týdne
		$calendar->setStartDay(1);		
		
		// vygenerování měsíce
		$calendar->buildMonth();
		// uspořádané dny týdne
		$this->registry->getObject('template')->dataToTags( $calendar->getDaysInOrder(),'cal_0_day_' ); 
		// dny měsíce
		$this->registry->getObject('template')->dataToTags( $calendar->getDates(),'cal_0_dates_' ); 
		// styly
		$this->registry->getObject('template')->dataToTags( $calendar->getDateStyles(),'cal_0_dates_style_' ); 
		// data
		$this->registry->getObject('template')->dataToTags( $calendar->getDateData(),'cal_0_dates_data_' ); 
		
		$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'test-calendar.tpl.php', 'footer.tpl.php' );
	}
}


?>