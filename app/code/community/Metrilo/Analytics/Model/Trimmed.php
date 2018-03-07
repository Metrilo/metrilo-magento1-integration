<?php
class Metrilo_Analytics_Model_Trimmed extends Mage_Core_Model_Config_Data
{
    public function save() {
        $value = trim($this->getValue());
        $this->setValue($value);

        return parent::save();
    }
}
