<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 30/01/2019
 * Time: 03:14 م
 */

namespace Tiefanovic\FawryGateway\Controller;


abstract class Checkout extends \Magento\Framework\App\Action\Action
{

    protected $_customerSession;
    protected $_checkoutSession;
    protected $_orderFactory;
    protected $_logger;
    protected $_helper;
    protected $_paymentModel;
    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Tiefanovic\FawryGateway\Model\Standard $paymentModel,
        \Tiefanovic\FawryGateway\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_pageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_logger = $logger;
        $this->paymentModel = $paymentModel;
        $this->_helper = $helper;
        parent::__construct($context);
    }
    /**
     * Cancel order, return quote to customer
     *
     * @param string $errorMsg
     * @return false|string
     */
    protected function _cancelCurrenctOrderPayment($errorMsg = '')
    {
        $gotoSection = false;
        $this->_helper->cancelCurrentOrder($errorMsg);
        if ($this->_checkoutSession->restoreQuote()) {
            //Redirect to payment step
            $gotoSection = 'paymentMethod';
        }
        return $gotoSection;
    }

    /**
     * Cancel order, return quote to customer
     *
     * @param string $errorMsg
     * @return false|string
     */
    protected function _cancelPayment($order, $errorMsg = '')
    {
        return $this->_helper->cancelOrder($order, $errorMsg);
    }

    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrderById($order_id)
    {
        $order_info = $this->_orderFactory->create()->loadByIncrementId($order_id);
        return $order_info;
    }
    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        return $this->_orderFactory->create()->loadByIncrementId(
            $this->_checkoutSession->getLastRealOrderId()
        );
    }

    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
    protected function getCustomerSession()
    {
        return $this->_customerSession;
    }
    protected function getPaymentModel()
    {
        return $this->_paymentModel;
    }
    protected function getHelper()
    {
        return $this->_helper;
    }

    public function orderRedirect($returnUrl) {
        echo "<html><body onLoad=\"javascript: window.top.location.href='" . $this->_url->getUrl($returnUrl) . "'\"></body></html>";
        exit;
    }
}