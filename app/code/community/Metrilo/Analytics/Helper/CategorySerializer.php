<?php

class Metrilo_Analytics_Helper_CategorySerializer extends Mage_Core_Helper_Abstract
{
    public function serialize($category)
    {
        $requestPathObject = Mage::getSingleton('core/url_rewrite');
        $categoryId        = $category->getId();
        
        // available only for categories with url key
        $requestPath = $requestPathObject->getResource()
            ->getRequestPathByIdPath('category/' . $categoryId, $category->getStoreId());

        if(!empty($requestPath))
        {
            $url = Mage::getBaseUrl() . $requestPath;
        } else {
            // if category has no url key load category model and get base cat url
            $url = Mage::getModel('catalog/category')->load($categoryId)->getUrl();
        }
    
        return [
            'id'   => $categoryId,
            'name' => $category->getName(),
            'url'  => $url
        ];
    }
}