<?php
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('intrapost/parcel'))
    ->addColumn('entry_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true), 'Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array('unsigned' => true, 'nullable' => false), 'Order ID')
    ->addColumn('data', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(), 'Parcel Data')
    ->addIndex($installer->getIdxName('intrapost/parcel', array('order_id')), array('order_id'));
$installer->getConnection()->createTable($table);
$installer->endSetup();
