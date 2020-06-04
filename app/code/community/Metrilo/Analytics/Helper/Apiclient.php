<?php
class Metrilo_Analytics_Helper_ApiClient extends Mage_Core_Helper_Abstract
{
    private $_helper;
    
    public function getClient($storeId)
    {
        $this->_helper = Mage::helper('metrilo_analytics');
        
        try {
            $edition        = (Mage::getVersion() < '1.7') ? '' : Mage::getEdition();
            $token          = $this->_helper->getApiToken($storeId);
            $platform       = 'Magento ' . $edition . ' ' . Mage::getVersion();
            $pluginVersion  = (string)Mage::getConfig()->getModuleConfig("Metrilo_Analytics")->version;
            $apiEndpoint    = $this->_helper->getApiEndpoint();
            return new Metrilo_Analytics_Api_Client(
                $token,
                $platform,
                $pluginVersion,
                $apiEndpoint,
                Mage::getBaseDir('log')
            );
        } catch (Exception $e) {
            $this->_helper->logError('ApiClientHelper', $e);
        }
    }
}