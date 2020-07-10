<?php
class Metrilo_Analytics_Helper_ProductOptions extends Mage_Core_Helper_Abstract
{
    public function getParentOptions($product)
    {
        $productOptions   = [];
        $productType      = $product->getTypeId();
        $childrenProducts = [];
    
        if ($productType == 'configurable') {
            //collection needs some refactoring to sync product data on store view level instead of default
        $childrenProducts = Mage::getModel('catalog/product_type_configurable')
            ->setProduct($product)
            ->getUsedProductCollection()
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions();
        } elseif ($productType == 'bundle') {
            $childrenProducts = $product->getTypeInstance()
                ->getSelectionsCollection(
                    $product->getTypeInstance()->getOptionsIds($product),
                    $product
                );
        } elseif ($productType == 'grouped') {
            $childrenProducts = $product->getTypeInstance()
                ->getAssociatedProducts($product);
        }
        
        
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
    
    public function getParentIds($productId, $productType)
    {
        $parentIds = [];
    
        if ($productType === 'configurable') {
            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);
        } elseif ($productType === 'bundle') {
            $parentIds = Mage::getModel('bundle/product_type')->getParentIdsByChild($productId);
        } elseif ($productType === 'grouped') {
            $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($productId);
        }
    
        return $parentIds;
    }
}
