<?php
 
    // Load abstract action
    ClassLoader::loadAsyncActionClass('basic.abstract');
    

    class Googlemaps_GetUser_AsyncAction extends Basic_Abstract_AsyncAction
    {
        
        /**
         * Performs the action
         */
        public function perform($_domainId = null, array $_params = array())
        {
            $mySql = Application::getService('basic.mysqlmanager');
			// Extract inputs
            $id = $this->_getString('id', $_params, false);
            
            $user = $mySql->getRecord('google_maps_users', array('id' => $id));
            
            $this->data['user'] = $user;
            
        }
    }
        

?>