<?php
class Metrilo_Analytics_Block_Head extends Mage_Core_Block_Template
{
    
    /**
     * key in session storage
     */
    const DATA_TAG = "metrilo_events";
    
    private $_helper;
    private $_sessionEvents;
    
    public function _construct()
    {
        $this->_helper        = Mage::helper('metrilo_analytics');
        $this->_sessionEvents = Mage::helper('metrilo_analytics/sessionEvents');
    }

    /**
     * Get events to track them to metrilo js api
     *
     * @return array
     */
    public function getEvent()
    {
        $actionName = Mage::app()->getFrontController()->getAction()->getFullActionName();
        switch($actionName) {
            // product view pages
            case 'catalog_product_view':
                return Mage::helper('metrilo_analytics/events_productView')->callJs();
            // category view pages
            case 'catalog_category_view':
                return Mage::helper('metrilo_analytics/events_categoryView')->callJS();
            // catalog search pages
            case 'catalogsearch_result_index':
                return Mage::helper('metrilo_analytics/events_catalogSearch')->callJS();
            // catalog advanced result page
            case 'catalogsearch_advanced_result':
                return Mage::helper('metrilo_analytics/events_catalogSearch')->callJS();
            // cart view pages
            case 'checkout_cart_index':
                return Mage::helper('metrilo_analytics/events_cartView')->callJS();
            // checkout view page
            case 'checkout_index_index':
                return Mage::helper('metrilo_analytics/events_checkoutView')->callJS();
            // CMS and any other pages
            default:
                return Mage::helper('metrilo_analytics/events_pageView')->callJs();
        }
    }
    
    public function getEvents() {
        $sessionEvents   = $this->_sessionEvents->getSessionEvents();
        $sessionEvents[] = $this->getEvent();
        return $sessionEvents;
    }
    
    public function getLibraryUrl() {
        return $this->_helper->getApiEndpoint() . '/tracking.js?token=' . $this->_helper->getApiToken($this->_helper->getStoreId());
    }

    /**
     * Render metrilo js if module is enabled
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        $request = Mage::app()->getRequest();
        $storeId = $this->_helper->getStoreId($request);

        if($this->_helper->isEnabled($storeId)) {
            return $html;
        }

        return "";
    }
}
