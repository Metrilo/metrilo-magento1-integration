<?php
class Metrilo_Analytics_Model_RemoveFromCartObserver extends Varien_Event_Observer
{
    private $_helper;
    private $_sessionEvents;
    
    public function _construct()
    {
        $this->_helper        = Mage::helper('metrilo_analytics');
        $this->_sessionEvents = Mage::helper('metrilo_analytics/sessionevents');
    }
    
    public function removeFromCart($observer)
    {
        try {
            if (!$this->_helper->isEnabled($observer->getEvent()->getQuoteItem()->getStoreId())) {
                return;
            }
            $removeFromCartEvent = new Metrilo_Analytics_Helper_Events_RemoveFromCart($observer->getEvent());
            $this->_sessionEvents->addSessionEvent($removeFromCartEvent->callJs());
        } catch (Exception $e) {
            Mage::log(json_encode(array('RemoveFromCartObserver error: ' => $e->getMessage())) . PHP_EOL, null, 'Metrilo_Analytics.log');
        }
    }
}