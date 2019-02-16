<?php

namespace Tiefanovic\FawryGateway\Block\Order;

use Magento\Sales\Model\Order\Address;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Tiefanovic\FawryGateway\Helper\Data as FawryHelper;
/**
 * Invoice view  comments form
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'order/fawry_info.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

   
    protected $timeZone;
    
    protected $fawryHelper;
    /**
     * @param TemplateContext $context
     * @param Registry $registry
     * @param PaymentHelper $paymentHelper
     * @param AddressRenderer $addressRenderer
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        TimezoneInterface $timezone,
        PaymentHelper $paymentHelper,
        FawryHelper $fawryHelper,
        array $data = []
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->coreRegistry = $registry;
        $this->_isScopePrivate = true;
        $this->timeZone = $timezone;
        $this->fawryHelper = $fawryHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
    public function isShown(){
        $order = $this->getOrder();
        return ($order->getPayment()->getMethod() == 'fawrygateway' && $order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) && !empty($order->getFawryRefnum());
    }
    public function getRefNumber(){
        return $this->getOrder()->getFawryRefnum();
    }
    public function getExpiryTime(){
        $date = new \DateTime($this->getOrder()->getCreatedAt());
        $date->add(new \DateInterval('PT'.$this->fawryHelper->getExpiryTime().'H0S'));
       return  $this->timeZone->formatDateTime($date);
    }
    public function payableAmount(){
        return $this->getOrder()->getTotalDue();
    }
}
