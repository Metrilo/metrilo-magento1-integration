<?php
class Metrilo_Analytics_Model_CategoryData extends Mage_Core_Model_Abstract
{
    public $chunkItems = Metrilo_Analytics_Helper_Data::chunkItems;
    
    public function getCategories($storeId, $chunkId)
    {
        return $this->_getCategoryQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);
    }
    
    public function getCategoryChunks($storeId)
    {
        $storeTotal = $this->_getCategoryQuery($storeId)->getSize();
        
        return (int)ceil($storeTotal / $this->chunkItems);
    }
    
    public function getCategoryWithRequestPath($categoryId, $storeId) {
        
        $categoryObject = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('entity_id', $categoryId)
            ->addUrlRewriteToResult()
            ->getFirstItem();
        
        $categoryObject->setStoreId($storeId);
        
        return $categoryObject;
    }
    
    private function _getCategoryQuery($storeId)
    {
        return Mage::getModel('catalog/category')
            ->getCollection()
            ->setStoreId($storeId)
            ->addAttributeToSelect('*');
    }
}
