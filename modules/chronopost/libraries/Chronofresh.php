<?php

class Chronofresh
{
    /**
     * @param $carrier
     *
     * @return bool
     */
    public static function isFreshCarrier($carrier)
    {
        return ($carrier->id_reference === Configuration::get('CHRONOPOST_CHRONOFRESH_ID') ||
            $carrier->id_reference === Configuration::get('CHRONOPOST_CHRONOFRESH_CLASSIC_ID') ||
            $carrier->id_reference === Configuration::get('CHRONOPOST_CHRONORELAIS_AMBIENT_ID') ||
            $carrier->id_reference === Configuration::get('CHRONOPOST_CHRONOEXPRESS_ID') ||
            $carrier->id_reference === Configuration::get('CHRONOPOST_CHRONOCLASSIC_ID'));
    }

    /**
     * @param $carrier
     *
     * @return bool
     */
    public static function isSharedCarrier($carrier)
    {
        return ($carrier->id_reference === Configuration::get('CHRONOPOST_CHRONOEXPRESS_ID') ||
            $carrier->id_reference === Configuration::get('CHRONOPOST_CHRONOCLASSIC_ID'));
    }

    /**
     * @param $carrier
     *
     * @return bool
     */
    public static function isChronoFreshCarrier($carrier)
    {
        return $carrier->id_reference === Configuration::get('CHRONOPOST_CHRONOFRESH_ID');
    }

    /**
     * @param $carrier
     *
     * @return bool
     */
    public static function isChronoFreshClassicCarrier($carrier)
    {
        return $carrier->id_reference === Configuration::get('CHRONOPOST_CHRONOFRESH_CLASSIC_ID');
    }

    /**
     * @return bool
     */
    public static function isFreshAccount()
    {
        return Configuration::get('CHRONOPOST_GENERAL_ACCOUNTTYPE') === '2';
    }

    /**
     * Get chrono fresh products code
     *
     * @return string[]
     */
    public static function getProducts()
    {
        return ['1T', '5T', '2R', '2S'];
    }
}
