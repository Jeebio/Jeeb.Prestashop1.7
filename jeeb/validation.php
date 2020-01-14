<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/jeeb.php');

$context  = Context::getContext();
$cart     = $context->cart;

$jeeb   = new jeeb();

if ($cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$jeeb->active)
        Tools::redirect('index.php?controller=order&step=1');

// Check that this payment option is still available in case the customer
// changed his address just before the end of the checkout process
$authorized = false;

foreach (Module::getPaymentModules() as $module)
        if ($module['name'] == 'jeeb') {
                $authorized = true;
                break;
        }

if (!$authorized)
        die($jeeb->l('This payment method is not available.', 'validation'));

echo $jeeb->processPayment($cart);
