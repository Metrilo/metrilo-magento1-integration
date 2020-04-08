<?php
class Metrilo_Analytics_Model_OrderObserver extends Varien_Event_Observer
{
    private $_helper;
    private $_orderSerializer;
    
    public function _construct()
    {
        $this->_helper          = Mage::helper('metrilo_analytics');
        $this->_orderSerializer = Mage::helper('metrilo_analytics/orderSerializer');
    }
    
    public function orderUpdate($observer)
    {
        try {
            $client          = Mage::helper('metrilo_analytics/apiClient')->getClient($this->_helper->getStoreId());
            $order           = $observer->getOrder();
            $serializedOrder = $this->_orderSerializer->serialize($order);
            if ($serializedOrder) {
                $client->order($serializedOrder);
            }
        } catch (Exception $e) {
            $this->_helper->logError('OrderObserver', $e);
        }
    }
}
