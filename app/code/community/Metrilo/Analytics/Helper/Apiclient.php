<?php
class Metrilo_Analytics_Helper_Apiclient extends Mage_Core_Helper_Abstract
{
    public function getClient($storeId)
    {
        try {
            $helper         = Mage::helper('metrilo_analytics');
            $edition        = (Mage::getVersion() < '1.7') ? '' : Mage::getEdition();
            $token          = $helper->getApiToken($storeId);
            $platform       = 'Magento ' . $edition . ' ' . Mage::getVersion();
            $pluginVersion  = (string)Mage::getConfig()->getModuleConfig("Metrilo_Analytics")->version;
            $apiEndpoint    = $helper->getApiEndpoint();
            return new Metrilo_Analytics_Api_Client($token, $platform, $pluginVersion, $apiEndpoint, Mage::getBaseDir('log'));
        } catch (Exception $e) {
            Mage::log(json_encode(array('ApiClientHelper error: ' => $e->getMessage())) . PHP_EOL, null, 'Metrilo_Analytics.log');
        }
        
    }
}