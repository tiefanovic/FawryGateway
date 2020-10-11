<?php
namespace Tiefanovic\FawryGateway\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Tiefanovic\FawryGateway\Helper\Data as FawryHelper;
class CheckPayment
{
    protected $orderCollection;
    protected $fawryHelper;
    
    public function __construct(
        CollectionFactory $orderCollectionFactory,
        FawryHelper $fawryHelper
    )
    {
        $this->orderCollection = $orderCollectionFactory->create();
        $this->fawryHelper = $fawryHelper;
    }

	public function execute()
	{
	    /*
	    * To Do
	    * Retrieve all orders with pending_payment status and method is fawrygateway
	    * check if have fawry_refnum
	    * check Status
	    * Update Order 
	    */
        $orders = $this->orderCollection->addFieldToSelect('*');
        $orders->addFieldToFilter('status', ['eq' => \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT]);
        $orders->getSelect()->join(
            ["sop" => "sales_order_payment"],
            'main_table.entity_id = sop.parent_id',
            ['method']
        )->where('sop.method = ?', 'fawrygateway' );
      
        if($orders->count()){
            foreach($orders as $order){
                  if($order->getRealOrderId()){
                      $url = $this->fawryHelper->getStatusUrl() .'?merchantCode='.$this->fawryHelper->getMerchantCode().'&merchantRefNumber='.$order->getRealOrderId().'&signature='.$this->generateSig($order->getRealOrderId());
                      $response = file_get_contents($url);
                      if(($response = json_decode($response)) && isset($response->statusCode)){
                            if($response->statusCode == 200){
                                switch($response->paymentStatus){
                                    case 'PAID':
                                    $this->fawryHelper->processSuccessOrder($order, $response->referenceNumber);
                                        break;
                                    case    'EXPIRED':
                                    case    'CANCELLED':
                                    case    'REFUNDED':
                                        $this->fawryHelper->cancelOrder($order, 'Order ' . $response->merchantRefNumber . ' with ref number ' . $response->referenceNumber . ' and payment method ' . $response->paymentMethod .' was ' . $response->paymentStatus);
                                        break;
                                    case    'FAILED':
                                        $this->fawryHelper->orderFailed($order);
                                        break;

                                }
                            }
                        }
                  }
            }
        }
		return $this;
	}
	public function generateSig($orderId){
	    return hash('sha256', $this->fawryHelper->getMerchantCode().$orderId.$this->fawryHelper->getSecretKey());
	}
}

