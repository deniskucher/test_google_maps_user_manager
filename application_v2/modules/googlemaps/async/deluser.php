<?php
 
    // Load abstract action
    ClassLoader::loadAsyncActionClass('basic.abstract');
    

    class Googlemaps_DelUser_AsyncAction extends Basic_Abstract_AsyncAction
    {
        
        /**
         * Performs the action
         */
        public function perform($_domainId = null, array $_params = array())
        {
            $mySql = Application::getService('basic.mysqlmanager');
			// Extract inputs
            $id = $this->_getString('id', $_params, true);
            $mySql->delete('google_maps_users', array('id'=>$id));
            
            $this->data['success'] = true;
            
        }
    }
        

?>