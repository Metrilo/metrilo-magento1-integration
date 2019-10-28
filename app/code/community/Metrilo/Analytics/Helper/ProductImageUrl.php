<?php
class Metrilo_Analytics_Helper_ProductImageUrl extends Mage_Core_Helper_Abstract
{
    public function getProductImageUrl($imageUrlRequestPath)
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product'  . $imageUrlRequestPath;
    }
}
