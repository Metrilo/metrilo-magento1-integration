<?php
class Metrilo_Analytics_Helper_DeletedProductSerializer extends Mage_Core_Helper_Abstract
{
    public function serialize($deletedProductOrders) {
        $productBatch = [];
        foreach ($deletedProductOrders as $order) {
            
            foreach ($order->getAllItems() as $item) {
                $parentProduct  = '';
                $productOptions = [];
                
                $parentItemId   = $item->getParentItemId();
                $itemId         = $item->getProductId();
                $itemSku        = $item->getSku();
                $itemName       = $item->getname();
                
                if ($item->getProductType() == 'configurable' || $this->_checkForProductIdIndex($itemId, $productBatch) !== false) {
                    continue;
                }
                
                if ($parentItemId) {
                    $parentProduct = $order->getItemById($parentItemId);
                    
                    if ($parentProduct) {
                        $productOptions[] = [
                            'id'       => $itemSku ? $itemSku : $itemId,
                            'sku'      => $itemSku,
                            'name'     => $itemName,
                            'price'    => $parentProduct->getPrice(),
                            'imageUrl' => ''
                        ];
                        
                        $parentIndex = $this->_checkForProductIdIndex($parentProduct->getProductId(), $productBatch);
                        if ($parentIndex !== false) {
                            if ($this->_checkForProductIdIndex($itemId, $productBatch[$parentIndex]['options']) !== false) {
                                continue;
                            }
                            $productBatch[$parentIndex]['options'] = array_merge($productBatch[$parentIndex]['options'], $productOptions);
                            continue;
                        }
                    }
                }
                
                $productBatch[] = [
                    'categories' => [],
                    'id'         => $parentProduct ? $parentProduct->getProductId() : $itemId,
                    'sku'        => $itemSku,
                    'imageUrl'   => '',
                    'name'       => $parentProduct ? $parentProduct->getName() : $itemName,
                    'price'      => $parentProduct ? 0 : $item->getPrice(),
                    'url'        => '',
                    'options'    => $productOptions
                ];
            }
        }
        
        return $productBatch;
    }
    
    private function _checkForProductIdIndex($productId, $array)
    {
        return array_search($productId, array_column($array, 'id'));
    }
}
