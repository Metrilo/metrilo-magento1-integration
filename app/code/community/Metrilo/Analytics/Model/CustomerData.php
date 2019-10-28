<?php
class Metrilo_Analytics_Model_CustomerData extends Mage_Core_Model_Abstract
{
    public $chunkItems = Metrilo_Analytics_Helper_Data::chunkItems;
    
    public function getCustomers($storeId, $chunkId)
    {
        return $this->_getCustomerQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);
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
}
