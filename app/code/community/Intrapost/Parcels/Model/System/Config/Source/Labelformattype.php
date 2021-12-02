<?php


class Intrapost_Parcels_Model_System_Config_Source_Labelformattype
{
    protected $_options;
    public function toOptionArray()
    {
        $options = [
            ['value' => '1', 'label' => Mage::helper('adminhtml')->__('PDF format for the Dymo 99012 LargeAddress labels')],
            ['value' => '2', 'label' => Mage::helper('adminhtml')->__('ZPL format for the Zebra printer with 150x100 mm labels')],
            ['value' => '3', 'label' => Mage::helper('adminhtml')->__('PDF format for a printer with 150x100 mm labels')],
        ];
        return $options;
    }
}
