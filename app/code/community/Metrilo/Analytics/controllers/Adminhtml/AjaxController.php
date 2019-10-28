<?php
class Metrilo_Analytics_Adminhtml_AjaxController extends Mage_Adminhtml_Controller_Action
{
    private $_customerObject;
    private $_categoryObject;
    private $_deletedProductOrderObject;
    private $_productObject;
    private $_orderObject;
    
    public function _construct()
    {
        $this->_customerObject            = Mage::getSingleton('metrilo_analytics/customerData');
        $this->_categoryObject            = Mage::getSingleton('metrilo_analytics/categoryData');
        $this->_deletedProductOrderObject = Mage::getSingleton('metrilo_analytics/deletedProductData');
        $this->_productObject             = Mage::getSingleton('metrilo_analytics/productData');
        $this->_orderObject               = Mage::getSingleton('metrilo_analytics/orderData');
    }
    
    private function _serializeRecords($records, $serializer) {
        $serializedData = [];
        foreach($records as $record) {
            $serializedRecord = $serializer->serialize($record);
            if ($serializedRecord) {
                $serializedData[] = $serializedRecord;
            }
        }
        
        return $serializedData;
    }
    
    public function indexAction()
    {
        $result            = array();
        $result['success'] = false;
        $activityHelper    = Mage::helper('metrilo_analytics/activity');
        $clientObject      = Mage::helper('metrilo_analytics/apiclient');
        
        try {
            $storeId    = (int)$this->getRequest()->getParam('storeId');
            $chunkId    = (int)$this->getRequest()->getParam('chunkId');
            $importType = (string)$this->getRequest()->getParam('importType');
            $client     = $clientObject->getClient($storeId);
            
            switch($importType) {
                case 'customers':
                    if ($chunkId == 0) {
                        $activityHelper->createActivity($storeId, 'import_start');
                    }
                    $serializedCustomers = $this->_serializeRecords($this->_customerObject->getCustomers($storeId, $chunkId), Mage::helper('metrilo_analytics/customerserializer'));
//                    Mage::log(json_encode(array('SerializedCustomers' => $serializedCustomers)) . PHP_EOL, null, 'Metrilo_Analytics.log');
//                    Mage::log(json_encode(array('ClientCall' => $client->customerBatch($serializedCustomers))) . PHP_EOL, null, 'Metrilo_Analytics.log');
                    // Unlike m2 where every customer has been assigned to specific store (storeview), in m1 customers created
                    // via admin have admin storeId (0) as value witch makes it impossible to map these customers to theirs
                    // respective store views (since all admin created accounts will have storeId value of 0). Also admin
                    // created accounts can be assigned to admin website witch makes it impossible for the account to login on
                    // the front-end but makes it possible to create orders for that account via admin :(
                    $result['success']   = $client->customerBatch($serializedCustomers);
                    break;
                case 'categories':
                    $serializedCategories = $this->_serializeRecords($this->_categoryObject->getCategories($storeId, $chunkId), Mage::helper('metrilo_analytics/categoryserializer'));
//                    Mage::log(json_encode(array('SerializedCategories' => $serializedCategories)) . PHP_EOL, null, 'Metrilo_Analytics.log');
//                    Mage::log(json_encode(array('ClientCall' => $client->categoryBatch($serializedCategories))) . PHP_EOL, null, 'Metrilo_Analytics.log');
                    $result['success']    = $client->categoryBatch($serializedCategories);
                    break;
                case 'deletedProducts':
                    $deletedProductOrders = $this->_deletedProductOrderObject->getDeletedProductOrders($storeId);
                    if ($deletedProductOrders) {
                        $serializedDeletedProducts = Mage::helper('metrilo_analytics/deletedproductserializer')->serialize($deletedProductOrders);
                        $deletedProductChunks      = array_chunk($serializedDeletedProducts, Metrilo_Analytics_Helper_Data::chunkItems);
                        foreach($deletedProductChunks as $chunk) {
//                            Mage::log(json_encode(array('SerializedDeletedProducts' => $chunk)) . PHP_EOL, null, 'Metrilo_Analytics.log');
//                            Mage::log(json_encode(array('ClientCall' => $client->productBatch($chunk))) . PHP_EOL, null, 'Metrilo_Analytics.log');
                            $client->productBatch($chunk);
                        }
                    }
                    break;
                case 'products':
                    $serializedProducts = $this->_serializeRecords($this->_productObject->getProducts($storeId, $chunkId), Mage::helper('metrilo_analytics/productserializer'));
//                    Mage::log(json_encode(array('SerializedProducts' => $serializedProducts)) . PHP_EOL, null, 'Metrilo_Analytics.log');
//                    Mage::log(json_encode(array('ClientCall' => $client->productBatch($serializedProducts))) . PHP_EOL, null, 'Metrilo_Analytics.log');
                    $result['success'] = $client->productBatch($serializedProducts);
                    break;
                case 'orders':
                    $serializedOrders = $this->_serializeRecords($this->_orderObject->getOrders($storeId, $chunkId), Mage::helper('metrilo_analytics/orderserializer'));
//                    Mage::log(json_encode(array('SerializedOrders' => $serializedOrders)) . PHP_EOL, null, 'Metrilo_Analytics.log');
//                    Mage::log(json_encode(array('ClientCall' => $client->orderBatch($serializedOrders))) . PHP_EOL, null, 'Metrilo_Analytics.log');
                    $result['success'] = $client->orderBatch($serializedOrders); //disable to reduce api call spam to production project.
                    if ($chunkId == (int)$this->getRequest()->getParam('ordersChunks') - 1) {
                        $activityHelper->createActivity($storeId, 'import_end');
                    }
                    break;
                default:
                    $result['success'] = false;
                    break;
            }
            $result['success'] = true;
        } catch (Exception $e) {
            Mage::log(json_encode(array('AjaxController error: ' => $e->getMessage())) . PHP_EOL, null, 'Metrilo_Analytics.log');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _isAllowed()
    {
        return true;
    }
}
