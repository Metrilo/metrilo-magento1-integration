<?php
class Metrilo_Analytics_Helper_Activity extends Mage_Core_Helper_Abstract
{
    private $_helper;
    private $_apiClient;
    
    public function createActivity($storeId, $type)
    {
        $this->_helper    = Mage::helper('metrilo_analytics');
        $this->_apiClient = Mage::helper('metrilo_analytics/apiClient');
        
        try {
            $token     = $this->_helper->getApiToken($storeId);
            $endPoint  = $this->_helper->getActivityEndpoint();
            $client    = $this->_apiClient->getClient($storeId);
    
            $data = [
                'type'   => $type
            ];
    
            $url = $endPoint . '/tracking/' . $token . '/activity';
    
            return $client->createActivity($url, $data);
        } catch (Exception $e) {
            $this->_helper->logError('ActivityHelper', $e);
        }
    }
}
