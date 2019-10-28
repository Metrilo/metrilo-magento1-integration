<?php
class Metrilo_Analytics_Helper_ProductSerializer extends Mage_Core_Helper_Abstract
{
    public function serialize($product)
    {
        $productImageUrlHelper = Mage::helper('metrilo_analytics/productimageurl');
        $productOptionsHelper  = Mage::helper('metrilo_analytics/productoptions');
        $productId = $product->getId();
        
        if ($product->getTypeId() === 'simple' && $productOptionsHelper->getParentIds($productId) != []) {
            return;
        }
    
        $imageUrl = (!empty($product->getImage())) ? $productImageUrlHelper->getProductImageUrl($product->getImage()) : '';
        $price    = (!empty($product->getPrice())) ? $product->getPrice() : 0; // Does not return grouped/bundled parent price
        $url      = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $product->getUrlPath();
    
        if ($product->isConfigurable()) {
            $productOptions = $productOptionsHelper->getConfigurableOptions($product);
        } else {
            $productOptions = [];
        }
        
        return [
            'categories' => $product->getCategoryIds(),
            'id'         => $productId,
            'sku'        => $product->getSku(),
            'imageUrl'   => $imageUrl,
            'name'       => $product->getName(),
            'price'      => $price,
            'url'        => $url,
            'options'    => $productOptions
        ];
    }
}
