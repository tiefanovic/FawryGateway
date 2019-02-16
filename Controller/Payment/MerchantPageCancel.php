<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 30/01/2019
 * Time: 03:24 م
 */

namespace Tiefanovic\FawryGateway\Controller\Payment;


use Magento\Framework\App\ResponseInterface;
use Tiefanovic\FawryGateway\Controller\Checkout;

class MerchantPageCancel extends Checkout
{

    public function execute()
    {
        $order = $this->getOrder();
        $this->getHelper()->orderFailed($order);
        $this->_checkoutSession->restoreQuote();

        $message = __('Error: User Closed Payment Page.');
        $this->messageManager->addError( $message );
        $returnUrl = $this->_url->getUrl('checkout/cart');
        
        $this->orderRedirect($returnUrl);
    }

}