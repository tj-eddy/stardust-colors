<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_7_1($object)
{
    $result = true;

    $result &= Configuration::updateValue('STSN_PRO_THUMNBS_PER_ODD_FW', 0);
    $result &= Configuration::updateValue('STSN_PRO_THUMNBS_PER_ODD_XXL', 0);
    $result &= Configuration::updateValue('STSN_PRO_THUMNBS_PER_ODD_XL', 0);
    $result &= Configuration::updateValue('STSN_PRO_THUMNBS_PER_ODD_LG', 0);
    $result &= Configuration::updateValue('STSN_PRO_THUMNBS_PER_ODD_MD', 0);
    $result &= Configuration::updateValue('STSN_PRO_THUMNBS_PER_ODD_SM', 0);
    $result &= Configuration::updateValue('STSN_PRO_THUMNBS_PER_ODD_XS', 0);
    $result &= Configuration::updateValue('STSN_TRANSPARENT_HEADER_TEXT', '');
    $result &= Configuration::updateValue('STSN_PRODUCT_GALLERY_FULLSCREEN_MOBILE', 0);
    $result &= Configuration::updateValue('STSN_GALLERY_THUMBNAILS_WIDTH_VPX', 0);
    $result &= Configuration::updateValue('STSN_PRODUCT_SUMMARY_LOCATION', Configuration::get('STSN_AN_PRODUCTFIELDS'));
    
    return $result;
}
