<?php
class Metrilo_Analytics_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    private $_customerObject;
    private $_categoryObject;
    private $_productObject;
    private $_orderObject;
    
    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('metrilo/system/config/button.phtml');
        
        $this->_customerObject = Mage::getSingleton('metrilo_analytics/customerData');
        $this->_categoryObject = Mage::getSingleton('metrilo_analytics/categoryData');
        $this->_productObject  = Mage::getSingleton('metrilo_analytics/productData');
        $this->_orderObject    = Mage::getSingleton('metrilo_analytics/orderData');
    }

    /**
     * Get import instance
     *
     * @return boolean
     */
    public function getStoreId()
    {
        return Mage::getModel('core/store')->load(Mage::app()->getRequest()->getParam('store'), 'code')->getId();
    }


    /**
     * Get import instance
     *
     * @return boolean
     */
    public function buttonEnabled()
    {
        $helper = Mage::helper('metrilo_analytics');

        $request = Mage::app()->getRequest();
        $storeId = $helper->getStoreId($request);

         return $helper->isEnabled($storeId) &&
            $helper->getApiToken($storeId) && $helper->getApiSecret($storeId);
    }

    /**
    * Return element html
    *
    * @param  Varien_Data_Form_Element_Abstract $element
    * @return string
    */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
    * Return ajax url for button
    *
    * @return string
    */
    public function getAjaxUrl()
    {
        return Mage::helper('adminhtml')->getUrl("adminhtml/ajax", array('isAjax'=> true));
    }

    /**
    * Generate button html
    *
    * @return string
    */
    public function getButtonHtml()
    {
        $button = $this->getLayout()
                       ->createBlock('adminhtml/widget_button')
                       ->setData(array(
                           'id'        => 'metrilo_button',
                           'label'     => $this->helper('adminhtml')->__('Import'),
                           'onclick'   => 'javascript:sync_chunk(0, \'customers\', false); return false;'
                       ));

        return $button->toHtml();
    }
    
    public function getCustomerChunks()
    {
        $customerObject = Mage::getSingleton('metrilo_analytics/customerData');
        return $customerObject->getCustomerChunks($this->getStoreId());
    }
    
    public function getCategoryChunks()
    {
        return $this->_categoryObject->getCategoryChunks($this->getStoreId());
    }
    
    public function getProductChunks()
    {
        return $this->_productObject->getProductChunks($this->getStoreId());
    }
    
    public function getOrderChunks()
    {
        return $this->_orderObject->getOrderChunks($this->getStoreId());
    }
    
}
