<?php
class Metrilo_Analytics_Helper_ProductSerializer extends Mage_Core_Helper_Abstract
{
    public function serialize($product)
    {
        $productImageUrlHelper = Mage::helper('metrilo_analytics/productImageUrl');
        $productOptionsHelper  = Mage::helper('metrilo_analytics/productOptions');
        $productId             = $product->getId();
        $specialPrice          = $product->getSpecialPrice();
        $productType           = $product->getTypeId();
        
        if ($productType === 'simple' && $productOptionsHelper->getParentIds($productId, $productType) != []) {
            return;
        }
    
        $imageUrl = (!empty($product->getImage())) ?
            $productImageUrlHelper->getProductImageUrl($product->getImage()) : '';
        // Does not return grouped/bundled parent price
        $price    = (!empty($product->getPrice())) ? $product->getPrice() : 0;
        $url      = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $product->getUrlPath();
    
        if ($productType === 'configurable' || $productType === 'bundle' || $productType === 'grouped') {
            $productOptions = $productOptionsHelper->getParentOptions($product);
        } else {
            $productOptions = [];
        }
        
        return [
            'categories' => $product->getCategoryIds(),
            'id'         => $productId,
            'sku'        => $product->getSku(),
            'imageUrl'   => $imageUrl,
            'name'       => $product->getName(),
            'price'      => $specialPrice ? $specialPrice : $price,
            'url'        => $url,
            'options'    => $productOptions
        ];
    }
}
