<?php
class Metrilo_Analytics_Helper_SessionEvents extends Mage_Core_Helper_Abstract
{
    private $metriloSessionEvents = 'metrilo_session_key';
    
    public function addSessionEvent($data)
    {
        $events   = $this->getSessionEvents();
        $events[] = $data;
        Mage::getSingleton('core/session')->setData($this->metriloSessionEvents, $events);
    }
    
    public function getSessionEvents()
    {
        $sessionEvents = Mage::getSingleton('core/session')->getData($this->metriloSessionEvents, true);
        
        if ($sessionEvents === null) {
            $sessionEvents = [];
        }
        
        return $sessionEvents;
    }
}