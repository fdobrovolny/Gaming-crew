<?php

/**
 * Třída pro práci s obrázky
 * @author Michael Peacock
 */
class Imagemanager
{
	private $type = '';
	private $uploadExtentions = array( 'png', 'jpg', 'jpeg', 'gif' );
	private $uploadTypes = array( 'image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png' );
	private $image;
	private $name;
	
	public function __construct(){}
	
	/**
	 * Načte obrázek ze souborového systému
	 * @param String $filepath cesta k souboru
	 * @return void	
	 */
	public function loadFromFile( $filepath )
	{
		$info = getimagesize( $filepath );
      	$this->type = $info[2];
     	if( $this->type == IMAGETYPE_JPEG ) 
     	{
        	$this->image = imagecreatefromjpeg($filepath);
      	} 
      	elseif( $this->type == IMAGETYPE_GIF ) 
      	{
        	$this->image = imagecreatefromgif($filepath);
        } 
        elseif( $this->type == IMAGETYPE_PNG ) 
        {
	        $this->image = imagecreatefrompng($filepath);
      	}
	}
	
	/**
	 * Získá šířku obrázku
	 * @return int
	 */
	public function getWidth() 
	{
    	return imagesx($this->image);
	}
   	
   	/**
  	 * Získá výšku obrázku
   	 * @return int
   	 */
	public function getHeight() 
   	{
      return imagesy($this->image);
   	}
   	
   	/**
   	 * Změní velikost obrázku
   	 * @param int $x šířka
   	 * @param int $y výška
   	 * @return void
   	 */
   	public function resize( $x, $y )
   	{
	   	$new = imagecreatetruecolor($x, $y);
      	imagecopyresampled($new, $this->image, 0, 0, 0, 0, $x, $y, $this->getWidth(), $this->getHeight());
      	$this->image = $new;
   	}
   	
   	/**
   	 * Změní velikost obrázku a upraví šířku podle nové výšky
   	 * @param int $height výška
   	 * @return void
   	 */
   	public function resizeScaleWidth( $height )
   	{
      	$width = $this->getWidth() * ( $height / $this->getHeight() );
      	$this->resize( $width, $height );
   	}
   	
   	/**
   	 * Změní velikost obrázku a upraví výšku podle nové šířky
   	 * @param int $width šířka
   	 * @return void
   	 */
   	public function resizeScaleHeight( $width )
   	{
		$height = $this->getHeight() * ( $width / $this->getWidth() );
      	$this->resize( $width, $height );
   	}
   	
   	/**
   	 * Změní velikost obrázku na základě procentuální hodnoty
   	 * @param int $percentage faktor změny velikosti
   	 * @return void
   	 */
   	public function scale( $percentage )
   	{
	   	$width = $this->getWidth() * $percentage / 100;
      	$height = $this->getheight() * $percentage / 100; 
      	$this->resize( $width, $height );
   	}
   	
   	/**
   	 * Zobrazí obrázek v prohlížeči - metoda by se měla volat před odesláním jakéhokoli jiného výstupu prohlížeči a mělo by za ní následovat volání funkce exit
   	 * @return void
   	 */
   	public function display()
   	{
	   	$type = '';
	   	if( $this->type == IMAGETYPE_JPEG )
	   	{
		   	$type = 'image/jpeg';
	   	}
	   	elseif( $this->type == IMAGETYPE_GIF )
	   	{
		   	$type = 'image/gif';
	   	}
	   	elseif( $this->type == IMAGETYPE_PNG )
	   	{
		   	$type = 'image/png';
	   	}
	   	
	   	header('Content-Type: ' . $type );
	   	
	   	if( $this->type == IMAGETYPE_JPEG )
	   	{
		   	imagejpeg( $this->image );
	   	}
	   	elseif( $this->type == IMAGETYPE_GIF )
	   	{
		   	imagegif( $this->image );
	   	}
	   	elseif( $this->type == IMAGETYPE_PNG )
	   	{
		   	imagepng( $this->image );
	   	}
	   	
   	}
	
	/**
	 * Zpracuje obrázek odeslaný uživatelem
	 * @param String $postfield klíč pole _FILES, pod kterým je nahraný soubor přístupný
	 * @param String $moveto cílové umístění pro soubor
	 * @param String $name_prefix prefix cílového souboru
	 * @return boolean
	 */
	public function loadFromPost( $postfield, $moveto, $name_prefix='' )
	{
		if( is_uploaded_file( $_FILES[ $postfield ]['tmp_name'] ) )
		{
			$i = strrpos( $_FILES[ $postfield ]['name'], '.');
	    	if (! $i ) 
	    	{ 
	    		// soubor nemá příponu
		   		return false; 
		   	}
		   	else
		   	{
			   	$l = strlen(  $_FILES[ $postfield ]['name'] ) - $i;
		        $ext = strtolower ( substr(  $_FILES[ $postfield ]['name'], $i+1, $l ) );
		        
		        if( in_array( $ext, $this->uploadExtentions ) )
		        {
			        if( in_array( $_FILES[ $postfield ]['type'], $this->uploadTypes ) )
			        {

				        	$name = str_replace( ' ', '', $_FILES[ $postfield ]['name'] );
				        	$this->name = $name_prefix . $name;
				        	$path = $moveto . $name_prefix.$name;
				        	move_uploaded_file( $_FILES[ $postfield ]['tmp_name'] , $path );
				        	$this->loadFromFile( $path );
				        	return true;
		
			        }
			        else
			        {
			        	// neplatný typ souboru
				        return false;
			        }
		        }
		        else
		        {
		        	// neplatná přípona souboru
			        return false;
		        }
		   	}
	        
		}
		else
		{
			// nejedná se o soubor nahraný uživatelem
			return false;
		}
	}
	
	/**
	 * Získá název souboru
	 * @return String
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Uloží změny souboru, například po změně velikosti
	 * @param String $location umístění souboru
	 * @param String $type typ obrázku
	 * @param int $quality kvalita obrázku
	 * @return void
	 */
	public function save( $location, $type='', $quality=100 )
	{
		$type = ( $type == '' ) ? $this->type : $type;
		
		if( $type == IMAGETYPE_JPEG ) 
		{
        	imagejpeg( $this->image, $location, $quality);
    	} 
    	elseif( $type == IMAGETYPE_GIF ) 
    	{
        	imagegif( $this->image, $location );         
      	} 
      	elseif( $type == IMAGETYPE_PNG ) 
      	{
        	imagepng( $this->image, $location );
        }
	}
}

?>