<?php


class jeebPaymentModuleFrontController extends ModuleFrontController
{
  public $ssl = true;
  public $display_column_left = false;

  /**
   * @see FrontController::initContent()
   */
  public function initContent()
  {
    parent::initContent();

    $cart = $this->context->cart;

    echo $this->module->processPayment($cart);
  }
}
