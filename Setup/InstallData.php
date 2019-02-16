<?php
namespace Tiefanovic\FawryGateway\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\TestFramework\Event\Magento;
use Tiefanovic\FawryGateway\Model\Standard as FawryPayment;

class InstallData implements InstallDataInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install( ModuleDataSetupInterface $setup, ModuleContextInterface $context )
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Install order statuses from config
         */
        $data = [];
        $statuses = [
            FawryPayment::STATUS_NEW => __('Fawry New Order'),
            FawryPayment::STATUS_FAILED => __('Fawry Payment Failed'),
            FawryPayment::STATUS_PENDING => __('Fawry Pending Payment'),
        ];

        foreach ($statuses as $code => $info) {
            $data[] = ['status' => $code, 'label' => $info];
        }

        $installer->getConnection()->insertArray(
            $installer->getTable('sales_order_status'),
            ['status', 'label'],
            $data
        );


        /**
         * Install order states from config
         */
        $data = [];
        $states = [
            'new' => [
                'statuses' => [ FawryPayment::STATUS_NEW ],
            ],
            'canceled' => [
                'statuses' => [ FawryPayment::STATUS_FAILED ],
            ],
            'pending' => [
                'statuses' => [ FawryPayment::STATUS_PENDING ],
            ],
        ];

        foreach ($states as $code => $info) {
            if (isset($info['statuses'])) {
                foreach ($info['statuses'] as $status) {
                    $data[] = [
                        'status' => $status,
                        'state' => $code,
                        'is_default' => 0,
                        'visible_on_front' => 1,
                    ];
                }
            }
        }
        $installer->getConnection()->insertArray(
            $installer->getTable('sales_order_status_state'),
            ['status', 'state', 'is_default', 'visible_on_front'],
            $data
        );

        $installer->endSetup();
    }

}