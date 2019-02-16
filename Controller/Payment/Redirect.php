<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 30/01/2019
 * Time: 03:23 م
 */

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