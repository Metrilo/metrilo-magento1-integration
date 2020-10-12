<?php

class Metrilo_Analytics_Model_CustomerObserver extends Varien_Event_Observer
{
    private $_helper;
    private $_customerSerializer;
    private $_customerModel;
    private $_subscriberModel;
    private $_customerGroupModel;
    private $_sessionEvents;
    
    public function _construct()
    {
        $this->_helper             = Mage::helper('metrilo_analytics');
        $this->_customerSerializer = Mage::helper('metrilo_analytics/customerSerializer');
        $this->_customerModel      = Mage::getModel('customer/customer');
        $this->_subscriberModel    = Mage::getModel('newsletter/subscriber');
        $this->_customerGroupModel = Mage::getModel('customer/group');
        $this->_sessionEvents      = Mage::helper('metrilo_analytics/sessionEvents');
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
                
                $client             = Mage::helper('metrilo_analytics/apiClient')->getClient($customer->getStoreId());
                $serializedCustomer = $this->_customerSerializer->serialize($customer);
                $client->customer($serializedCustomer);
            }
        } catch (Exception $e) {
            $this->_helper->logError('CustomerObserver', $e);
        }
    }
    
    private function _getCustomerFromEvent($observer)
    {
        switch ($observer->getEvent()->getName()) {
            case 'customer_save_after':
                $customer = $observer->getEvent()->getCustomer();
                if ($customer->hasDataChanges()) {
                    return new Metrilo_Analytics_Helper_MetriloCustomer(
                        $customer->getStoreId(),
                        $customer->getEmail(),
                        strtotime($customer->getCreatedAt()) * 1000,
                        $customer->getData('firstname'),
                        $customer->getData('lastname'),
                        $this->getCustomerSubscriberStatus($customer->getId()),
                        $this->getCustomerGroup($customer->getGroupId())
                    );
                }
                
                break;
            case 'newsletter_subscriber_save_after':
                $subscriber = $observer->getEvent()->getSubscriber();
                $customerId = $subscriber->getCustomerId();
                if ($subscriber->getIsStatusChanged() && $customerId !== 0) {
                    return $this->metriloCustomer($this->_customerModel->load($customerId));
                } else {
                    $subscriberEmail = $subscriber->getEmail();
                    $identifyCustomer = new Metrilo_Analytics_Helper_Events_IdentifyCustomer($subscriberEmail);
                    $customEvent      = new Metrilo_Analytics_Helper_Events_CustomEvent('Subscribed');
    
                    $this->_sessionEvents->addSessionEvent($identifyCustomer->callJs());
                    $this->_sessionEvents->addSessionEvent($customEvent->callJs());
                    
                    return new Metrilo_Analytics_Helper_MetriloCustomer(
                        $subscriber->getStoreId(),
                        $subscriberEmail,
                        strtotime($subscriber->getData('change_status_at')) * 1000,
                        '',
                        '',
                        true,
                        ['Newsletter']
                    );
                }
                
                break;
            case 'customer_register_success':
                return $this->metriloCustomer($observer->getEvent()->getCustomer());
                
                break;
            case 'sales_order_save_after':
                return new Metrilo_Analytics_Helper_MetriloCustomer(
                    $observer->getEvent()->getOrder()->getStoreId(),
                    $observer->getEvent()->getOrder()->getCustomerEmail(),
                    strtotime($observer->getEvent()->getOrder()->getCreatedAt()) * 1000,
                    $observer->getEvent()->getOrder()->getBillingAddress()->getData('firstname'),
                    $observer->getEvent()->getOrder()->getBillingAddress()->getData('lastname'),
                    true,
                    ['guest_customer']
                );
            default:
                break;
        }
        
        return false;
    }
    
    private function metriloCustomer($customer)
    {
        return new Metrilo_Analytics_Helper_MetriloCustomer(
            $customer->getStoreId(),
            $customer->getEmail(),
            strtotime($customer->getCreatedAt()) * 1000,
            $customer->getFirstName(),
            $customer->getLastName(),
            $this->getCustomerSubscriberStatus($customer->getEmail()),
            $this->getCustomerGroup($customer->getGroupId())
        );
    }
    
    private function getCustomerSubscriberStatus($customerEmail)
    {
        return $this->_subscriberModel->loadByEmail($customerEmail)->isSubscribed();
    }
    
    private function getCustomerGroup($groupId)
    {
        $groupName[] = $this->_customerGroupModel->load($groupId)->getCustomerGroupCode();
        return $groupName;
    }
}
