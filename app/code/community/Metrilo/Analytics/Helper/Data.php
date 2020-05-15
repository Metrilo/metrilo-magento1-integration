<?php
/**
 * Helper class for metrilo properties
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Helper_Data extends Mage_Core_Helper_Abstract
{
    public $js_domain = 't.metrilo.com';
    private $push_domain = 'http://p.metrilo.com';

    /**
     * Checks if the api key and secret are valid
     *
     * @return boolean
     */
    public function createActivity($storeId, $type)
    {
        $key = $this->getApiToken($storeId);
        $secret = $this->getApiSecret($storeId);

        $data = array(
            'type' => $type,
            'signature' => md5($key . $type . $secret)
        );

        $url = $this->push_domain.'/tracking/' . $key . '/activity';

        $responseCode = Mage::helper('metrilo_analytics/requestclient')->post($url, $data)['code'];

        return $responseCode == 200;
    }

    /**
     * Get session instance
     *
     * @return Mage_Core_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('core/session');
    }

    /**
    * Get storeId for the current request context
    *
    * @return string
    */
    public function getStoreId($request = null) {
        if ($request) {
            # If request is passed retrieve store by storeCode
            $storeCode = $request->getParam('store');

            if ($storeCode) {
                return Mage::getModel('core/store')->load($storeCode)->getId();
            }
        }

        # If no request or empty store code
        return Mage::app()->getStore()->getId();
    }

    /**
     * Check if metrilo module is enabled
     *
     * @return boolean
     */
    public function isEnabled($storeId = null)
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/enable', $storeId);
    }

    /**
     * Get API Token from system configuration
     *
     * @return string
     */
    public function getApiToken($storeId = null)
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/api_key', $storeId);
    }

    /**
     * Get API Secret from system configuration
     *
     * @return string
     */
    public function getApiSecret($storeId = null)
    {
        return Mage::getStoreConfig('metrilo_analytics_settings/settings/api_secret', $storeId);
    }

    /**
     * Add event to queue
     *
     * @param string $method Can be identify|track
     * @param string $type
     * @param string|array $data
     */
    public function addEvent($method, $type, $data, $metaData = false)
    {
        $events = array();

        if ($this->getSession()->getData(Metrilo_Analytics_Block_Head::DATA_TAG) != '') {
            $events = (array)$this->getSession()->getData(Metrilo_Analytics_Block_Head::DATA_TAG);
        }

        $eventToAdd = array(
            'method' => $method,
            'type' => $type,
            'data' => $data
        );

        if ($metaData) {
            $eventToAdd['metaData'] = $metaData;
        }

        if ($method == 'identify') {
            array_unshift($events, $eventToAdd);
        } else {
            array_push($events, $eventToAdd);
        }

        $this->getSession()->setData(Metrilo_Analytics_Block_Head::DATA_TAG, $events);
    }

    private function orderPaymentMethod($order) {
        try {
            return $order->getPayment()->getMethodInstance()->getTitle();
        // For the cases when the payment method was deleted.
        } catch (Exception $e) {
            return $order->getPayment()->method;
        }
    }

    private function getProductDetails($item) {
        if (Mage::getVersion() < '1.7') {
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
        } else {
            $product = $item->getProduct(); //getProduct() is missing in versions older than (1.7.0.0)
        }

        $details = array(
            'id'    => (string)$item->getProductId(),
            'sku'   => $product->getSku(),
            'price' => $item->getPrice(),
            'name'  => $item->getName()
        );

        // For the cases when the image was deleted.
        try {
            if ($product->getImage()) {
                $details['image_url'] = (string)Mage::helper('catalog/image')->init($product, 'image');
            }
        } catch (Exception $e) {}

        return $details;
    }

    private function getItems($order) {
        $items = array();

        foreach ($order->getAllVisibleItems() as $item) {
            // Main product attributes
            $parentItemDetails = $this->getProductDetails($item);
            $parentItemDetails['quantity'] = $item->getQtyOrdered();

            $childrenItems = $item->getChildrenItems();

            if (count($childrenItems) > 0) {
                foreach ($childrenItems as $childItem) {
                    $childProductDetails = $this->getProductDetails($childItem);

                    // For legacy reasons we are passing the child SKU as an identifier
                    $optionId = ($childProductDetails['sku']) ? $childProductDetails['sku'] : $childProductDetails['id'];

                    $childItemEntry = array_merge($parentItemDetails, array(
                        'option_id'    => $optionId,
                        'option_sku'   => $childProductDetails['sku'],
                        'option_price' => $parentItemDetails['price'],
                        'option_name'  => $childProductDetails['name'],
                    ));

                    array_push($items, array_filter($childItemEntry));
                }
            } else {
                array_push($items, array_filter($parentItemDetails));
            }
        }

        return $items;
    }


    /**
     * Get order details and sort them for metrilo
     *
     * @param  Mage_Sales_Model_Order $order
     * @return array
     */
    public function prepareOrderDetails($order)
    {
        $data = array(
            'order_id'          => $order->getIncrementId(),
            'order_status'      => $order->getStatus(),
            'amount'            => (float)$order->getGrandTotal(),
            'shipping_amount'   => (float)$order->getShippingAmount(),
            'tax_amount'        => $order->getTaxAmount(),
            'shipping_method'   => $order->getShippingDescription(),
            'payment_method'    => $this->orderPaymentMethod($order),
        );

        $this->_assignBillingInfo($data, $order);

        if ($order->getCouponCode()) {
            $data['coupons'] = array($order->getCouponCode());
        }

        $data['items'] = $this->getItems($order);

        Mage::log($data, null, 'Metrilo_Analytics.log');

        return $data;
    }

    /**
     * Create HTTP request to Metrilo API to sync multiple orders
     *
     * @param Array(Mage_Sales_Model_Order) $orders
     * @return void
     */
    public function callBatchApi($storeId, $orders)
    {
        try {
            $ordersForSubmition = $this->_buildOrdersForSubmition($orders);
            if (count($ordersForSubmition) < 1) {
                return;
            }
            $call = $this->_buildCall($storeId, $ordersForSubmition);

            $this->_callMetriloApi($storeId, $call);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'Metrilo_Analytics.log');
        }
    }

    // Private functions start here
    private function _callMetriloApi($storeId, $call) {
        ksort($call);

        $basedCall = base64_encode(Mage::helper('core')->jsonEncode($call));
        $signature = md5($basedCall.$this->getApiSecret($storeId));

        $requestBody = array(
            's'   => $signature,
            'hs'  => $basedCall
        );

        $client = Mage::helper('metrilo_analytics/requestclient');
        $client->post($this->push_domain.'/bt', $requestBody);
    }

    /**
     * Create submition ready arrays from Array of Mage_Sales_Model_Order
     * @param Array(Mage_Sales_Model_Order) $orders
     * @return Array of Arrays
     */
    private function _buildOrdersForSubmition($orders) {
        $ordersForSubmition = array();

        foreach ($orders as $order) {
            if ($order->getId() && $order->getStatus() != null && trim($order->getCustomerEmail())) {
                try {
                    array_push($ordersForSubmition, $this->_buildOrderForSubmition($order));
                } catch (Exception $e) {
                    Mage::log($e->getMessage(), null, 'Metrilo_Analytics.log');
                }
            }
        }

        return $ordersForSubmition;
    }

    /**
     * Build event array ready for encoding and encrypting. Built array is returned using ksort.
     *
     * @param  string  $ident
     * @param  string  $event
     * @param  array  $params
     * @param  boolean|array $identityData
     * @param  boolean|int $time
     * @param  boolean|array $callParameters
     * @return void
     */
    private function _buildEventArray($ident, $event, $params, $identityData = false, $time = false, $callParameters = false)
    {
        $call = array(
            'event_type'    => $event,
            'params'        => $params,
            'uid'           => $ident
        );

        if($time) {
            $call['time'] = $time;
        }

        $call['server_time'] = round(microtime(true) * 1000);
        // check for special parameters to include in the API call
        if($callParameters) {
            if($callParameters['use_ip']) {
                $call['use_ip'] = $callParameters['use_ip'];
            }
        }
        // put identity data in call if available
        if($identityData) {
            $call['identity'] = $identityData;
        }
        // Prepare keys is alphabetical order
        ksort($call);

        return $call;
    }

    private function _buildOrderForSubmition($order) {
        $orderDetails = $this->prepareOrderDetails($order);
        // initialize additional params
        $callParameters = false;
        // check if order has customer IP in it
        $ip = $order->getRemoteIp();
        if ($ip) {
            $callParameters = array('use_ip' => $ip);
        }
        // initialize time
        $time = false;
        if ($order->getCreatedAtStoreDate()) {
            $time = $order->getCreatedAtStoreDate()->getTimestamp() * 1000;
        }

        $identityData = $this->_orderIdentityData($order);

        return $this->_buildEventArray(
            $identityData['email'], 'order', $orderDetails, $identityData, $time, $callParameters
        );
    }


    private function _orderIdentityData($order) {
        return array(
            'email'         => $order->getCustomerEmail(),
            'first_name'    => $order->getBillingAddress()->getFirstname(),
            'last_name'     => $order->getBillingAddress()->getLastname(),
            'name'          => $order->getBillingAddress()->getName(),
        );
    }

    private function _buildCall($storeId, $ordersForSubmition) {
        $edition = (Mage::getVersion() < '1.7') ? '' : Mage::getEdition();
        return array(
            'token'    => $this->getApiToken($storeId),
            'events'   => $ordersForSubmition,
            // for debugging/support purposes
            'platform' => 'Magento ' . $edition . ' ' . Mage::getVersion(), //getEdition() is missing in systems older than 1.7.0.0
            'version'  => (string)Mage::getConfig()->getModuleConfig("Metrilo_Analytics")->version
        );
    }

    private function _assignBillingInfo(&$data, $order)
    {
        $billingAddress = $order->getBillingAddress();
        # Assign billing data to order data array
        $data['billing_phone']    = $billingAddress->getTelephone();
        $data['billing_country']  = $billingAddress->getCountryId();
        $data['billing_region']   = $billingAddress->getRegion();
        $data['billing_city']     = $billingAddress->getCity();
        $data['billing_postcode'] = $billingAddress->getPostcode();
        $data['billing_address']  = $billingAddress->getStreetFull();
        $data['billing_company']  = $billingAddress->getCompany();
    }
}
