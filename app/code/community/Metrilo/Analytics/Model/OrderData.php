<?php
class Metrilo_Analytics_Model_OrderData extends Mage_Core_Model_Abstract
{
    private $chunkItems = Metrilo_Analytics_Helper_Data::CHUNK_ITEMS;
    
    public function getOrders($storeId, $chunkId)
    {
        return $this->_getOrderQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);
    }
    
    public function getOrderChunks($storeId)
    {
        $storeTotal = $this->_getOrderQuery($storeId)->getSize();
        
        return (int)ceil($storeTotal / $this->chunkItems);
    }
    
    private function _getOrderQuery($storeId)
    {
        return Mage::getModel('sales/order')
            ->getCollection()
            ->addAttributeToFilter('store_id', $storeId)
            ->addAttributeToSelect('*')
            ->setOrder('entity_id', 'asc');
    }
}
