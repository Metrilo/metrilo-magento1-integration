<?php
class Metrilo_Analytics_Model_ProductData extends Mage_Core_Model_Abstract
{
    public $chunkItems = Metrilo_Analytics_Helper_Data::CHUNK_ITEMS;
    
    public function getProducts($storeId, $chunkId)
    {
        return $this->_getProductQuery($storeId)->setPageSize($this->chunkItems)->setCurPage($chunkId + 1);
    }
    
    public function getProductChunks($storeId)
    {
        $storeTotal = $this->_getProductQuery($storeId)->getSize();
        
        return (int)ceil($storeTotal / $this->chunkItems);
    }
    
    private function _getProductQuery($storeId)
    {
        return Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addStoreFilter($storeId)
                        ->addFieldToFilter('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                        ->addAttributeToSelect([
                            'entity_id',
                            'type_id',
                            'sku',
                            'created_at',
                            'updated_at',
                            'name',
                            'image',
                            'price',
                            'special_price',
                            'url_path',
                            'visibility'
                        ]);
    }
    
    public function getProductWithRequestPath($productId, $storeId)
    {
        return Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);
    }
}
