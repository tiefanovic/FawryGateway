<?php

namespace Tiefanovic\FawryGateway\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Checkout\Model\Session as CheckoutSession;
use Tiefanovic\FawryGateway\Helper\Data as FawryHelper;

class Standard extends AbstractMethod
{
    const   STATUS_NEW                 = 'fawrygateway_standard_new';
    const   STATUS_FAILED              = 'fawrygateway_standard_failed';
    const   STATUS_PENDING             = 'fawrygateway_standard_pending';

    const   PAYMENT_STATUS_NEW         =   'NEW';
    const   PAYMENT_STATUS_PAID        =   'PAID';
    const   PAYMENT_STATUS_CANCELED    =   'CANCELED';
    const   PAYMENT_STATUS_DELIVERED   =   'DELIVERED';
    const   PAYMENT_STATUS_REFUNDED    =   'REFUNDED';
    const   PAYMENT_STATUS_EXPIRED     =   'EXPIRED';
    

    protected $_code = "fawrygateway";
    protected $_isGateway                   = true;
    protected $_canOrder                    = true;
    protected $_canCapture                  = false;
    protected $_canCapturePartial           = false;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = false;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = true;
    protected $_isInitializeNeeded          = true;

    protected $objectManager;
    protected $fawryHelper;
    protected $checkoutSession;
    protected $storeManager;
    protected $order;
    protected $scopeConfig;
    protected $orderSender;
    protected $invoiceSender;
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    private $hashInput;
    private $params;
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ObjectManagerInterface $objectManager,
        FawryHelper $fawryHelper,
        CheckoutSession $checkoutSession,
        StoreManagerInterface $storeManager,
        Order $order,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender,
        OrderRepository $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->objectManager = $objectManager;
        $this->fawryHelper = $fawryHelper;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }
    public function setMethodCode($code) {
        $this->_code = $code;
    }

    public function getMethodCode() {
        return $this->_code;
    }
    public function isAvailable(CartInterface $quote = null)
    {
        if ($quote == null) {
            $quote = $this->checkoutSession->getQuote();
        }
        if ($quote && (
                $quote->getBaseGrandTotal() < $this->fawryHelper->getMinAmount()
                || ($this->fawryHelper->getMaxAmount() && $quote->getBaseGrandTotal() > $this->fawryHelper->getMaxAmount() ))
        ) {
            return false;
        }
        /* test porpus */
        //if(!in_array($this->checkoutSession->getQuote()->getCustomer()->getId(), ['2485'])) return false;
        if(!$this->fawryHelper->getMerchantCode() || !$this->fawryHelper->getSecretKey())
            return false;
        return parent::isAvailable($quote) && $this->fawryHelper->isActive();
    }
    public function initialize($paymentAction, $stateObject)
    {

        $payment = $this->getInfoInstance();
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);
        $order->setIsNotified(false);

        $stateObject->setState(\Magento\Sales\Model\Order::STATE_NEW);
        $stateObject->setIsNotified(false);
    }
    public function getInstructions()
    {
        return $this->fawryHelper->getInstructions();
    }
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
    
        if (!$this->canOrder()) {
            throw new LocalizedException(__('The order action is not available.'));
        }
        return $this;
    }
}
