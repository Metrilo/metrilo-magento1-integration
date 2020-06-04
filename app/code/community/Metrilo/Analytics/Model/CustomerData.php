<?php
class Metrilo_Analytics_Model_CustomerData extends Mage_Core_Model_Abstract
{
    public $chunkItems = Metrilo_Analytics_Helper_Data::CHUNK_ITEMS;
    
    public function getCustomers($storeId, $chunkId)
    {
        $metriloCustomers = [];
        $customers        = $this->getCustomerQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);
    
        foreach ($customers as $customer) {
            $metriloCustomers[] = new Metrilo_Analytics_Helper_MetriloCustomer(
                $customer->getStoreId(),
                $customer->getEmail(),
                strtotime($customer->getCreatedAt()) * 1000,
                $customer->getData('firstname'),
                $customer->getData('lastname'),
                $this->getCustomerSubscriberStatus($customer->getId()),
                $this->getCustomerGroup($customer->getGroupId())
            );
        }
    
        return $metriloCustomers;
    }
    
    public function getCustomerChunks($storeId)
    {
        $storeTotal = $this->_getCustomerQuery($storeId)->getSize();
        
        return (int)ceil($storeTotal / $this->chunkItems);
    }
    
    private function _getCustomerQuery($storeId)
    {
        return Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('store_id', $storeId);
    }
    
    private function getCustomerSubscriberStatus($customerEmail)
    {
        return Mage::getModel('newsletter/subscriber')->loadByEmail($customerEmail)->isSubscribed();
    }
    
    private function getCustomerGroup($groupId)
    {
        $group       = Mage::getModel('customer/group')->load($groupId)->getCustomerGroupCode();
        $groupName[] = $group->getCode();
        return $groupName;
    }
}
