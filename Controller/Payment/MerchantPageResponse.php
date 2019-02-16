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

class MerchantPageResponse extends Checkout
{

    public function execute()
    {
        /*
         * ?chargeResponse={"merchantRefNumber":"000002344","fawryRefNumber":"912969894","paymentMethod":"PAYATFAWRY"}
         * { ["chargeResponse"]=> string(85) "{"merchantRefNumber":"000002411","fawryRefNumber":"913096889","paymentMethod":"CARD"}" } 
         */
        $chargeResponse = json_decode($this->getRequest()->getParam('chargeResponse'));
        if($chargeResponse){
            $orderId = $chargeResponse->merchantRefNumber;
            $order = $this->getOrderById($orderId);
            $returnUrl = $this->_url->getUrl('checkout/onepage/success');
            $responseParams = $this->getRequest()->getParams();
           // $paymentResponse = $this->getHelper()->validateResponse($responseParams);
    
            $paymentModel = $this->getPaymentModel();
            $success = false;
            if($chargeResponse->paymentMethod == 'PAYATFAWRY') {
                // TO DO
                $order->setFawryRefnum($chargeResponse->fawryRefNumber)->save();
                // Process Order
                $success = $this->getHelper()->processOrder($order, $chargeResponse->fawryRefNumber);
            }
            else if($chargeResponse->paymentMethod == 'CARD'){
                 $order->setFawryRefnum($chargeResponse->fawryRefNumber)->save();
                // Process Order
                $success = $this->getHelper()->processSuccessOrder($order, $chargeResponse->fawryRefNumber);
            }
            if($success) {
                $returnUrl = $this->_url->getUrl('checkout/onepage/success');
            }else {
                $this->getHelper()->orderFailed($order);
                $this->_checkoutSession->restoreQuote();
                $message = __('Error: Payment Failed, Please check your payment details and try again.');
                $this->messageManager->addError( $message );
                $returnUrl = $this->_url->getUrl('checkout/cart');
            }
        }else{
            $this->_checkoutSession->restoreQuote();
            $message = __('Error: Payment Failed, Please check your payment details and try again.');
            $this->messageManager->addError( $message );
            $returnUrl = $this->_url->getUrl('checkout/cart');
        }

        $this->orderRedirect($returnUrl);
    }
}