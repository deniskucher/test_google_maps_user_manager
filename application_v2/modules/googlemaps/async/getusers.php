<?php
 
    // Load abstract action
    ClassLoader::loadAsyncActionClass('basic.abstract');
    

    class Googlemaps_GetUsers_AsyncAction extends Basic_Abstract_AsyncAction
    {
        
        /**
         * Performs the action
         */
        public function perform($_domainId = null, array $_params = array())
        {
            $mySql = Application::getService('basic.mysqlmanager');
			// Extract inputs
            $usersSort = array();
            $users = $mySql->getRecords('google_maps_users', '`id`');
            // var_dump($users);
            foreach ($users as $key => $value) {
                $usersSort[$value['id']] = $value;
            }
            
            $this->data['users'] = $users;
            
        }
    }
        

?>