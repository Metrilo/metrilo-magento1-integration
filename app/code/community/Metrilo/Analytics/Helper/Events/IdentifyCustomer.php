<?php
class Metrilo_Analytics_Helper_Events_IdentifyCustomer extends Mage_Core_Helper_Abstract
{
    private $_email;
    
    public function __construct
    (
        $email
    ) {
        $this->_email = $email;
    }
    
    public function callJS()
    {
        return 'window.metrilo.identify("' . $this->_email . '");';
    }
}
