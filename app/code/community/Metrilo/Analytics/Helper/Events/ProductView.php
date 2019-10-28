<?php
class Metrilo_Analytics_Helper_Events_ProductView extends Mage_Core_Helper_Abstract
{
    public function callJS() {
        return "window.metrilo.viewProduct(" . Mage::registry('current_product')->getId() . ");";
    }
}