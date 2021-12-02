<?php

class Intrapost_Parcels_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param $order Mage_Sales_Model_Order
     */
    public function createParcel($order, $parcelType)
    {
        $shippingAddress = $order->getShippingAddress();
        $housenumber = '';
        $street = $shippingAddress->getStreet1();
        $addition = '';
        foreach ([1, 2, 3, 4] as $i) {
            $method = 'getStreet' . $i;
            $value = $shippingAddress->$method();
            if ($value) {
                if (is_numeric($value)) {
                    $housenumber = $value;
                } else {
                    if ($i > 1) {
                        $addition .= $value . ' ';
                    }
                }
            }
        }
        if (!$housenumber) {
            $matches = [];
            preg_match_all('/(\d+)/', $street, $matches);
            if (count($matches) && isset($matches[0]) && isset($matches[0][0])) {
                $housenumber = $matches[0][0];
            }
            if (!$housenumber) {
                $matches = [];
                preg_match_all('/(\d+)/', $shippingAddress->getStreetFull(), $matches);
                if (count($matches) && isset($matches[0]) && isset($matches[0][0])) {
                    $housenumber = $matches[0][0];
                }
                if (!$housenumber) {
                    $housenumber = 99999;
                }
            }
        }
        if (strlen($addition) > 15) {
            $addition = substr($addition, 0, 15);
        }
        $addition = preg_replace('/(\d+)/','',$addition);
        $addition = trim($addition);

        $street = str_replace($housenumber,'', $street);
        $address = [
            "Street" => $street,
            "Number" => (int)$housenumber,
            "Addition" => $addition,
            "Zipcode" => $shippingAddress->getPostcode(),
            "City" => $shippingAddress->getCity(),
            "CountryCode" => $shippingAddress->getCountryId()
        ];
        $company = $shippingAddress->getCompany();
        if ($parcelType == '0') {
            if ($shippingAddress->getCountryId() == 'NL') {
                $parcelType = 8;//MailboxParcel
            } else {
                $parcelType = 1;//StandardParcel
            }
        }
        $storeId = $order->getStoreId();

        $request = [
            "ApiKey" => Mage::getStoreConfig('intrapost/general/api_key', $storeId),
            "AccountNumber" => (int)Mage::getStoreConfig('intrapost/general/accountnumber', $storeId),
            "Address" => $address,
            "ParcelType" => (int)$parcelType,
            "ContactPerson" => $shippingAddress->getName(),
            "Company" => $company,
            "Phone" => $shippingAddress->getTelephone(),
            "Email" => $shippingAddress->getEmail(),
            "Reference" => $order->getIncrementId(),
            //"OrderReference" => $order->getIncrementId(),
            "SenderAddress" => [
                "Street" => Mage::getStoreConfig('intrapost/sender_details/street', $storeId),
                "Number" => (int)Mage::getStoreConfig('intrapost/sender_details/number', $storeId),
                "Addition" => Mage::getStoreConfig('intrapost/sender_details/addition', $storeId),
                "Zipcode" => Mage::getStoreConfig('intrapost/sender_details/zipcode', $storeId),
                "City" => Mage::getStoreConfig('intrapost/sender_details/city', $storeId),
                "CountryCode" => Mage::getStoreConfig('intrapost/sender_details/country_id', $storeId),
            ],
            "SenderCompany" => Mage::getStoreConfig('intrapost/sender_details/sender_company', $storeId),
            "LabelFormatType" => (int)Mage::getStoreConfig('intrapost/general/label_format_type', $storeId),
            "SendMailToRecipient" => (bool)Mage::getStoreConfig('intrapost/general/mailtrackandtrace', $storeId),
        ];
        if ($order->getData('channel_name')=='bol' && $order->getData('channel_id')){
            $request["BolComOrderId"] = $order->getData('channel_id');
        }

        $url = 'https://api.intrapost.nl/parcel/create_1_1';
        try {
            // create a new cURL resource
            $curl = curl_init();

            $payload = json_encode($request);
            // set URL and other appropriate options
            curl_setopt($curl, CURLOPT_URL, $url);

            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($curl, CURLOPT_SSLVERSION, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            // grab URL and pass it to the browser
            $response = curl_exec($curl);
            return $response;
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'intrapost.log');
            throw $e;
        }
    }

    public function getLabels11($parcelIds, $storeId)
    {
        $request = [
            "ApiKey" => Mage::getStoreConfig('intrapost/general/api_key', $storeId),
            "ParcelIDs" => $parcelIds,
            "LabelFormatType" => (int)Mage::getStoreConfig('intrapost/general/label_format_type', $storeId),
        ];
        $url = 'https://api.intrapost.nl/parcel/get-labels_1_1';
        try {
            // create a new cURL resource
            $curl = curl_init();

            $payload = json_encode($request);
            // set URL and other appropriate options
            curl_setopt($curl, CURLOPT_URL, $url);

            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($curl, CURLOPT_SSLVERSION, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 10);
            // grab URL and pass it to the browser
            $response = curl_exec($curl);

            return $response;
        } catch (Exception $e) {
            Mage::log($e->getMessage(), null, 'intrapost.log');
            throw $e;
        }
    }

    public function getLabels11File($parcelIds, $storeId)
    {
        $filename = implode(',', $parcelIds);
        $format = (int)Mage::getStoreConfig('intrapost/general/label_format_type', $storeId);
        $variable = 'PdfLabelData';
        if ($format == '2') {
            $format = '.zpl';
            $variable = 'ZplLabelText';
        } else {
            $format = '.pdf';
        }
        $filename = hash_hmac('sha256', $filename, 'intrapost') . $format;
        $io = new Varien_Io_File();
        if (!$io->fileExists('media/intrapost/' . $filename)) {
            $response = $this->getLabels11($parcelIds, $storeId);
            $result = json_decode($response, true);
            if (isset($result[$variable])) {
                $decoded = base64_decode($result[$variable]);
                if (!$io->fileExists('media/intrapost', false)) {
                    $io->mkdir('media/intrapost');
                }
                $io->write('media/intrapost/' . $filename, $decoded);
                return $decoded;
            }
            throw new \Exception('An error has occurred');
        } else {
            $data = $io->read('media/intrapost/' . $filename);
            return $data;
        }
    }
}
