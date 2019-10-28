<?php
class Metrilo_Analytics_Model_DeletedProductData extends Mage_Core_Model_Abstract
{
    public function getDeletedProductOrders($storeId)
    {
        $resource                  = Mage::getSingleton('core/resource');
        $orderItemCollection       = Mage::getModel("sales/order_item");
        $orderCollection           = Mage::getModel("sales/order");
        $deletedProductOrdersQuery = $orderItemCollection->getCollection()->getSelect()
            ->distinct()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['order_id'])
            ->joinLeft(array('catalog' => 'catalog_product_entity'), 'main_table.product_id = catalog.entity_id', array())
            ->where('catalog.entity_id IS NULL')
            ->where('main_table.store_id = ?', $storeId);
        
        $deletedProductOrderIds = $resource->getConnection('core_read')->fetchAll($deletedProductOrdersQuery);
        
        return $orderCollection->getCollection()->addFieldToFilter('entity_id', ['in' => $deletedProductOrderIds]);
    }
}
