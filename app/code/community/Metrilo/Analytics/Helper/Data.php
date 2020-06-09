<?php
class Metrilo_Analytics_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CHUNK_ITEMS = 50;
    
    public function getStoreId($request = null)
    {
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
    
    public function getStoreIdsPerProject($storeIds)
    {
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
    
    public function logError($file, $exception)
    {
        if ($exception instanceof \Exception) {
            $this->log($file, $exception->getMessage());
        } else {
            $this->log($file, $exception);
        }
    }
    
    private function log($file, $exception)
    {
        $logLocation = BP . '/var/log/Metrilo_Analytics.log';
        if (file_exists($logLocation) && filesize($logLocation) > 10 * 1024 * 1024) {
            unlink($logLocation);
        }
        
        Mage::log(json_encode(array($file . ' error: ' => $exception)) . PHP_EOL, null, 'Metrilo_Analytics.log');
    }
}
