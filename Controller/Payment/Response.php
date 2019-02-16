<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 30/01/2019
 * Time: 03:24 م
 */

namespace Tiefanovic\FawryGateway\Controller\Payment;


use Tiefanovic\FawryGateway\Controller\Checkout;

class Response extends Checkout
{

    public function execute()
    {
        die(var_dump($this->getRequest()->getParams()));
    }
}