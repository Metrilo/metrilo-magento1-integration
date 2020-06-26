<?php
class Metrilo_Analytics_Helper_Events_CartView extends Mage_Core_Helper_Abstract
{
    public function callJS()
    {
        return "window.metrilo.customEvent('view_cart');";
    }
}
