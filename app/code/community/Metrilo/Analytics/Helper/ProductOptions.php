<?php
class Metrilo_Analytics_Helper_ProductOptions extends Mage_Core_Helper_Abstract
{
    public function getConfigurableOptions($product)
    {
        $productOptions = [];
    
        //collection needs some refactoring to sync product data on store view level instead of default
        $childrenProducts = Mage::getModel('catalog/product_type_configurable')
            ->setProduct($product)
            ->getUsedProductCollection()
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions();
        
        foreach ($childrenProducts as $childProduct) {
            $imageUrl = (!empty($childProduct->getImage())) ?
                Mage::helper('metrilo_analytics/productImageUrl')->getProductImageUrl($childProduct->getImage()) : '';
            
            $childProductSpecialPrice = $childProduct->getSpecialPrice();
            $productOptions[] = [
                'id'       => $childProduct->getId(),
                'sku'      => $childProduct->getSku(),
                'name'     => $childProduct->getName(),
                'price'    => $childProductSpecialPrice ? $childProductSpecialPrice : $childProduct->getPrice(),
                'imageUrl' => $imageUrl
            ];
        }
        
        return $productOptions;
    }
    
    public function getParentIds($productId)
    {
        return Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
    }
}
