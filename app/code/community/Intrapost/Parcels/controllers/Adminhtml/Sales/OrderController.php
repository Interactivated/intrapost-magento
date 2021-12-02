<?php
require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'Sales' . DS . 'OrderController.php');

class Intrapost_Parcels_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    public function masscreateparcelsAction()
    {
        $request = $this->getRequest();
        $ids = $request->getParam('order_ids');
        $shipment_ids = $request->getParam('shipment_ids');
        if ($shipment_ids && !$ids){
            /** @var Mage_Sales_Model_Resource_Order_Shipment_Collection $collection */
            $collection = Mage::getModel('sales/order_shipment')->getCollection();
            $collection->addFieldToFilter('entity_id',['in'=>$shipment_ids]);
            $collection->load();
            /** @var  Mage_Sales_Model_Order_Shipment $shipment */
            foreach ($collection as $shipment){
                $ids[] = $shipment->getOrderId();
            }
        }
        $parcelType = $request->getParam('parceltype');
        $parcelIds = [];
        $storeId = null;
        $singleResultPdf = '';
        $format = 1;
        $parcelsMissed = [];
        $missedShipments = [];
        try {
            foreach ($ids as $id) {
                $parcel = Mage::getModel('intrapost/parcel');
                $parcel->load($id, 'order_id');
                $data = $parcel->getData('data');
                $data = json_decode($data, true);
                if (isset($data['ParcelID']) && $data['ParcelID']) {
                    $parcelIds[] = $data['ParcelID'];
                    $storeId = 0;
                    $singleResultPdf = $data['PdfLabelData'];
                    $format = (int)Mage::getStoreConfig('intrapost/general/label_format_type', $storeId);
                } else {
                    /** @var Mage_Sales_Model_Order $order */
                    $order = Mage::getModel('sales/order')->load($id);
                    if ($request->getParam('skipcreate') == '1') {
                        $parcelsMissed[] = '#' . $order->getIncrementId();
                        continue;
                    }
                    /** @var Mage_Sales_Model_Order_Shipment[] $shipments */
                    $shipments = $order->getShipmentsCollection();
                    if (!count($shipments)) {
                        $missedShipments[] = '#' . $order->getIncrementId();
                        continue;
                    }
                    $storeId = $order->getStoreId();
                    $format = (int)Mage::getStoreConfig('intrapost/general/label_format_type', $storeId);
                    $response = Mage::helper('intrapost')->createParcel($order, $parcelType);
                    $data = json_decode($response, true);
                    if (isset($data['ParcelID']) && $data['ParcelID']) {
                        $parcelIds[] = $data['ParcelID'];
                        $parcel->setData('order_id', $id);
                        $parcel->setData('data', $response);
                        $parcel->save();
                        $singleResultPdf = $data['PdfLabelData'];
                        if(isset($data['TrackTraceLink']) && $data['TrackTraceLink']){
                            foreach ($shipments as $shipment){
                                /** @var Mage_Sales_Model_Order_Shipment_Track $track */
                                $track = Mage::getModel('sales/order_shipment_track');
                                $track->setShipment($shipment);
                                $track->setNumber($data['TrackTraceLink']);
                                $track->setOrderId($id);
                                $track->setCarrierCode('custom');
                                $track->setTitle('intrapost');
                                $track->save();
                                $shipment->addTrack($track);
                            }
                        }
                    } else {
                        if (isset($data['Message']) && $data['Message']) {
                            throw new \Exception($data['Message']);
                        } else {
                            throw new \Exception('An error has occurred');
                        }
                    }
                }
            }
            if (count($parcelsMissed)) {
                throw new \Exception('Parcel does not exist for order(s) ' . implode(',', $parcelsMissed));
            }
            if (count($missedShipments)) {
                throw new \Exception('Shipment does not exist for order(s) ' . implode(',', $missedShipments));
            }
            $contentType = 'application/pdf';
            if ($format == '2') {
                $format = '.zpl';
                $contentType = 'application/zpl';
            } else {
                $format = '.pdf';
            }
            if (count($parcelIds) == 1 && $singleResultPdf) {
                $result = base64_decode($singleResultPdf);

                return $this->_prepareDownloadResponse(
                    'intrapost_parcel' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . $format,
                    $result,
                    $contentType
                );
            } elseif (count($parcelIds)) {
                $result = Mage::helper('intrapost')->getLabels11File($parcelIds, $storeId);
                return $this->_prepareDownloadResponse(
                    'intrapost_parcel' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . $format,
                    $result,
                    $contentType
                );
            } else {
                throw new \Exception('There are no available orders');
            }
        } catch (\Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_redirect('*/sales_order/index');
        }
    }
}