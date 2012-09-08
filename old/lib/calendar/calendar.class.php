<?php
/**
 * Třída kalendáře
 */
class Calendar{
	
	/**
	 * Rok reprezentovaný kalendářem
	 */
	private $year; 
	
	/**
	 * Aktuální den reprezentovaný kalendářem
	 */
	private $day;
	
	/**
	 * Aktuální měsíc reprezentovaný kalendářem
	 */
	private $month;
	
	/**
	 * Den, kterým začíná týden, v případě ČR se jedná o pondělí
	 */
	private $startDay = 1;
	
	/**
	 * Pole dnů týdne
	 */
	private $days = array('Ne','Po','Út','St','Čt','Pá','So');
	
	/**
	 * Pole měsíců roku
	 */
	private $months = array(	0=> '',
							1 => 'Leden',
							2 => 'Únor',
							3 => 'Březen',
							4 => 'Duben',
							5 => 'Květen',
							6 => 'Červen',
							7 => 'Červenec',
							8 => 'Srpen',
							9 => 'Září',
							10 => 'Říjen',
							11 => 'Listopad',
							12 => 'Prosinec'	
						);
	
	/**
	 * Dny týdne, seřazené podle určeného počátečního dne
	 */
	private $orderedDays;
	
	/**
	 * Název aktuálního měsíce
	 */
	private $monthName;
	
	/**
	 * Dny měsíce 
	 */
	private $dates=array();
	
	/**
	 * Styly pro dny měsíce
	 */
	private $dateStyles=array();
	
	/**
	 * Seznam dní, ke kterým se váže událost
	 */
	private $daysWithEvents = array();
	
	/**
	 * Data spojená se dny
	 */
	private $data=array();
	
	/**
	 * Data spojená se dny, pole o 42 prvcích
	 */
	private $dateData = array();
	 
	/**
	 * Konstruktor kalendáře
	 * @param int $day vybraný den kalendáře
	 * @param int $month měsíc reprezentovaný kalendářem
	 * @param int $year rok reprezentovaný kalendářem
	 * @return void
	 */
	public function __construct( $day, $month, $year )
	{
		$this->year = ( $year == '' ) ? date('y') : $year;
		$this->month =  ( $month == '' ) ? date('m') : $month;
		$this->day = ( $day == '' ) ? date('d') : $day;
		$this->monthName =  $this->months[ ltrim( $this->month, '0') ];
	}
	
	/**
	 * Sestaví měsíc reprezentovaný kalendářem
	 * @return void
	 */
	public function buildMonth()
	{
		$this->orderedDays = $this->getDaysInOrder();
		
		$this->monthName =  $this->months[ ltrim( $this->month, '0') ];
		
		// začátek zadaného měsíce
		$start_of_month = getdate( mktime(12, 0, 0, $this->month, 1, $this->year ) );
		
		$first_day_of_month = $start_of_month['wday'];
		
		$days = $this->startDay - $first_day_of_month;
		
		if( $days > 1 )
		{
			// posunutí zpět
			$days -= 7;
			
		}

		$num_days = $this->daysInMonth($this->month, $this->year);
		// 42 průchodů
		$start = 0;
		$cal_dates = array();
		$cal_dates_style = array();
		$cal_events = array();
		while( $start < 42 )
		{
			// přeskočení neexistujících dnů
			if( $days < 0 )
			{
				$cal_dates[] = '';
				$cal_dates_style[] = 'calendar-empty';
				$cal_dates_data[] = '';
			}
			else
			{
				if( $days < $num_days )
				{
					// skutečné dny
					$cal_dates[] = $days+1;
					if( in_array( $days+1, $this->daysWithEvents ) )
					{
						$cal_dates_style[] = 'has-events';
						$cal_dates_data[] = $this->data[ $days+1 ];
					}
					else
					{
						$cal_dates_style[] = '';
						$cal_dates_data[] = '';
					}
					
				}
				else
				{
					// nadbytečné dny
					$cal_dates[] = '';
					$cal_dates_style[] = 'calendar-empty';
					$cal_dates_data[] = '';
				}
				
			}
			// navýšení hodnot
			$start++;
			$days++;
		}
		
		// hotovo
		$this->dates = $cal_dates;
		$this->dateStyles = $cal_dates_style;
		$this->dateData = $cal_dates_data;
	}
	
