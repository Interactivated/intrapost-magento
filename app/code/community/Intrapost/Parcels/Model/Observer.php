<?php

class Intrapost_Parcels_Model_Observer
{
    public function massPrintLabels($observer)
    {
        $block = $observer->getBlock();
        $block_name = $block->getId();
        if ($block_name == 'sales_order_grid' || $block_name=='sales_shipment_grid') {
            $helper = Mage::helper('intrapost');
            $statuses = [
                0 => $helper->__('Default'),
                1 => $helper->__('StandardParcel'),
                2 => $helper->__('InsuredParcel'),
                3 => $helper->__('RegisteredParcel'),
                4 => $helper->__('InsuredLetter'),
                5 => $helper->__('RegisteredLetter'),
                6 => $helper->__('StandardParcelStatedAddress'),
                7 => $helper->__('StandardParcelStatedAddressSignature'),
                8 => $helper->__('MailboxParcel'),
            ];
            $block->getMassactionBlock()->addItem('intrapost_masscreateparcels', array(
                'label' => Mage::helper('sales')->__('Intrapost: Create Parcels and Print Labels'),
                'url' => $block->getUrl('*/sales_order/masscreateparcels'),
                'additional' => array(
                    'parceltype' => array(
                        'name' => 'parceltype',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper('catalog')->__('Parcel type'),
                        'values' => $statuses
                    )
                )
            ));
            $block->getMassactionBlock()->addItem('intrapost_massprintlabels', array(
                'label' => Mage::helper('sales')->__('Intrapost: Print Labels'),
                'url' => $block->getUrl('*/sales_order/masscreateparcels',array('skipcreate'=>'1'))
            ));
        }
    }
}
