<?php
class Metrilo_Analytics_Model_CustomerObserver extends Varien_Event_Observer
{
    private $_helper;
    private $_customerSerializer;
    private $_customerModel;
    
    public function _construct()
    {
        $this->_helper             = Mage::helper('metrilo_analytics');
        $this->_customerSerializer = Mage::helper('metrilo_analytics/customerSerializer');
        $this->_customerModel      = Mage::getModel('customer/customer');
    }
    
    public function customerUpdate($observer)
    {
        try {
            $customer = $this->_getCustomerFromEvent($observer);
            if ($customer && $this->_helper->isEnabled($customer->getStoreId())) {
                if (!trim($customer->getEmail())) {
                    $errorMsg = 'Customer with id = ' . $customer->getId() . '  has no email address!';
                    $this->_helper->logError('CustomerObserver', $errorMsg);
                    return;
                }
                
                $client             = Mage::helper('metrilo_analytics/apiclient')->getClient($customer->getStoreId());
                $serializedCustomer = $this->_customerSerializer->serialize($customer);
                $client->customer($serializedCustomer);
            }
        } catch (Exception $e) {
            $this->_helper->logError('CustomerObserver', $e);
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
