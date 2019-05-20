<?php
namespace Tiefanovic\FawryGateway\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Tiefanovic\FawryGateway\Helper\Data as PaymentHelper;
class PaymentConfigProvider implements ConfigProviderInterface
{
    protected $paymentHelper;
    protected $urlBuilder;

    public function __construct(
        PaymentHelper $paymentHelper,
        UrlInterface $urlBuilder
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;
    }
    public function getConfig()
    {
        $config = [
            'payment' => [
                'payfortFort' => [],
            ],
        ];
        if ($this->paymentHelper->isActive()) {
            $config['payment']['fawryGateway']['redirectUrl'] = $this->urlBuilder->getUrl('fawrygateway/payment/redirect', ['_secure' => true]);
            $config['payment']['fawryGateway']['instructions'] = $this->paymentHelper->getInstructions();
            $config['payment']['fawryGateway']['isActive'] = $this->paymentHelper->isActive();
        }

        return $config;
    }
}