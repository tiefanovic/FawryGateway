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
class Success extends Info
{
   
    protected $_template = 'order/fawry_info.phtml';
    protected $checkoutSession;
    
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        TimezoneInterface $timezone,
        PaymentHelper $paymentHelper,
        FawryHelper $fawryHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context,$registry,$timezone,$paymentHelper,$fawryHelper, $data);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }
    
}
