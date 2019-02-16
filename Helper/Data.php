<?php
namespace Tiefanovic\FawryGateway\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Tiefanovic\FawryGateway\Model\Config\Source\Modes;

class Data extends  AbstractHelper {


    const STAGING_STATUS_URL    = 'https://atfawry.fawrystaging.com/ECommerceWeb/Fawry/payments/status';
    const LIVE_STATUS_URL    = 'https://www.atfawry.com/ECommerceWeb/Fawry/payments/status';
    private $_gatewayHost        = 'https://www.atfawry.com/ECommercePlugin/FawryPay.jsp';
    private $_gatewayStagingHost = 'https://atfawry.fawrystaging.com/ECommercePlugin/FawryPay.jsp';

    protected $customerSession;
    protected $checkoutSession;
    protected $orderCommentSender;
    protected $orderManagement;
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    )
    {
        parent::__construct($context);
        $this->customerSession = $session;
        $this->checkoutSession = $checkoutSession;
        $this->orderCommentSender = $orderCommentSender;
        $this->orderManagement = $orderManagement;
    }

    public function getModuleMode(){
        return $this->scopeConfig->getValue("payment/fawrygateway/paymentmode", ScopeInterface::SCOPE_STORE);
    }
    public function getMerchantCode(){
        return $this->scopeConfig->getValue("payment/fawrygateway/merchant_code", ScopeInterface::SCOPE_STORE);
    }
    public function getSecretKey(){
        return $this->scopeConfig->getValue("payment/fawrygateway/security_key", ScopeInterface::SCOPE_STORE);
    }
    public function isActive(){
        return $this->scopeConfig->getValue("payment/fawrygateway/active", ScopeInterface::SCOPE_STORE);
    }
    public function getInstructions(){
        return $this->scopeConfig->getValue("payment/fawrygateway/instructions", ScopeInterface::SCOPE_STORE);
    }
    public function getMinAmount(){
        return $this->scopeConfig->getValue("payment/fawrygateway/min_amount", ScopeInterface::SCOPE_STORE);
    }
    public function getMaxAmount(){
        return $this->scopeConfig->getValue("payment/fawrygateway/max_amount", ScopeInterface::SCOPE_STORE);
    }
    public function getExpiryTime(){
        return $this->scopeConfig->getValue("payment/fawrygateway/expiry_time", ScopeInterface::SCOPE_STORE);
    }
    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getMerchantPageData(\Magento\Sales\Model\Order $order) {
        /*
         * https://atfawry.fawrystaging.com/ECommercePlugin/FawryPay.jsp+'?chargeRequest={ "language":"ar-eg",
         "merchantCode":"is0N+YQzlE4=", "merchantRefNumber":"12333",
         "customer":{ "name":"test user", "mobile":"0100739xxx", "email":"test@test.com", "customerProfileId":"8723871236" },
         "order":{ "description":"test bill inq",
         "expiry":"2", 
         "orderItems":[ { "productSKU":"12222", "description":"Test Product", "price":"50", "quantity":"2", "width":"10", "height":"5", "length":"100", "weight":"1" } ] },
         "signature":"243d69d005ba46943c5f8d590cf7f8ad6c02663a838ca5b7039c33e03ad10799"}&successPageUrl='successPageUrl'&failerPageUrl='failerPageUrl' 
         */
        $orderId = $order->getRealOrderId();
        $gatewayParams = array(
            'merchantCode'      => $this->getMerchantCode(),
            'lang'              => 'ar-eg',
            'merchantRefNumber'    => $orderId,
            'customer'          => [
                                    'name'  =>  trim($order->getCustomerName()),
                                    'mobile'  =>  trim($order->getShippingAddress()->getTelephone()),
                                    'email'  =>  trim($order->getCustomerEmail()),
                                    'customerProfileId'  =>  trim($order->getCustomerId()),
                                    ],
            'order'             => [
                                    'expiry'        =>  $this->getExpiryTime(),
                                    'orderItems'    =>  $this->generateOrder($order),
                                    ]
        );
        $signature = $this->generateSig($gatewayParams);
        $gatewayParams['signature'] = $signature;
        $gatewayUrl = $this->getGatewayUrl();
        $returnUrl = $this->_urlBuilder->getUrl('fawrygateway/payment/merchantPageResponse', ['_secure'=>true]);
        return array('url' => $gatewayUrl, 'params' => $gatewayParams, 'returnUrl'  =>  $returnUrl);
    }
    public function getGatewayUrl(){
        if($this->getModuleMode() == Modes::PRODUCTION)
            return $this->_gatewayHost;
        return $this->_gatewayStagingHost;
    }
    public function getStatusUrl(){
         if($this->getModuleMode() == Modes::PRODUCTION)
            return self::LIVE_STATUS_URL;
        return self::STAGING_STATUS_URL;
    }
    public function generateSig($gatewayParams)
    {
        $string = $gatewayParams['merchantCode'].$gatewayParams['merchantRefNumber'].$gatewayParams['customer']['customerProfileId'];
        foreach($gatewayParams['order']['orderItems'] as $orderItem){
            $string .= $orderItem['productSKU'].$orderItem['quantity'].$orderItem['price'];
        }
        $string .= $gatewayParams['order']['expiry'].$this->getSecretKey();
        return hash('SHA256',$string);
    }
    public function restoreQuote()
    {
        return $this->checkoutSession->restoreQuote();
    }

    public function cancelCurrentOrder($comment)
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if(!empty($comment)) {
            $comment = 'FawryGatway :: ' . $comment;
        }
        if ($order->getId() && $order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED) {
            $order->registerCancellation($comment)->save();
            return true;
        }
        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $comment
     * @return bool
     */
    public function cancelOrder($order, $comment)
    {
        $gotoSection = false;
        if(!empty($comment)) {
            $comment = 'FawryGatway :: ' . $comment;
        }
        if ($order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED) {
            $order->registerCancellation($comment)->save();
            $gotoSection = true;
        }
        return $gotoSection;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function orderFailed($order) {
        if ($order->getState() != \Tiefanovic\FawryGateway\Model\Standard::STATUS_FAILED) {
            $order->setStatus(\Tiefanovic\FawryGateway\Model\Standard::STATUS_FAILED);
            $order->setState(\Tiefanovic\FawryGateway\Model\Standard::STATUS_FAILED);
            $order->save();
            $customerNotified = $this->sendOrderEmail($order);
            $order->addStatusToHistory( \Tiefanovic\FawryGateway\Model\Standard::STATUS_FAILED , 'FawryGatway :: payment has failed.', $customerNotified );
            $order->save();
            return true;
        }
        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function sendOrderEmail($order) {
        $result = true;
        try{
            if($order->getState() != $order::STATE_PROCESSING) {
                $this->orderCommentSender->send($order, true, '');
            }
            else{
                $this->orderManagement->notify($order->getEntityId());
            }
        } catch (\Exception $e) {
            $result = false;
            $this->_logger->critical($e);
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function processOrder($order, $refNum) {

        if ($order->getState() != $order::STATE_PENDING_PAYMENT) {
            $order->setStatus($order::STATE_PENDING_PAYMENT);
            $order->setState($order::STATE_PENDING_PAYMENT);
            $order->save();
            $customerNotified = $this->sendOrderEmail($order);
            $order->addStatusToHistory( $order::STATE_PENDING_PAYMENT , 'FawryGatway :: Order waiting payment with @RefNum. ' . $refNum, $customerNotified );
            $order->save();
            return true;
        }
        return false;
    }
    public function processSuccessOrder($order, $refNum) {

        if ($order->getState() != $order::STATE_PROCESSING) {
            $order->setStatus($order::STATE_PROCESSING);
            $order->setState($order::STATE_PROCESSING);
            $order->save();
            $customerNotified = $this->sendOrderEmail($order);
            $order->addStatusToHistory( $order::STATE_PROCESSING , 'FawryGatway :: Order with @RefNum. ' . $refNum . ' has successfully paid', $customerNotified );
            $order->save();
            return true;
        }
        return false;
    }
    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array All Orders
     */
    public function generateOrder(\Magento\Sales\Model\Order $order){
        $allOrders = [];
        $items = $order->getAllItems();
        foreach ($items as $item) {
            if($item->getPrice() <= 0) continue;
            /** @var \Magento\Sales\Model\Order\Item * $item */
            $subOrder = [
                'productSKU'    =>  $item->getSku(),
                'description'   =>  $item->getDescription(),
                'price'         =>  number_format((float) $item->getPrice(), 2, '.', ''),
                'quantity'      =>  (int) $item->getQtyOrdered(),
                'weight'        =>  number_format((float) $item->getProduct()->getWeight(), '2', '.', '')
            ];
            array_push($allOrders, $subOrder);
        }
        // Add shipping and taxes
        $allOrders[] = ['productSKU'    =>  'shippingamnt',
                        'description'   =>  'Shipping Cost',
                        'price'         =>  number_format((float) $order->getShippingAmount(), 2, '.', ''),
                        'quantity'      =>  1,
                        'weight'        =>  0
                        ];
        $allOrders[] = ['productSKU'    =>  'taxamnt',
                        'description'   =>  'Tax Cost',
                        'price'         =>  number_format((float) $order->getShippingTaxAmount(), 2, '.', ''),
                        'quantity'      =>  1,
                        'weight'        =>  0
                        ];
        return $allOrders;
    }
}