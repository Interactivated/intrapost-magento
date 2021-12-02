<?php

class Intrapost_Parcels_Model_Parcel extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('intrapost/parcel');
    }
}