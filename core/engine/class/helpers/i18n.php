<?php
class i18nHelper extends Helper
{

	var $localeFile;
    function __construct($params = ""){
        parent::__construct($params);

    }

	function locale($locale){
		
		if( file_exists(LOCALE_DIR.$locale.".php" ) )
			include( LOCALE_DIR.$locale.".php" );
		
		$this->localeFile = $i18n;
	}

	function t($tag = "", $echo = true){
		if( empty($tag) )
			return '';
		
		if( !array_key_exists($tag, $this->localeFile) )
			return false;
		
		if( $echo ){
			echo $this->localeFile[$tag];
			return '';
		}
			
		return $this->localeFile[$tag];
	}

}
?>