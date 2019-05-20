<?php

namespace Tiefanovic\FawryGateway\Controller\Payment;


use Tiefanovic\FawryGateway\Controller\Checkout;

class Response extends Checkout
{

    public function execute()
    {
        die(var_dump($this->getRequest()->getParams()));
    }
}