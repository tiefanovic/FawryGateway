define(
    [ 'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        
        rendererList.push({
            type: 'fawrygateway',
            component: 'Tiefanovic_FawryGateway/js/view/payment/method-renderer/fawry-gateway'
        });


        return Component.extend({});
    }
);