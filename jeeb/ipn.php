<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/jeeb.php');

$jeeb = new jeeb();
$postdata = file_get_contents("php://input");
$json = json_decode($postdata, true);
// fclose($handle);
$signature = Configuration::get('jeeb_APIKEY');
if($json['signature']==$signature){
  error_log("Entered Jeeb-Notification");
  error_log("Response =>". var_export($json, TRUE));
  if($json['orderNo']){
    error_log("hey".$json['orderNo']);

    $orderNo = $json['orderNo'];

    $db = Db::getInstance();
    $result = array();
    $result = $db->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'order_bitcoin_jeeb` WHERE `id_order` = "' . $json['orderNo'] . '";');
    error_log("Db Result = ".print_r($result[0],true));
    error_log("Key =".$result[0]['key']);

    $cart_id = (int)$result[0]['cart_id'];
    $order_id = Order::getOrderByCartId($cart_id);
    $order = new Order($order_id);

    if ( $json['stateId']== 2 ) {
      $order_status = Configuration::get('JEEB_PENDING');
      error_log("Data : ".$order_status." cart ".$cart_id." amount ".$json['requestAmount']." key ".(string)$result[0]['key'] );

      $jeeb->validateOrder($cart_id, $order_status, $json['requestAmount'], "Jeeb", null, array(), null, false, (string)$result[0]['key']);

      $new_history = new OrderHistory();
      $new_history->id_order = (int)$json['orderNo'];
      $new_history->changeIdOrderState((int)$order_status, $order, true);

      $db = Db::getInstance();
      $result = array();
      $result = $db->ExecuteS('UPDATE `' . _DB_PREFIX_ . 'order_history` SET `id_order_state`= '. $order_status .'  WHERE `id_order` = "' . $json['orderNo'] . '";');


    }
    else if ( $json['stateId']== 3 ) {
      $order_status = Configuration::get('JEEB_CONFIRMING');
      $new_history = new OrderHistory();
      $new_history->id_order = (int)$json['orderNo'];
      $new_history->changeIdOrderState((int)$order_status, $order, true);

      $db = Db::getInstance();
      $result = array();
      $result = $db->ExecuteS('UPDATE `' . _DB_PREFIX_ . 'order_history` SET `id_order_state`= '. $order_status .'  WHERE `id_order` = "' . $json['orderNo'] . '";');


    }
    else if ( $json['stateId']== 4 ) {
      $data = array(
        "token" => $json["token"]
      );

      $is_confirmed = $jeeb->confirm_payment($signature, $data);

      if($is_confirmed){
        error_log('Payment confirmed by jeeb');
        $order_status = Configuration::get('PS_OS_PAYMENT');


        $db = Db::getInstance();
        $result = array();
        $result = $db->ExecuteS('SELECT * FROM `' . _DB_PREFIX_ . 'order_bitcoin_jeeb` WHERE `id_order` = "' . $json['orderNo'] . '";');
        error_log("Db Result = ".print_r($result[0],true));
        error_log("Key =".$result[0]['key']);

        $cart_id = (int)$result[0]['cart_id'];


        $new_history = new OrderHistory();
        $new_history->id_order = (int)$json['orderNo'];
        $new_history->changeIdOrderState((int)$order_status, $order, true);

        $db = Db::getInstance();
        $result = array();
        $result = $db->ExecuteS('UPDATE `' . _DB_PREFIX_ . 'order_history` SET `id_order_state`= '. $order_status .'  WHERE `id_order` = "' . $json['orderNo'] . '";');


      }
      else {
        error_log('Payment confirmation rejected by jeeb');
      }
    }
    else if ( $json['stateId']== 5 ) {
      $order_status = Configuration::get('JEEB_EXPIRED');

      $new_history = new OrderHistory();
      $new_history->id_order = (int)$json['orderNo'];
      $new_history->changeIdOrderState((int)$order_status, $order, true);

      $db = Db::getInstance();
      $result = array();
      $result = $db->ExecuteS('UPDATE `' . _DB_PREFIX_ . 'order_history` SET `id_order_state`= '. $order_status .'  WHERE `id_order` = "' . $json['orderNo'] . '";');


    }
    else if ( $json['stateId']== 6 ) {
      $order_status = Configuration::get('PS_OS_REFUND');

      $new_history = new OrderHistory();
      $new_history->id_order = (int)$json['orderNo'];
      $new_history->changeIdOrderState((int)$order_status, $order, true);

      $db = Db::getInstance();
      $result = array();
      $result = $db->ExecuteS('UPDATE `' . _DB_PREFIX_ . 'order_history` SET `id_order_state`= '. $order_status .'  WHERE `id_order` = "' . $json['orderNo'] . '";');


    }
    else if ( $json['stateId']== 7 ) {
      $order_status = Configuration::get('PS_OS_REFUND');

      $new_history = new OrderHistory();
      $new_history->id_order = (int)$json['orderNo'];
      $new_history->changeIdOrderState((int)$order_status, $order, true);

      $db = Db::getInstance();
      $result = array();
      $result = $db->ExecuteS('UPDATE `' . _DB_PREFIX_ . 'order_history` SET `id_order_state`= '. $order_status .'  WHERE `id_order` = "' . $json['orderNo'] . '";');


    }
    else{
      error_log('Cannot read state id sent by Jeeb');
    }
}
}
