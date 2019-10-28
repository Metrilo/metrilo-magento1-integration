<?php
class Metrilo_Analytics_Helper_Events_AddToCart extends Mage_Core_Helper_Abstract
{
    private $_event;
    
    public function __construct
    (
        $event
    ) {
        $this->_event = $event;
    }
    
    public function callJS() {
        return "window.metrilo.addToCart('" . $this->_event->getProduct()->getId() . "', " . $this->_event->getQuoteItem()->getQty() . ");";
    }
}