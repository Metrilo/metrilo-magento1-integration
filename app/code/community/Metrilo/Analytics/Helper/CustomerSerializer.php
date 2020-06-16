<?php

class Metrilo_Analytics_Helper_CustomerSerializer extends Mage_Core_Helper_Abstract
{
    public function serialize($customer)
    {
        return [
            'email'      => $customer->getEmail(),
            'createdAt'  => $customer->getCreatedAt(),
            'firstName'  => $customer->getFirstname(),
            'lastName'   => $customer->getLastname(),
            'subscribed' => $customer->getSubscriberStatus(),
            'tags'       => $customer->getTags()
        ];
    }
}
