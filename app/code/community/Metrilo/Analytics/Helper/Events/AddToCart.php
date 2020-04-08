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
        return "window.metrilo.addToCart('" . $this->getItemIdentifier() . "', " . $this->_event->getQuoteItem()->getQty() . ");";
    }
    
    private function getItemIdentifier() {
        $item = $this->_event->getQuoteItem();
        $itemOptions = $item->getChildren();
        
        if ($itemOptions) {
            $itemSku = $itemOptions[0]->getSku();
            
            if ($itemSku) {
                return $itemSku;
            } else {
                return $itemOptions[0]->getProductId();
            }
        }
        
        return $item->getProductId();
    }
}