<?php
class Metrilo_Analytics_Helper_Events_CustomEvent extends Mage_Core_Helper_Abstract
{
    private $customEvent;
    
    public function __construct
    (
        $customEvent
    ) {
        $this->customEvent = $customEvent;
    }
    
    public function callJS()
    {
        return 'window.metrilo.customEvent("' . $this->customEvent . '");';
    }
}
