<?php
class Metrilo_Analytics_Helper_Events_CatalogSearch extends Mage_Core_Helper_Abstract
{
    public function callJS() {
        return "window.metrilo.search('" . Mage::helper('catalogsearch')->getQueryText() . "', '" . Mage::helper('core/url')->getCurrentUrl() . "');";
    }
}