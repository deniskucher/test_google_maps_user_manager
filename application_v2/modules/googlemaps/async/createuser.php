<?php
 
    // Load abstract action
    ClassLoader::loadAsyncActionClass('basic.abstract');
    

    class Googlemaps_CreateUser_AsyncAction extends Basic_Abstract_AsyncAction
    {
        
        /**
         * Performs the action
         */
        public function perform($_domainId = null, array $_params = array())
        {
            $mySql = Application::getService('basic.mysqlmanager');
			// Extract inputs
            $errorFlag = false;
            $errormessagearray = array();

            $data = $this->_getArray('data', $_params, true);
            $nameStr = $data['name'];
            $nameStr = trim($nameStr);
            $nameStr = preg_replace("/\s{2,}/"," ",$nameStr);
            $nameArr = preg_split('/ /', $nameStr);
            foreach ($nameArr as $key => $name) {
                $nameValid = preg_match('/^[A-Za-zА-Яа-я]+$/msiu', $name);
                if (!$nameValid) {
                    $errorFlag = true;
                    $errormessagearray['name'] = 'Vvedite korrektno name!';
                }
            }
            $addressStr = trim($data['address']);
            $addressStr = str_replace(',', ' ', $addressStr);
            $addressStr = preg_replace("/\s{2,}/"," ",$addressStr);
            
            $addressArr = preg_split('/ /', $addressStr);
            $addressArrLenght = count($addressArr);
            $index = $addressArr[0];
            $country = $addressArr[1];
            // var_dump($country);exit();
            $city = $addressArr[2];
            foreach ($addressArr as $key2 => $value2) {
                if (($key2>2) and ($key2<$addressArrLenght-1)) {
                    $street.= ' '.$value2;
                }
            }
            $street = trim($street);
            $house = $addressArr[$addressArrLenght-1];
            $indexValid = preg_match("/[0-9]{5}/i", $index);
            if (!$indexValid) {
                $errorFlag = true;
                $errormessagearray['address'] = 'Vvedite korrektno index!';
            }
            $countryValid = preg_match('/^[A-Za-zА-Яа-я]+$/msiu',$country);
            if (!$countryValid) {
                $errorFlag = true;
                $errormessagearray['address'] = 'Vvedite korrektno country!';
            }
            $cityValid = preg_match('/^[A-Za-zА-Яа-я]+$/msiu', $city);
            if (!$cityValid) {
                $errorFlag = true;
                $errormessagearray['address'] = 'Vvedite korrektno city!';
            }

            $stretValid = preg_match('/^[A-Za-zА-ЯЁа-яё0-9\s]+/msi', $street);
            if (!$stretValid) {
                $errorFlag = true;
                $errormessagearray['address'] = 'Vvedite korrektno street!';
            }
            $street_geo = str_replace(' ', '+', $street);
            if(preg_match('/\//i', $house)) {
                $houseArr = explode("/", $house);
                foreach ($houseArr as $key => $value) {
                    $houseValid = preg_match("/^[a-zа-яё\d]{1}[a-zа-яё\d\s]*[a-zа-яё\d]{1}$/i", $value);
                    if (!$houseValid) {
                        $errorFlag = true;
                        $errormessagearray['address'] = 'Vvedite korrektno house!';
                    }
                }
            }else{
                $houseValid = preg_match('|^[A-Z0-9]+$|i', $house);
                if (!$houseValid) {
                    $errorFlag = true;
                    $errormessagearray['address'] = 'Vvedite korrektno house!';
                }
            }
            $address_geo = $country.'+'.$city.'+'.$street_geo.'+'.$house;
            
            $user_api_key = 'AIzaSyA8KiLQGppjeqsIMhRq6vMP_weYdQiOd6M'; //GOOGLE API KEY
            $xml = simplexml_load_file('http://maps.google.com/maps/api/geocode/xml?address='.$address_geo.'&sensor=false');
            $json_string = json_encode($xml);    
            $result_array = json_decode($json_string, TRUE);
            $status = $xml->status;
            // print_r($xml);
            if ($status == 'OK') {
                $lat = $result_array['result']['geometry']['location']['lat'];
                $lng = $result_array['result']['geometry']['location']['lng'];
                
                $data['lat'] = $lat;
                $data['lng'] = $lng;
                
            }else{
                $errorFlag = true;
                $errormessagearray['address'] = 'Can`t get coordinats! Iput correct address!';
            }
            
            $json = json_encode($errormessagearray);
            if ($errorFlag) {
                $errorFlag = false;
                throw new AsyncActionException($json);
            }
            
            $mySql->insert('google_maps_users', array('name'=>$nameStr,'index'=>$index, 'country'=>$country, 'city'=>$city,
                'street'=>$street,'house'=>$house, 'lat'=>$lat,'lng'=>$lng));
            
            $userid = $mySql->getInsertId();
            
            $user = $mySql->getRecord('google_maps_users', array('id' => $userid));
            
            $this->data['user'] = $user;
            
        }
    }
        

?>