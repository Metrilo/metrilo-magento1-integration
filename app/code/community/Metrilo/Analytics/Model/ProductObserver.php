<?php
class Metrilo_Analytics_Model_ProductObserver extends Varien_Event_Observer
{
    private $_productSerializer;
    private $_productData;
    private $_helper;
    
    public function _construct()
    {
        $this->_productSerializer = Mage::helper('metrilo_analytics/productSerializer');
        $this->_productData       = Mage::getModel('metrilo_analytics/productData');
        $this->_helper            = Mage::helper('metrilo_analytics');
    }
    
    public function productUpdate($observer)
    {
        try {
            $product        = $observer->getEvent()->getProduct();
            $productStoreId = $product->getStoreId();
            
            if ($productStoreId == 0) {
                $productStoreIds = $this->_helper->getStoreIdsPerProject($product->getStoreIds());
            } else {
                if (!$this->_helper->isEnabled($productStoreId)) {
                    return;
                }
                $productStoreIds[] = $productStoreId;
            }
            foreach ($productStoreIds as $storeId) {
                $client         = Mage::helper('metrilo_analytics/apiclient')->getClient($storeId);
                $productParents = Mage::helper('metrilo_analytics/productOptions')->getParentIds($product->getId());
                $productsToSync = ($productParents) ? $productParents : [$product->getId()];
    
                foreach ($productsToSync as $productId) {
                    $productWithRequestPath = $this->_productData->getProductWithRequestPath($productId, $storeId);
                    $serializedProduct      = $this->_productSerializer->serialize($productWithRequestPath);
                    $client->product($serializedProduct);
                }
            }
        } catch (Exception $e) {
            Mage::log(json_encode(array('ProductObserver error: ' => $e->getMessage())) . PHP_EOL, null, 'Metrilo_Analytics.log');
        }
    }
}
