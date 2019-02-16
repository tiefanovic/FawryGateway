<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 26/01/2019
 * Time: 11:18 ص
 */

namespace Tiefanovic\FawryGateway\Model\Config\Source;


use Magento\Framework\Option\ArrayInterface;

class Modes implements ArrayInterface
{
    const STAGING = 'staging';
    const PRODUCTION = 'production';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'label' =>  'Staging',
                'value' =>  $this::STAGING
            ],[
                'label' =>  'Production',
                'value' =>  $this::PRODUCTION
            ]
        ];
    }
}