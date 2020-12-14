<?php
class Metrilo_Analytics_Model_ConfigObserver extends Varien_Event_Observer
{
    private $_helper;
    private $_activityHelper;
    
    public function _construct()
    {
        $this->_helper         = Mage::helper('metrilo_analytics');
        $this->_activityHelper = Mage::helper('metrilo_analytics/activity');
    }
    
    public function saveConfig($observer)
    {
        try {
            $storeId = $observer->getStore();
            if (!$this->_activityHelper->createActivity($storeId, 'integrated')) {
                if ($storeId === 0) {
                    Mage::getSingleton('core/session')->addError(
                        'You\'ve just entered the API token and API Secret to the default configuration scope .
                        This means that the Metrilo module will be added to all your store views .
                        If you want to connect only a specific store view, please remove it form the default scope and
                        add it only to the specific store view configuration scope .
                        You can find the "Import" button by opening any specific configuration scope .'
                    );
                } else {
                    Mage::getSingleton('core/session')->addError(
                        'The API Token and/or API Secret you have entered are invalid.
                        You can find the correct ones in Settings -> Installation in your Metrilo account.'
                    );
                }
            }
        } catch (Exception $e) {
            $this->_helper->logError('ConfigObserver', $e);
        }
        
    }
}
