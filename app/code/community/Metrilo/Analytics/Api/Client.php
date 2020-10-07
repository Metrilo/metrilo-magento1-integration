<?php
class Metrilo_Analytics_Api_Client
{
    private $_backendParams;
    private $_endpoint;
    private $_validator;
    private $_secret;
    private $_customerPath = '/v2/customer';
    private $_categoryPath = '/v2/category';
    private $_productPath  = '/v2/product';
    private $_orderPath    = '/v2/order';
    
    public function __construct($token, $secret, $platform, $pluginVersion, $apiEndpoint, $logPath)
    {
        $this->_backendParams['token']         = $token;
        $this->_secret                         = $secret;
        $this->_backendParams['platform']      = $platform;
        $this->_backendParams['pluginVersion'] = $pluginVersion;
        $this->_endpoint                       = $apiEndpoint;
        $this->_validator                      = new Metrilo_Analytics_Api_Validator($logPath);
    }
    
    public function customer($customer)
    {
        $validCustomer = $this->_validator->validateCustomer($customer);
        
        if ($validCustomer) {
            return $this->backendCall($this->_customerPath, ['params' => $customer]);
        }
    }
    
    public function customerBatch($customers)
    {
        $validCustomers = $this->_validator->validateCustomers($customers);
        
        if (!empty($validCustomers)) {
            return $this->backendCall($this->_customerPath . '/batch', ['batch' => $validCustomers]);
        }
    }
    
    public function category($category)
    {
        $validCategory = $this->_validator->validateCategory($category);
        
        if ($validCategory) {
            return $this->backendCall($this->_categoryPath, ['params' => $category]);
        }
    }
    
    public function categoryBatch($categories)
    {
        $validCategories = $this->_validator->validateCategories($categories);
        
        if (!empty($validCategories)) {
            return $this->backendCall($this->_categoryPath . '/batch', ['batch' => $validCategories]);
        }
    }
    
    public function product($product)
    {
        $validProduct = $this->_validator->validateProduct($product);
        
        if ($validProduct) {
            return $this->backendCall($this->_productPath, ['params' => $product]);
        }
    }
    
    public function productBatch($products)
    {
        $validProducts = $this->_validator->validateProducts($products);
        
        if (!empty($validProducts)) {
            return $this->backendCall($this->_productPath . '/batch', ['batch' => $validProducts]);
        }
    }
    
    public function order($order)
    {
        $validOrder = $this->_validator->validateOrder($order);
        
        if ($validOrder) {
            return $this->backendCall($this->_orderPath, ['params' => $order]);
        }
    }
    
    public function orderBatch($orders)
    {
        $validOrders = $this->_validator->validateOrders($orders);
        
        if (!empty($validOrders)) {
            return $this->backendCall($this->_orderPath . '/batch', ['batch' => $validOrders]);
        }
    }
    
    public function createActivity($url, $data)
    {
        $connection = new Metrilo_Analytics_Api_Connection();
        $result     = $connection->post($url, $data, $this->_secret);
        return $result['code'] == 200;
    }
    
    private function backendCall($path, $body)
    {
        $connection                   = new Metrilo_Analytics_Api_Connection();
        $this->_backendParams['time'] = round(microtime(true) * 1000);
        $body                         = array_merge($body, $this->_backendParams);
        
        return $connection->post($this->_endpoint.$path, $body, $this->_secret);
    }
}
