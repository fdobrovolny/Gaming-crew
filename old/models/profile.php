<?php

/**
 * Model profilu
 */
class Profile{
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Identifikátor profilu
	 */
	private $id;
	
	/**
	 * Položky, jejichž hodnoty je možné uložit voláním metody save
	 */
	private $savable_profile_fields = array( 'name', 'dino_name', 'dino_dob', 'dino_breed', 'dino_gender', 'photo', 'bio' );
	
	/**
	 * Identifikátor uživatele
	 */
	private $user_id;
	
	/**
	 * Jméno uživatele
	 */
	private $name;
	
	/**
	 * Jméno dinosaura
	 */
	private $dino_name;
	
	/**
	 * Datum narození dinosaura
	 */
	private $dino_dob;
	
	/**
	 * Biografie uživatele
	 */
	private $bio;
	
	/**
	 * Druh dinosaura
	 */
	private $dino_breed;
	
	/**
	 * Pohlaví dinosaura
	 */
	private $dino_gender;
	
	/**
	 * Fotografie uživatele
	 */
	private $photo;

	private $valid;
	
	/**
	 * Konstruktor profilu
	 * @param Registry $registry objekt registru
	 * @param int $id identifikátor profilu
	 * @return void
	 */
	public function __construct( Registry $registry, $id=0 )
	{
		$this->registry = $registry;
		if( $id != 0 )
		{
			$this->id = $id;
			// je-li zadaný identifikátor, nastaví se hodnoty vlastností na základě záznamu v databázi
			$sql = "SELECT * FROM profile WHERE user_id=" . $this->id;
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() == 1 )
			{
				$this->valid = true;
				
				$data = $this->registry->getObject('db')->getRows();
				// nastavení hodnot vlastností
				foreach( $data as $key => $value )
				{
					$this->$key = $value;
				}
			}
			else
			{
				$this->valid = false;
			}
		}
		else
		{
			$this->valid = false;
		}
	}
	
	/**
	 * Jedná se o platný profil?
	 * @return bool
	 */
	public function isValid()
	{
		return $this->valid;
	}

  /**
   * Získá data profilu ve formě pole
   * @return array
   */
  public function toArray( $prefix='' )
  {
    $r = array();
    foreach( $this as $field => $data )
    {
      if( ! is_object( $data ) && ! is_array( $data ) )
      {
        $r[ $field ] = $data;
      }
    }
    return $r;
  }

	/**
	 * Nastaví jméno uživatele
	 * @param String $name jméno
	 * @return void
	 */
	public function setName( $name )
	{
		$this->name = $name;
	}
	
	/**
	 * Nastaví jméno dinosaura
	 * @param String $name jméno
	 * @return void
	 */
	public function setDinoName( $name )
	{
		$this->dino_name = $name;
	}
	
	/**
	 * Nastaví datum narození dinosaura
	 * @param String $dob datum narození
	 */
	public function setDinoDOB( $dob )
	{
		$this->dino_dob = $dob;
	}
	
	/**
	 * Nastaví druh dinosaura uživatele
	 * @param String $breed druh
	 * return void
	 */
	public function setDinoBreed( $breed )
	{
		$this->dino_breed = $breed;
	}
	
	/**
	 * Nastaví pohlaví dinosaura uživatele
	 * @param String $gender pohlaví
	 * @param boolean $checked příznak jestli řadič ověřil pohlaví anebo je třeba to udělat zde
	 * @return void
	 */
	public function setDinoGender( $gender, $checked=true )
	{
		if( $checked == true )
		{
			$this->dino_gender = $gender;
		}
		else
		{
			$genders = array('mužské', 'ženské');
			if( in_array( $gender, $genders ) )
			{
				$this->dino_gender = $gender;
			}
		}
	}
	
	/**
	 * Nastaví biografii uživatele
	 * @param String biografie
	 * @return void
	 */
	public function setBio( $bio )
	{
		$this->bio = $bio;
	}
	
	/**
	 * Nastaví fotografii uživatele
	 * @param String photo name
	 * @return void
	 */
	public function setPhoto( $photo )
	{
		$this->photo = $photo;
	}
	
	/**
	 * Uloží profil uživatele
	 * @return bool
	 */
	public function save()
	{
		// ověření, jestli je uživatel oprávněný uložit profil
		if( $this->registry->getObject('authenticate')->isLoggedIn() && ( $this->registry->getObject('authenticate')->getUser()->getUserID() ==  $this->id || $this->registry->getObject('authenticate')->getUser()->isAdmin() == true  ) )
		{
			// profil ukládá buďto sám uživatele, kterému profil patří, anebo administrátor
			$changes = array();
			foreach( $this->savable_profile_fields as $field )
			{
				$changes[ $field ] = $this->$field;
			}
			$this->registry->getObject('db')->updateRecords( 'profile', $changes, 'user_id=' . $this->id );
			if( $this->registry->getObject('db')->affectedRows() == 1 )
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Převede data profilu na značky
	 * @param String $prefix prefix pro značky
	 * @return void
	 */
	public function toTags( $prefix='' )
	{
		foreach( $this as $field => $data )
		{
			if( ! is_object( $data ) && ! is_array( $data ) )
			{
				$this->registry->getObject('template')->getPage()->addTag( $prefix.$field, $data );
			}
		}
	}
	
	/**
	 * Získá jméno uživatele
	 * @return String
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Získá fotografii uživatele
	 * @return String
	 */
	public function getPhoto()
	{
		return $this->photo;
	}
	
	/**
	 * Získá identifikátor uživatele
	 * @return int
	 */
	public function getID()
	{
		return $this->user_id;
	}
	
}

?>