	public function setData( $data )
	{
		$this->data = $data;
	}
	
	/**
	 * Získá dny
	 * Kalendář má celkem 41 pozic pro dny, tato metoda získá obsah těchto 41 míst a určí tak, které z nich mají mít čísla a které mají zůstat prázdné
	 * @return array
	 */
	public function getDates()
	{
		return $this->dates;
	}
	
	
	/**
	 * Získá styly dnů
	 * @return array pole stylů dnů vytvořené metodou buildMonth
	 */
	public function getDateStyles()
	{
		return $this->dateStyles;
	}
	
	/**
	 * Získá další měsíc
	 * @return Object objekt kalendáře
	 */
	public function getNextMonth()
	{
		$nm = new Calendar( '', ( ($this->month < 12 ) ? $this->month + 1 : 1), ( ( $this->month == 12 ) ? $this->year + 1 : $this->year ) );
		return $nm;
	}
	
	/**
	 * Získá předchozí měsíc
	 * @return Object objekt kalendáře
	 */
	public function getPreviousMonth()
	{
		$pm = new Calendar( '', ( ( $this->month > 1 ) ? $this->month - 1 : 12 ), ( ( $this->month == 1 ) ? $this->year-1 : $this->year ) );
		return $pm;
	}
	
	public function setStartDay( $day )
	{
		$this->start_day = $day;
	}
	
	public function getDateData()
	{
		return $this->dateData;
	}
	
	/**
	 * Nastaví měsíc
	 * @param int $m
	 * @return void
	 */
	public function setMonth( $m )
	{
		$this->month = $m; 	
	}
	
	/**
	 * Nastaví dny, ke kterým se vážou události (jejich styl se adekvátně upraví)
	 * @param array $days
	 * @return void
	 */
	public function setDaysWithEvents( $days )
	{
		$this->daysWithEvents = $days;
	}
	
	/**
	 * Nastaví rok
	 * @param int $y
	 */
	public function setYear( $y )
	{
		$this->year = $y;
	}
	
	/**
	 * Získá uspořádané pole dnů
	 * @return array pole dnů (jeho prvky jsou řetězce)
	 */
	function getDaysInOrder()
	{
		$ordered_days = array();
		for( $i = 0; $i < 7; $i++ )
		{
			$ordered_days[] = $this->days[ ( $this->startDay + $i ) % 7 ];
		}
		return $ordered_days;
	}
	
	/**
	 * Získá počet dnů měsíce
	 * @param int $m měsíc
	 * @param int $y rok
	 * @return int počet dnů v měsíci
	 */
	function daysInMonth($m, $y)
	{
		if( $m < 1 || $m > 12 )
		{
			return 0;
		}
		else
		{
			// 30: 9, 4, 6, 11
			if( $m == 9 || $m == 4 || $m == 6 || $m == 11 )
			{
				return 30;
			}
			else if( $m != 2 )
			{
				// ostatní mají 31 dnů
				return 31;
			}
			else
			{
				// kromě února
				if( $y % 4 != 0 )
				{
					// který má 28 dnů
					return 28;
				}
				else
				{
					if( $y % 100 != 0 ) 
					{
						// a u přestupných roků 29
						return 29;
					}
					else
					{
						if( $y % 400 != 0 )
						{
							// 28 dnů
							return 28;
						}
						else
						{
							// přestupný rok, 29 dnů
							return 29;
						}
					}
				}
			}
		}
	}
	
	/**
	 * Získá měsíc
	 * @return int
	 */
	public function getMonth()
	{
		return $this->month;
		
	}
	
	/**
	 * Získá rok
	 * @return int
	 */
	public function getYear()
	{
		return $this->year;
	}
	
	/**
	 * Získá název měsíce
	 * @return String
	 */
	public function getMonthName()
	{
		return $this->monthName;
	}
	
	
	
	
}

?>