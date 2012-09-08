<?php
class Streamcontroller{
	
	/**
	 * Objekt registru
	 */
	private $registry;
	
	/**
	 * Objekt modelu proudu stavů
	 */
	private $model;
	
	/**
	 * Konstruktor
	 * @param Registry $registry objekt registru
	 * @param bool $directCall příznak zda konstruktor volá přímo framework (true) nebo jiný řadič (false)
	 */
	public function __construct( Registry $registry, $directCall )
	{
		$this->registry = $registry;
		if( $this->registry->getObject('authenticate')->isLoggedIn() )
		{
			$this->generateStream();
		}
		else
		{
			$this->registry->errorPage( 'Přihlaste se prosím', 'Pouze přihlášení uživatelů mohou vidět, co se děje' );
		}		
	}
	
	private function generateStream( $offset=0 )
	{
		require_once( FRAMEWORK_PATH . 'models/stream.php' );
		$stream = new Stream( $this->registry );
		$stream->buildStream( $this->registry->getObject('authenticate')->getUser()->getUserID(), $offset );
		if( ! $stream->isEmpty() )
		{
			$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'stream/main.tpl.php', 'footer.tpl.php');
				
			$streamdata = $stream->getStream();
			
			$IDs = $stream->getIDs();
			$cacheableIDs = array();
			foreach( $IDs as $id )
			{
				$i = array();
				$i['status_id'] = $id;
				$cacheableIDs[] = $i;
			}
			
			
			$cache = $this->registry->getObject('db')->cacheData( $cacheableIDs );
			$this->registry->getObject('template')->getPage()->addTag( 'stream', array( 'DATA', $cache ) );
			
			foreach( $streamdata as $data )
			{
				$datatags = array();
				foreach( $data as $tag => $value )
				{
					$datatags[ 'status' . $tag ] = $value;
				}
				
				// vlastní stavové aktualizace aktivního uživatele
				if( $data['profile'] == $this->registry->getObject('authenticate')->getUser()->getUserID() && $data['poster'] == $this->registry->getObject('authenticate')->getUser()->getUserID() )
				{
					// dárek ode mě pro mě
					// http://www.imdb.com/title/tt0285403/quotes?qt0473119
					$this->registry->getObject('template')->addTemplateBit( 'stream-' . $data['ID'], 'stream/types/' . $data['type_reference'] . '-Spongebob-Squarepants-Costume-gift.tpl.php', $datatags );
				}
				elseif( $data['profile'] == $this->registry->getObject('authenticate')->getUser()->getUserID() )
				{
					// stavové aktualizace pro aktivního uživatele
					$this->registry->getObject('template')->addTemplateBit( 'stream-' . $data['ID'], 'stream/types/' . $data['type_reference'] . '-toself.tpl.php', $datatags );	
				}
				elseif( $data['poster'] == $this->registry->getObject('authenticate')->getUser()->getUserID() )
				{
					// stavové aktualizace od aktivního uživatele
					$this->registry->getObject('template')->addTemplateBit( 'stream-' . $data['ID'], 'stream/types/' . $data['type_reference'] . '-fromself.tpl.php', $datatags );	
				}
				elseif( $data['poster'] == $data['profile'] )
				{
					$this->registry->getObject('template')->addTemplateBit( 'stream-' . $data['ID'], 'stream/types/' . $data['type_reference'] . '-user.tpl.php', $datatags );		
				}
				else
				{
					// aktualizace v rámci sítě kontaktů
					$this->registry->getObject('template')->addTemplateBit( 'stream-' . $data['ID'], 'stream/types/' . $data['type_reference'] . '.tpl.php', $datatags );		
				}
				
			}
			
			// komentáře, líbí se a nelíbí se
			$status_ids = implode( ',', $IDs );
			$start = array();
			foreach( $IDs as $id )
			{
				$start[ $id ] = array();
			}
			
			// komentáře
			$comments = $start;
			$sql = "SELECT p.name as commenter, c.profile_post, c.comment FROM profile p, comments c WHERE p.user_id=c.creator AND c.approved=1 AND c.profile_post IN ({$status_ids})";
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() > 0 )
			{
				while( $comment = $this->registry->getObject('db')->getRows() )
				{
					$comments[ $comment['profile_post'] ][] = $comment;
				}
			}
			foreach( $comments as $status => $comments )
			{
				$cache = $this->registry->getObject('db')->cacheData( $comments );
				$this->registry->getObject('template')->getPage()->addTag( 'comments-' . $status, array( 'DATA', $cache )  );				
			}
			
			// líbí se a nelíbí se
			$likes = $start;
			$dislikes = $start;
			$sql = "SELECT i.status, p.name as iker, i.iker as iker_id, i.type as type FROM profile p, ikes i WHERE p.user_id=i.iker AND i.status IN ({$status_ids}) ";
			$this->registry->getObject('db')->executeQuery( $sql );
			if( $this->registry->getObject('db')->numRows() > 0 )
			{
				while( $ike = $this->registry->getObject('db')->getRows() )
				{
					if( $ike['type'] == 'likes' )
					{
						$likes[ $ike['status'] ][] = $ike;
					}
					else
					{
						$dislikes[ $ike['status'] ][] = $ike;
					}
					
				}
			}
			foreach( $likes as $status => $likeslist )
			{
				$cache = $this->registry->getObject('db')->cacheData( $likeslist );
				$this->registry->getObject('template')->getPage()->addTag( 'likes-' . $status, array( 'DATA', $cache )  );				
			}
			foreach( $dislikes as $status => $dislikeslist )
			{
				$cache = $this->registry->getObject('db')->cacheData( $dislikeslist );
				$this->registry->getObject('template')->getPage()->addTag( 'dislikes-' . $status, array( 'DATA', $cache )  );				
			}
			
		}
		else
		{
			$this->registry->getObject('template')->buildFromTemplates( 'header.tpl.php', 'stream/none.tpl.php', 'footer.tpl.php');
				
		}
		
		
		
		
		
	}
	
	
	
}
		
		
?>