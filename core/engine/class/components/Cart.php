<?php
class CartComponent extends Component
{
	var $cartId = 'cart';
	
    function __construct($params = ""){
        parent::__construct($params);
		
		if( empty($_SESSION[$this->cartId]) )
			$_SESSION[$this->cartId] = array();

    }

	function add($item){
		
     	$id 			= ( empty($item["id"]) ) 			? false 		: $item["id"];
     	$title 			= ( empty($item["title"]) ) 		? false 		: $item["title"];
     	$description 	= ( empty($item["desc"]) ) 			? false 		: $item["desc"];
     	$description 	= ( empty($item["description"]) ) 	? $description	: $item["description"];
     	$price 			= ( empty($item["price"]) ) 		? 0				: $item["price"];
     	$qtd 			= ( empty($item["qtd"]) ) 			? 1 			: $item["qtd"];
     	$qtd 			= ( empty($item["quantity"]) ) 		? $qtd 			: $item["quantity"];

     	$this->cartId 	= ( empty($item["cartId"]) ) 		? $this->cartId : $item["cartId"];

		/* deletes standard variables */
		unset($item['id']);
		unset($item['title']);
		unset($item['desc']);
		unset($item['description']);
		unset($item['price']);
		unset($item['qtd']);
		unset($item['quantity']);
		unset($item['cartId']);
		
		if( !$id ){
			return false;
		}

		
		if( !empty($_SESSION[$this->cartId][$id]) ){
			$_SESSION[$this->cartId][$id]['quantity'] = $_SESSION[$this->cartId][$id]['quantity'] + $qtd;
		} else {
			$_SESSION[$this->cartId][$id]['id'] = $id;
			$_SESSION[$this->cartId][$id]['title'] = $title;
			$_SESSION[$this->cartId][$id]['description'] = $description;
			$_SESSION[$this->cartId][$id]['price'] = $price;
			$_SESSION[$this->cartId][$id]['quantity'] = $qtd;
		}

		foreach( $item as $key=>$value ){
			$_SESSION[$this->cartId][$id][$key] = $value;
		}


		$totalQtd = $_SESSION[$this->cartId][$id]['quantity'];
		
		$_SESSION[$this->cartId][$id]['total_price'] = $this->totalPrice($price, $totalQtd);
		
		return true;
		
	}
	
	function remove($id, $cartId = 'cart'){
		if( isset($_SESSION[$cartId][$id]) )
			unset($_SESSION[$cartId][$id]);
		
		return true;
	}
	
	function setQuantity($id, $qtd = '', $cartId = 'cart'){

		if( empty($qtd) || !is_numeric($qtd) || !is_numeric($id) )
			return false;
		
		$_SESSION[$cartId][$id]['quantity'] = $qtd;
		if( $_SESSION[$cartId][$id]['quantity'] <= 0 )
			$_SESSION[$cartId][$id]['quantity'] = 0;

		$this->updateTotalPrice($id);
		
		return true;
	}
	
	function updateTotalPrice($id, $cartId = 'cart'){
		if( empty($_SESSION[$cartId][$id]['price']) )
			return false;
		
		$price = $_SESSION[$cartId][$id]['price'];
		$qtd = $_SESSION[$cartId][$id]['quantity'];
		
		$_SESSION[$cartId][$id]['total_price'] = $this->totalPrice($price, $qtd);
		return true;
	}
	
	function totalPrice($price, $qtd){
		
		$price = str_replace(',', '.', $price);
		
		$price = 0 + $price;
		$result = $price*$qtd;
		$result = number_format($result, 2, ',', '.');
		return $result;
	}
	
	function update(){
		
		foreach( $_SESSION[$this->cartId] as $id => $item){
			$this->updateTotalPrice($id);
		}
		
	}
	
	function load($cartId = 'cart'){
		
		if( empty($_SESSION[$cartId]) )
			return array();
		
		foreach( $_SESSION[$cartId] as $id=>$product ){
			$this->updateTotalPrice($id);
		}
		
		$cart = $_SESSION[$cartId];
		return $cart;
	}
	
	function price(){
		$totalPrice = (float) 0.00;
		$this->update();
		foreach( $_SESSION[$this->cartId] as $id=>$item ){
			$priceStr = str_replace('.', '', $item['total_price']);
			$priceStr = str_replace(',', '.', $item['total_price']);
			$price = (float) $priceStr;
			$totalPrice = $totalPrice + $price;
		}
		$totalPrice = number_format($totalPrice, 2, ',', '.');

		return $totalPrice;
	}

	function reset(){
		unset($_SESSION[$this->cartId]);
	}
	
	function itemsCount(){
		$qtd = 0;
		$qtd = count($_SESSION[$this->cartId]);
		return $qtd;
	}
}
?>