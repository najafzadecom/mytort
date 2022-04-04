<?php
$data = file_get_contents('php://input');
$result = json_decode($data,true);
if(!empty($result['code'])){
	$location = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
	include ($location[0] . 'wp-load.php');

	if (!empty($result['code'])) {
		$order_info_id = $result['payload']['orderID'];
		$order_session_id = $result['payload']['sessionId'];

		$payriff = new WC_Gateway_Payriff();

		$payriff->get_order_information($result['payload']);
	}
}elseif(isset($_GET['order_id']) && $_SERVER['REQUEST_METHOD'] === 'GET'){
	$location = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
	include ($location[0] . 'wp-load.php');

	$order_id = (int)$_GET['order_id'];

	//Create object from Order class
    $order = new WC_Order( $order_id );

    $payment = new WC_Gateway_Payriff();

    // //Get url
    // $return_url = $order->get_checkout_order_received_url();


    $return_url = $payment->get_return_url($order);

    // print_r($return_url);

    //Redirect user
	// $url = '/checkout/order-received/'.$order->id.'/?key='.$order->order_key;
	header("Location: ".$return_url);
	exit();
}
						