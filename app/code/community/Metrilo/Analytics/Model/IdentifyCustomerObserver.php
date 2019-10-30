<?php
class Metrilo_Analytics_Model_IdentifyCustomerObserver extends Varien_Event_Observer
{
    private $_helper;
    private $_sessionEvents;
    
    public function _construct()
    {
        $this->_helper        = Mage::helper('metrilo_analytics');
        $this->_sessionEvents = Mage::helper('metrilo_analytics/sessionEvents');
    }
    
    private function getEventEmail($observer) {
        switch ($observer->getEvent()->getName()) {
            // identify on customer login action
            case 'customer_login':
                return $observer->getEvent()->getCustomer()->getEmail();
            // identify on customer account edit action
            case 'customer_account_edited':
                return $observer->getEvent()->getEmail();
            // identify on customer place order action
            case 'sales_order_save_after':
                return $observer->getEvent()->getOrder()->getCustomerEmail();
            default:
                break;
        }
        
        return false;
    }
    
    public function identifyCustomer($observer)
    {
        try {
            $identifyEmail = $this->getEventEmail($observer);
        
            if ($identifyEmail && $this->_helper->isEnabled($observer->getEvent()->getStoreId())) {
                $identifyCustomerEvent = new Metrilo_Analytics_Helper_Events_IdentifyCustomer($identifyEmail);
                $this->_sessionEvents->addSessionEvent($identifyCustomerEvent->callJs());
            }
        } catch (Exception $e) {
            Mage::log(json_encode(array('IdentifyCustomerObserver error: ' => $e->getMessage())) . PHP_EOL, null, 'Metrilo_Analytics.log');
        }
    }
}
