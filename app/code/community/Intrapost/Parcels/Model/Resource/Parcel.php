<?php
class Intrapost_Parcels_Model_Resource_Parcel extends Mage_Core_Model_Resource_Db_Abstract
{

    public function _construct()
    {
        $this->_init('intrapost/parcel', 'entity_id');
    }
}