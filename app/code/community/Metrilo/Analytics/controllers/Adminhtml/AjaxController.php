<?php
class Metrilo_Analytics_Adminhtml_AjaxController extends Mage_Adminhtml_Controller_Action
{
    private $_helper;
    private $_activityHelper;
    private $_clientObject;
    private $_customerObject;
    private $_categoryObject;
    private $_deletedProductOrderObject;
    private $_productObject;
    private $_orderObject;
    
    public function _construct()
    {
        $this->_helper                    = Mage::helper('metrilo_analytics');
        $this->_activityHelper            = Mage::helper('metrilo_analytics/activity');
        $this->_clientObject              = Mage::helper('metrilo_analytics/apiClient');
        $this->_customerObject            = Mage::getModel('metrilo_analytics/customerData');
        $this->_categoryObject            = Mage::getModel('metrilo_analytics/categoryData');
        $this->_deletedProductOrderObject = Mage::getModel('metrilo_analytics/deletedProductData');
        $this->_productObject             = Mage::getModel('metrilo_analytics/productData');
        $this->_orderObject               = Mage::getModel('metrilo_analytics/orderData');
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
        
        try {
            $storeId    = (int)$this->getRequest()->getParam('storeId');
            $chunkId    = (int)$this->getRequest()->getParam('chunkId');
            $importType = (string)$this->getRequest()->getParam('importType');
            $client     = $this->_clientObject->getClient($storeId);
            
            switch($importType) {
                case 'customers':
                    if ($chunkId == 0) {
                        $this->_activityHelper->createActivity($storeId, 'import_start');
                    }
                    $serializedCustomers = $this->_serializeRecords($this->_customerObject->getCustomers($storeId, $chunkId), Mage::helper('metrilo_analytics/customerSerializer'));
                    // Unlike m2 where every customer has been assigned to specific store (storeview), in m1 customers created
                    // via admin have admin storeId (0) as value witch makes it impossible to map these customers to theirs
                    // respective store views (since all admin created accounts will have storeId value of 0). Also admin
                    // created accounts can be assigned to admin website witch makes it impossible for the account to login on
                    // the front-end but makes it possible to create orders for that account via admin :(
                    $result['success']   = $client->customerBatch($serializedCustomers);
                    break;
                case 'categories':
                    $serializedCategories = $this->_serializeRecords($this->_categoryObject->getCategories($storeId, $chunkId), Mage::helper('metrilo_analytics/categorySerializer'));
                    $result['success']    = $client->categoryBatch($serializedCategories);
                    break;
                case 'deletedProducts':
                    $deletedProductOrders = $this->_deletedProductOrderObject->getDeletedProductOrders($storeId);
                    if ($deletedProductOrders) {
                        $serializedDeletedProducts = Mage::helper('metrilo_analytics/deletedproductserializer')->serialize($deletedProductOrders);
                        $deletedProductChunks      = array_chunk($serializedDeletedProducts, Metrilo_Analytics_Helper_Data::chunkItems);
                        foreach($deletedProductChunks as $chunk) {
                            $client->productBatch($chunk);
                        }
                    }
                    break;
                case 'products':
                    $serializedProducts = $this->_serializeRecords($this->_productObject->getProducts($storeId, $chunkId), Mage::helper('metrilo_analytics/productSerializer'));
                    $result['success'] = $client->productBatch($serializedProducts);
                    break;
                case 'orders':
                    $serializedOrders = $this->_serializeRecords($this->_orderObject->getOrders($storeId, $chunkId), Mage::helper('metrilo_analytics/orderSerializer'));
                    $result['success'] = $client->orderBatch($serializedOrders); //disable to reduce api call spam to production project.
                    if ($chunkId == (int)$this->getRequest()->getParam('ordersChunks') - 1) {
                        $this->_activityHelper->createActivity($storeId, 'import_end');
                    }
                    break;
                default:
                    $result['success'] = false;
                    break;
            }
            $result['success'] = true;
        } catch (Exception $e) {
            $this->_helper->logError('AjaxController', $e);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
