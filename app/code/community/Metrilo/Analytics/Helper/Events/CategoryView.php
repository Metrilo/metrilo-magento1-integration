<?php
class Metrilo_Analytics_Helper_Events_CategoryView extends Mage_Core_Helper_Abstract
{
    public function callJS() {
        return "window.metrilo.viewCategory(" . Mage::registry('current_category')->getId() . ");";
    }
}