<?php
/**
 * Ajax controller for sending orders to metrilo
 *
 * @author Miroslav Petrov <miro91tn@gmail.com>
 */
class Metrilo_Analytics_Adminhtml_AjaxController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Import order chunks
     *
     * @return void
     */
    public function indexAction()
    {
        $result = array();
        $result['success'] = false;
        $helper = Mage::helper('metrilo_analytics');
        try {
            $import = Mage::getSingleton('metrilo_analytics/import');
            $storeId = (int)$this->getRequest()->getParam('storeId');
            $chunkId = (int)$this->getRequest()->getParam('chunkId');
            $totalChunks = (int)$this->getRequest()->getParam('totalChunks');

            if ($chunkId == 0) {
                $helper->createActivity($storeId, 'import_start');
            }

            // Get orders from the Database
            $orders = $import->getOrders($storeId, $chunkId);
            // Send orders via API helper method (last parameter shows synchronity)
            $helper->callBatchApi($storeId, $orders, false);

            if ($chunkId == $totalChunks - 1) {
                $helper->createActivity($storeId, 'import_end');
            }

            $result['success'] = true;
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _isAllowed()
    {
	return true;
    }
}
