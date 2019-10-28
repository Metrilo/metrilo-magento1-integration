<?php
class Metrilo_Analytics_Helper_Events_PageView extends Mage_Core_Helper_Abstract
{
    public function callJS() {
        return "window.metrilo.viewPage('" . Mage::helper('core/url')->getCurrentUrl() . "', " . json_encode(array('name' => Mage::getSingleton('cms/page')->getTitle())) . ");";
    }
}