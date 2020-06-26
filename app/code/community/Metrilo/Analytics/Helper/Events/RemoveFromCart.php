<?php
class Metrilo_Analytics_Helper_Events_RemoveFromCart extends Mage_Core_Helper_Abstract
{
    private $_event;
    
    public function __construct
    (
        $event
    ) {
        $this->_event = $event;
    }
    
    public function callJS()
    {
        $item = $this->_event->getQuoteItem();
        
        return "window.metrilo.removeFromCart('" .
            $item->getProductId() . "', " .
            (int)$item->getQty() . ");";
    }
}
