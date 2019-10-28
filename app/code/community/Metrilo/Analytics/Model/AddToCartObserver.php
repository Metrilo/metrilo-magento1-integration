<?php
class Metrilo_Analytics_Model_AddToCartObserver extends Varien_Event_Observer
{
    private $_helper;
    private $_sessionEvents;
    
    public function _construct()
    {
        $this->_helper        = Mage::helper('metrilo_analytics');
        $this->_sessionEvents = Mage::helper('metrilo_analytics/sessionevents');
    }
    
    public function addToCart($observer)
    {
        try {
            if (!$this->_helper->isEnabled($observer->getEvent()->getProduct()->getStoreId())) {
                return;
            }
            $addToCartEvent = new Metrilo_Analytics_Helper_Events_AddToCart($observer->getEvent());
            $this->_sessionEvents->addSessionEvent($addToCartEvent->callJs());
//            Mage::log(json_encode(array('addToCart event: ' => $addToCartEvent)) . PHP_EOL, null, 'Metrilo_Analytics.log');
        } catch (Exception $e) {
            Mage::log(json_encode(array('AddToCartObserver error: ' => $e->getMessage())) . PHP_EOL, null, 'Metrilo_Analytics.log');
        }
    }
}