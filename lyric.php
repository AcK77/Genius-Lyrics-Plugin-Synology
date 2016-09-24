<?php
// https://global.download.synology.com/download/Document/DeveloperGuide/AS_Guide.pdf
// Based on Frank Lai module with permission https://bitbucket.org/franklai/synologylyric/

class Ac_KGenius
{
    public function __construct () {}

	public function getLyricsList ( $artist, $title, $info )
	{
        $song_info = $this->parseSearchResult ( $this->getContent ( "http://genius.com/search?q=" . urlencode ( $artist . " " . $title ) ), $artist, $title );
        if ( $song_info === FALSE ) return FALSE;

		//Return Track informations
        $info->addTrackInfoToList(
            $song_info['artist'],
            $song_info['title'],
            $song_info['id'], //Lyrics page link
            ""
        );
        return TRUE;
    }

	private function parseSearchResult ( $content, $artist, $title )
	{
        $begin = '<ul class="search_results song_list primary_list">';
        $end   = '</ul>';

		//Check if begin tag is in content
        if ( strpos ( $content, $begin ) === FALSE ) return FALSE;
		
		$dom = new DOMDocument;
		$dom->loadHTML ( $this->getSubString ( $content, $begin, $end ) );
		
		//Search song in the song list
        foreach ( $dom->getElementsByTagName ( 'li' ) as $li )
		{
			$pattern = "'<span class=\"primary_artist_name\">(.*?)</span>'si";
            $genius_artist = utf8_decode ( html_entity_decode ( str_replace ( "&nbsp;", " ", htmlentities ( html_entity_decode ( $this->getFirstMatch ( $this->DOMinnerHTML ( $li ), $pattern ) ), null, 'UTF-8' ) ) ) );

			$pattern = "'<span class=\"song_title\">(.*?)</span>'si";
			$genius_title = utf8_decode ( html_entity_decode ( str_replace ( "&nbsp;", " ", htmlentities ( html_entity_decode ( $this->getFirstMatch ( $this->DOMinnerHTML ( $li ), $pattern ) ), null, 'UTF-8' ) ) ) );
			
			// Song Featured Artist must in Title and be separated by "Feat. Artists" or "(feat. Artists)" in ID3Tag
			similar_text ( $artist . ' ' . stristr ( $title, "feat.", true ), $genius_artist . ' ' . $genius_title, $percent );
			
			if ( $percent > 75 )
			{
				$pattern = '/<a href="([^"]+)" class=" song_link"/';
				$lyrics_url = $this->getFirstMatch ( $this->DOMinnerHTML ( $li ), $pattern );

				return array(	
					'artist' => $artist,
					'title' => $title,
					'id' => $lyrics_url
				);
			}
		}
    }
	
	public function getLyrics ( $id, $info )
	{
		//$id = Link of song lyrics
        $content = $this->getContent ( $id );
        if ( !$content ) return FALSE;

        $begin = '<lyrics class="lyrics" remove-class-on-angular-load="lyrics" yields-anchorer="lyrics_anchorer = anchorer" canonical-lyrics-html="lyrics_data.body.html">';
        $end   = '</lyrics>';
		
		//Get lyrics
        $lyrics = $this->getSubString ( $content, $begin, $end );
		
		//Clean lyrics
		$lyrics = preg_replace ( '/<script\b[^>]*>(.*?)<\/script>/is', "", $lyrics );
		$lyrics = preg_replace ( '/<[^>]*>/', '', $lyrics );
        $lyrics = html_entity_decode ( $lyrics, ENT_QUOTES, 'UTF-8' );
		$lyrics = wordwrap ( $lyrics, 49, "\n", false ); //Fix for smartphone display!
		
        $info->addLyrics ( $lyrics, $id );

        return TRUE;
    }

	// Helper Functions
	//------------------------
	private function DOMinnerHTML ( DOMNode $element ) 
	{ 
		$innerHTML = ""; 
		$children  = $element->childNodes;

		foreach ( $children as $child ) $innerHTML .= $element->ownerDocument->saveHTML ( $child );

		return $innerHTML; 
	} 
	
	private function getContent ( $url )
    {
        $curl = curl_init ();

        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $curl, CURLOPT_ENCODING, 'gzip,deflate' );
        curl_setopt ( $curl, CURLOPT_CONNECTTIMEOUT, 10 );
        curl_setopt ( $curl, CURLOPT_TIMEOUT, 10 );
        curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, TRUE );
        curl_setopt ( $curl, CURLOPT_VERBOSE, TRUE );
        curl_setopt ( $curl, CURLOPT_URL, $url );

        $result = curl_exec ( $curl );
        curl_close ( $curl );

        return $result;
    }
	
	private function getSubString ( $string, $prefix, $suffix )
    {
        $start = strpos ( $string, $prefix );
        if ( $start === false ) return $string;

        $end = strpos ( $string, $suffix, $start );
        if ( $end === false ) return $string;

        if ( $start >= $end ) return $string;

        return substr ( $string, $start, $end - $start + strlen ( $suffix ) );
    }
	
	private function getFirstMatch ( $string, $pattern )
    {
        if ( 1 === preg_match ( $pattern, $string, $matches ) ) return $matches[1];

        return false;
    }
}
?>