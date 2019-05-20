<?php

namespace Tiefanovic\FawryGateway\Controller\Payment;


use Magento\Framework\App\ResponseInterface;
use Tiefanovic\FawryGateway\Controller\Checkout;

class Redirect extends Checkout
{

    public function execute()
    {
        return $this->_pageFactory->create();
    }
}