<?php

class Metrilo_Analytics_Helper_CustomerSerializer extends Mage_Core_Helper_Abstract
{
    public function serialize($customer)
    {
        $tags             = [];
        $subscriberStatus = Mage::getModel('newsletter/subscriber')->loadByEmail($customer->getEmail())->isSubscribed();
        $tags[]           = Mage::getModel('customer/group')->load($customer->getGroupId())->getCustomerGroupCode();
        
        return [
            'email'      => $customer->getEmail(),
            'createdAt'  => strtotime($customer->getCreatedAt()),
            'firstName'  => $customer->getFirstname(),
            'lastName'   => $customer->getLastname(),
            'subscribed' => $subscriberStatus,
            'tags'       => $tags
        ];
    }
}