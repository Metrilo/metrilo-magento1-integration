<?php
class Metrilo_Analytics_Helper_Events_CatalogSearch extends Mage_Core_Helper_Abstract
{
    public function callJS()
    {
        $query = (Mage::helper('catalogsearch')->getQueryText()) ?
            Mage::helper('catalogsearch')->getQueryText() : 'advanced_search';
        return "window.metrilo.search('" . $query . "', '" . Mage::helper('core/url')->getCurrentUrl() . "');";
    }
}
