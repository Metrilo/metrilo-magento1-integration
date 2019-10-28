<?php
class Metrilo_Analytics_Helper_Activity extends Mage_Core_Helper_Abstract
{
    public function createActivity($storeId, $type)
    {
        try {
            $helper    = Mage::helper('metrilo_analytics');
            $apiClient = Mage::helper('metrilo_analytics/apiclient');
    
            $token     = $helper->getApiToken($storeId);
            $secret    = $helper->getApiSecret($storeId);
            $endPoint  = $helper->getActivityEndpoint();
            $client    = $apiClient->getClient($storeId);
    
            $data = array(
                'type'          => $type,
                'project_token' => $token,
                'signature'     => md5($token . $type . $secret)
            );
    
            $url = $endPoint . '/tracking/' . $token . '/activity';
    
            return $client->createActivity($url, $data);
        } catch (Exception $e) {
            Mage::log(json_encode(array('ActivityHelper error: ' => $e->getMessage())) . PHP_EOL, null, 'Metrilo_Analytics.log');
        }
    }
}