<?php
class Metrilo_Analytics_Api_Client
{
    private $_backendParams;
    private $_endpoint;
    private $_validator;
    
    public function __construct($token, $secret, $platform, $pluginVersion, $apiEndpoint, $logPath)
    {
        $this->_backendParams['token']         = $token;
        $this->_backendParams['secret']        = $secret;
        $this->_backendParams['platform']      = $platform;
        $this->_backendParams['pluginVersion'] = $pluginVersion;
        $this->_endpoint                       = $apiEndpoint;
        $this->_validator                      = new Metrilo_Analytics_Api_Validator($logPath);
    }
    
    public function customer($customer)
    {
        $validCustomer = $this->_validator->validateCustomer($customer);
        
        if ($validCustomer) {
            return $this->backendCall('/v2/customer', ['params' => $customer]);
        }
    }
    
    public function customerBatch($customers)
    {
        $validCustomers = $this->_validator->validateCustomers($customers);
        
        if (!empty($validCustomers)) {
            return $this->backendCall('/v2/customer/batch', ['batch' => $validCustomers]);
        }
    }
    
    public function category($category)
    {
        $validCategory = $this->_validator->validateCategory($category);
        
        if ($validCategory) {
            return $this->backendCall('/v2/category', ['params' => $category]);
        }
    }
    
    public function categoryBatch($categories)
    {
        $validCategories = $this->_validator->validateCategories($categories);
        
        if (!empty($validCategories)) {
            return $this->backendCall('/v2/category/batch', ['batch' => $validCategories]);
        }
    }
    
    public function product($product)
    {
        $validProduct = $this->_validator->validateProduct($product);
        
        if ($validProduct) {
            return $this->backendCall('/v2/product', ['params' => $product]);
        }
    }
    
    public function productBatch($products)
    {
        $validProducts = $this->_validator->validateProducts($products);
        
        if (!empty($validProducts)) {
            return $this->backendCall('/v2/product/batch', ['batch' => $validProducts]);
        }
    }
    
    public function order($order)
    {
        $validOrder = $this->_validator->validateOrder($order);
        
        if ($validOrder) {
            return $this->backendCall('/v2/order', ['params' => $order]);
        }
    }
    
    public function orderBatch($orders)
    {
        $validOrders = $this->_validator->validateOrders($orders);
        
        if (!empty($validOrders)) {
            return $this->backendCall('/v2/order/batch', ['batch' => $validOrders]);
        }
    }
    
    public function createActivity($url, $data)
    {
        $connection = new Metrilo_Analytics_Api_Connection();
        $result     = $connection->post($url, $data, true);
        return $result['code'] == 200;
    }
    
    private function backendCall($path, $body)
    {
        $connection                   = new Metrilo_Analytics_Api_Connection();
        $this->_backendParams['time'] = round(microtime(true) * 1000);
        $body                         = array_merge($body, $this->_backendParams);
        
        return $connection->post($this->_endpoint.$path, $body);
    }
}
