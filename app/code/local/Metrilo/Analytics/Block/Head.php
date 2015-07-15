<?php 
/**
 * Block for head part who render all js lines
 * 
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Block_Head extends Mage_Core_Block_Template
{
    /**
     * key in session storage
     */
    const DATA_TAG = "metrilo_events";

    /**
     * Get events to track them to metrilo js api
     * 
     * @return array
     */
    public function getEvents()
    {
        $helper = Mage::helper('metrilo_analytics');
        $events = (array)Mage::getSingleton('core/session')->getData(self::DATA_TAG);
        // clear events from session ater get events once
        Mage::getSingleton('core/session')->setData(self::DATA_TAG,'');
        return $events;
    }
}