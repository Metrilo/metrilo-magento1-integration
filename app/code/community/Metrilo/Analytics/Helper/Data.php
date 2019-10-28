<?php
class Metrilo_Analytics_Helper_Data extends Mage_Core_Helper_Abstract
{
    const chunkItems = 50;
    
    public function getStoreId($request = null) {
        if ($request) {
            # If request is passed retrieve store by storeCode
            $storeCode = $request->getParam('store');
            
            if ($storeCode) {
                return (int)Mage::getModel('core/store')->load($storeCode)->getId();
            }
        }
        
        # If no request or empty store code
        return (int)Mage::app()->getStore()->getId();
    }
    
    public function isEnabled($storeId = null)
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/enable', $storeId);
    }
    
    public function getApiToken($storeId = null)
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/api_key', $storeId);
    }
    
    public function getApiSecret($storeId = null)
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/api_secret', $storeId);
    }
    
    public function getApiEndpoint()
    {
        $apiEndpoint = Mage::getStoreConfig('metrilo_analytics_settings/settings/api_endpoint');
        return ($apiEndpoint) ? $apiEndpoint : 'https://trk.mtrl.me';
    }
    
    public function getActivityEndpoint()
    {
        $activityEndpoint = Mage::getStoreConfig('metrilo_analytics_settings/settings/activity_endpoint');
        
        return ($activityEndpoint) ? $activityEndpoint : 'https://p.metrilo.com';
    }
    
    public function getStoreIdsPerProject($storeIds) {
        $storeIdConfigMap = [];
        foreach ($storeIds as $storeId) {
            if ($storeId == 0 || !$this->isEnabled($storeId)) { // store 0 is always admin
                continue;
            }
            $storeIdConfigMap[$storeId] = Mage::getStoreConfig('metrilo_analytics_settings/settings/api_key', $storeId);
        }
        $storeIdConfigMap = array_unique($storeIdConfigMap);
        
        return array_keys($storeIdConfigMap);
    }
}
