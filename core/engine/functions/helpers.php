<?php

function price($str = '', $currency = 'br'){
	if( empty($str) )
		return false;
		
	$str = str_replace(',', '.', $str);
	$price = $str;
	if( $currency == 'br' ){
		$price = 'R$ '.number_format($str, 2, ',', '.');
	
	}
	
	return $price;
}

function notice(){
	if( !empty($_SESSION['notice']) ){
		return $_SESSION['notice']['message'];
	}
}

?>