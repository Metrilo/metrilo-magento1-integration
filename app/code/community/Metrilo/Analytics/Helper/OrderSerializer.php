<?php

class Metrilo_Analytics_Helper_OrderSerializer extends Mage_Core_Helper_Abstract
{
    public function serialize($order)
    {
        if(!trim($order->getCustomerEmail())) {
            return;
        }
        $orderItems = $order->getAllItems();
        $orderProducts = [];
    
        foreach ($orderItems as $orderItem) {
            $itemType = $orderItem->getProductType();
            if ($itemType == 'configurable' || $itemType == 'bundle') { // exclude configurable/bundle parent product returned by getAllItems() method
                continue;
            }
            $orderProducts[] = [
                'productId' => $orderItem->getProductId(),
                'quantity'  => $orderItem->getQtyOrdered()
            ];
        }
    
        $orderBillingData = $order->getBillingAddress();
        $orderPhone       = $orderBillingData->getTelephone();
        $street           = $orderBillingData->getStreet();
        $couponCode       = $order->getCouponCode() ? [$order->getCouponCode()] : [];
    
        $orderBilling = [
            "firstName"     => $orderBillingData->getFirstname(),
            "lastName"      => $orderBillingData->getLastname(),
            "address"       => is_array($street) ? implode(PHP_EOL, $street) : $street,
            "city"          => $orderBillingData->getCity(),
            "countryCode"   => $orderBillingData->getCountryId(),
            "phone"         => $orderPhone,
            "postcode"      => $orderBillingData->getPostcode(),
            "paymentMethod" => $order->getPayment()->getMethodInstance()->getTitle()
        ];
        
        if (!empty($order->getCustomerEmail())) {
            $customerEmail = $order->getCustomerEmail();
        } elseif (!empty($orderPhone)) {
            $customerEmail = $orderPhone . '@phone_email';
        } else {
            return false;
        }
    
        return [
            'id'        => $order->getIncrementId(),
            'createdAt' => strtotime($order->getCreatedAt()),
            'email'     => $customerEmail,
            'amount'    => $order->getBaseGrandTotal() - $order->getTotalRefunded(),
            'coupons'   => $couponCode,
            'status'    => $order->getStatus(),
            'products'  => $orderProducts,
            'billing'   => $orderBilling
        ];
    }
}
