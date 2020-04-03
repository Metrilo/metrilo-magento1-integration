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
            if (!$this->_activityHelper->createActivity($observer->getStore(), 'integrated')) {
                Mage::getSingleton('core/session')->addError('The API Token and/or API Secret you have entered are invalid. You can find the correct ones in Settings -> Installation in your Metrilo account.');
            }
        } catch (Exception $e) {
            $this->_helper->logError('ConfigObserver', $e);
        }
        
    }
}

