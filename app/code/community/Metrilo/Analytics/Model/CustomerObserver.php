<?php
class Metrilo_Analytics_Model_CustomerObserver extends Varien_Event_Observer
{
    private $_customerModel;
    private $_customerSerializer;
    private $_helper;
    
    public function _construct()
    {
        $this->_customerModel      = Mage::getModel('customer/customer');
        $this->_customerSerializer = Mage::helper('metrilo_analytics/customerserializer');
        $this->_helper             = Mage::helper('metrilo_analytics');
    }
    
    public function customerUpdate($observer)
    {
        try {
            $customer = $this->_getCustomerFromEvent($observer);
            if ($customer && $this->_helper->isEnabled($customer->getStoreId())) {
                if (!trim($customer->getEmail())) {
                    Mage::log('Customer with id = ' . $customer->getId() . '  has no email address!' . PHP_EOL, null, 'Metrilo_Analytics.log');
                    return;
                }
                
                $client             = Mage::helper('metrilo_analytics/apiclient')->getClient($this->_helper->getStoreId());
                $serializedCustomer = $this->_customerSerializer->serialize($customer);
                $client->customer($serializedCustomer);
            }
        } catch (Exception $e) {
            Mage::log(json_encode(array('CustomerObserver error: ' => $e->getMessage())) . PHP_EOL, null, 'Metrilo_Analytics.log');
        }
    }
    
    private function _getCustomerFromEvent($observer) {
        switch ($observer->getEvent()->getName()) {
            case 'customer_save_after':
                $customer = $observer->getEvent()->getCustomer();
                if ($customer->hasDataChanges()) {
                    return $customer;
                }
                
                break;
            case 'newsletter_subscriber_save_after':
                $subscriber = $observer->getEvent()->getSubscriber();
                if ($subscriber->getIsStatusChanged()) {
                    return $this->_customerModel->load($subscriber->getCustomerId());
                }
                
                break;
            case 'customer_register_success':
                return $observer->getEvent()->getCustomer();
                
                break;
            default:
                break;
        }
        
        return false;
    }
}
