<?php

/**
 * Rozšíření řadiče registrace
 */
class Registrationcontrollerextention{
	
	private $registry;
	private $extraFields = array();
	private $errors = array();
	private $submittedValues = array();
	private $sanitizedValues = array();
	private $errorLabels = array();
	
	public function __construct( $registry )
	{
		$this->registry = $registry;
		$this->extraFields['dino_name'] = array( 'friendlyname' => 'Jméno dinosaura', 'table' => 'profile', 'field' => 'dino_name', 'type' => 'text', 'required' => false );
		$this->extraFields['dino_breed'] = array( 'friendlyname' => 'Druh dinosaura', 'table' => 'profile', 'field' => 'dino_breed', 'type' => 'text', 'required' => false );
		$this->extraFields['dino_gender'] = array( 'friendlyname' => 'Pohlaví dinosaura', 'table' => 'profile', 'field' => 'dino_gender', 'type' => 'list', 'required' => false, 'options' => array( 'mužské', 'ženské') );
		$this->extraFields['dino_dob'] = array( 'friendlyname' => 'Datum narození dinosaura', 'table' => 'profile', 'field' => 'dino_dob', 'type' => 'DOB', 'required' => false );
	}
	
	public function getExtraFields()
	{
		return array_keys( $this->extraFields );
	}
	
	public function checkRegistrationSubmission()
	{
		$valid = true;
		foreach( $this->extraFields as $field => $data )
		{
			if( ( ! isset( $_POST['register_' . $field] ) || $_POST['register_' . $field] == '' ) && $data['required'] = true )
			{
				$this->submittedValues[ $field ] = $_POST['register_' . $field];
				$this->errorLabels['register_' . $field .'_label'] = 'error';
				$this->errors[] = 'Pole ' . $data['friendlyname'] . ' nemůže být prázdné';
				$valid = false;
			}
			elseif( $_POST['register_' . $field] == '' )
			{
				$this->submittedValues[ 'register_' . $field ] = '';
			}
			else
			{
				if( $data['type'] == 'text' )
				{
					$this->sanitizedValues[ 'register_' . $field ] = $this->registry->getObject('db')->sanitizeData( $_POST['register_' . $field] );
					$this->submittedValues['register_' . $field] = $_POST['register_' . $field];
				}
				elseif( $data['type'] == 'int' )
				{
					$this->sanitizedValues[ 'register_' . $field ] = intval( $_POST['register_' . $field] );
					$this->submittedValues['register_' . $field] = $_POST['register_' . $field];
				}
				elseif(  $data['type'] == 'list'  )
				{
					if( ! in_array( $_POST['register_' . $field], $data['options'] ) )
					{
						$this->submittedValues[ 'register_' .$field ] = $_POST['register_' . $field];
						$this->sanitizedValues[ 'register_' . $field ] = $this->registry->getObject('db')->sanitizeData( $_POST['register_' . $field] );
					
						$this->errorLabels['register_' . $field .'_label'] = 'error';
						$this->errors[] = 'Hodnota pole ' . $data['friendlyname'] . ' je neplatná';
				
						$valid = false;
					}
					else
					{
						$this->sanitizedValues[ 'register_' . $field ] = $_POST['register_' . $field];
						$this->submittedValues['register_' . $field] = $_POST['register_' . $field];
					}
				}
				else
				{
					$method = 'validate' . $data['type'];
					if( $this->$method( $_POST['register_' . $field] ) == true )
					{
						$this->sanitizedValues[ 'register_' . $field ] = $this->registry->getObject('db')->sanitizeData( $_POST['register_' . $field] );
						$this->submittedValues['register_' . $field] = $_POST['register_' . $field];
					}
					else
					{
						$this->sanitizedValues[ 'register_' . $field ] = $this->registry->getObject('db')->sanitizeData( $_POST['register_' . $field] );
						$this->submittedValues['register_' . $field] = $_POST['register_' . $field];
						$this->errors[] = 'Hodnota pole ' . $data['friendlyname'] . ' je neplatná';
						$valid = false;
					}
				}
			}
		}
		if( $valid == true )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	private function validateDOB( $value )
	{
		// logika vychází z http://www.smartwebby.com/PHP/datevalidation.asp
		if( (strlen( $value ) != 10 ) )
		{
			return false;
		}
		else
		{
			// nachází se v datu dva znaky /?
			if( substr_count( $value, '/' ) != 2 )
			{
				return false;
			}
			else
			{
				$date_parts = explode( '/', $value );
				// jedná se o platné datum?
				if( $date_parts[0] < 0 || $date_parts[0] > 31 )
				{
					return false;
				}
				else
				{
					// ověření měsíce
					if( $date_parts[1] < 0 || $date_parts[1] > 12 )
					{
						return false;
					}
					else
					{
						// ověření roku
						// poznámka: v roce 2099 bude nutné upravit ;)
						if( $date_parts[2] < 1880 || $date_parts[2] > 2100 )
						{
							return false;
						}
						else
						{
							return true;
						}
					}
				}
			}
		}
	}
	
	/**
	 * Vytvoří profil uživatele
	 * @param int $uid identifikátor uživatele
	 * @return bool
	 */
	public function processRegistration( $uid )
	{
		$tables = array();
		$tableData = array();
		
		// seskupení polí profilu, aby se vložení dat mohlo provést v rámci jednoho dotazu 
		foreach( $this->extraFields as $field => $data )
		{
			if( ! ( in_array( $data['table'], $tables ) ) )
			{
				$tables[] = $data['table'];
				$tableData[ $data['table'] ] = array( 'user_id' => $uid, $data['field'] => $this->sanitizedValues[ 'register_' . $field ]);
			}
			else
			{
				$tableData[ $data['table'] ][$data['field']] = $this->sanitizedValues[ 'register_' . $field ];
			}
		}
		foreach( $tableData as $table => $data )
		{
			$this->registry->getObject('db')->insertRecords( $table, $data );
		}
		return true;
	}
	
	public function getRegistrationErrors()
	{
		return $this->errors;
	}
	
	public function getRegistrationValues()
	{
		return $this->submittedValues;
	}
	
	public function getErrorLabels()
	{
		return $this->errorLabels;	
	}
	
	
}


?>