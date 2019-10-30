<?php
class Metrilo_Analytics_Model_OrderObserver extends Varien_Event_Observer
{
    private $_orderSerializer;
    private $_helper;
    
    public function _construct()
    {
        $this->_orderSerializer = Mage::helper('metrilo_analytics/orderSerializer');
        $this->_helper          = Mage::helper('metrilo_analytics');
    }
    
    public function orderUpdate($observer)
    {
        try {
            $client          = Mage::helper('metrilo_analytics/apiclient')->getClient($this->_helper->getStoreId());
            $order           = $observer->getOrder();
            $serializedOrder = $this->_orderSerializer->serialize($order);
            $client->order($serializedOrder);
        } catch (Exception $e) {
            Mage::log(json_encode(array('OrderObserver error: ' => $e->getMessage())) . PHP_EOL, null, 'Metrilo_Analytics.log');
        }
    }
}
