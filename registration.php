<?php
/**
 * Project    : Fawry Payment Plugin
 * Created by : Tiefa - 2019
 * Date       : 01/26/2019 - 10:56 AM
 * File       : registration.php
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    "Tiefanovic_FawryGateway",
    __DIR__
);