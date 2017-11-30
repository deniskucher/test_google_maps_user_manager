<?php

    /**
     * Application class file.
     
     */
    
    
    // Load core classes
    ClassLoader::loadCoreClass('request');
    ClassLoader::loadCoreClass('viewer');
    
    
    /**
     * Application
     *
     */
    class Application
    {
    
        /**
         * @var array Builders collection
         */
        private static $builders = array();
        
        
        /**
         * @var array Services collection
         */
        private static $services = array();
        
        
        /**
         * @var array User (null stand for guest)
         */
        // private static $user = null;
        
        
        /**
         * @var Dictionary Dictionary
         */
        // private static $dic = null;
        
        
        /**
         * @var array Languages set (for ML mode only)
         */
        // private static $langs = array('ru' => 'Русский', 'en' => 'English');
        
        
        /**
         * @var string Current language
         */
        // private static $lang = DEFAULT_LANG;
        
        
        /**
         * @var array Application settings
         */
        // private static $settings = array();
        
        
        /**
         * @var array Action privileges
         */
        // private static $actionPrivileges = array
        // (
            // 'login' => array('allow' => 0),
            // 'logout' => array('allow' => 0),
            // 'create' => array('allow' => array(UR_ADMIN)),
            // 'update' => array('allow' => array(UR_ADMIN)),
            // 'delete' => array('allow' => array(UR_ADMIN)),
            // 'moveup' => array('allow' => array(UR_ADMIN)),
            // 'movedown' => array('allow' => array(UR_ADMIN)),
            // 'delete' => array('allow' => array(UR_ADMIN)),
            // 'password' => array('allow' => array(UR_ADMIN)),
        // );

        
        /**
         * Prevent direct object creation
         */
        private function __construct() {}
        
        
        /**
         * Prevent to clone the instance
         */
        private function __clone() {}
        
        
        /**
         * Application runner
         */
        public static function run()
        {
            try
            {
                // Start a session
                session_start();
                
                
                // Init MySQL service
                $mySqlManager = Application::getService('basic.mysqlmanager');
                $mySqlManager->connect(MYSQL_HOSTNAME, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DBNAME);
                
                
                // Create request object
                $request = new Request($_SERVER, $_REQUEST);
                
                
                // Build HTML response
                $response = Application::getBuilder('basic.httpresponse')->getByRequest($request);
                
                
                // Send status line
                header('HTTP/1.1 '.$response->statusCode);
                
                
                // -- Send headers --
                
                // Send 'Content-Type' header
                if ($response->dataType == 'html') header('Content-Type: text/html; charset=utf-8');
                elseif ($response->dataType == 'json') header('Content-Type: application/json; charset=utf-8');
                // elseif ($response->dataType == 'xml') header('Content-Type: application/xml; charset=utf-8');
                else header('Content-Type: text/html; charset=utf-8'); // default

                // Send 'Location' header if any
                if (!is_null($response->location)) header('Location: '.$response->location);
                    
                
                // Send message body
                if ($response->messageBodyFormat == 'html')
                    Viewer::render($response->messageBody, 'main.htmldocument');
                elseif ($response->messageBodyFormat == 'json') echo json_encode($response->messageBody);
                // elseif ($response->messageBodyFormat == 'xml')
                    // Viewer::renderXml($response->view, $response->model);
                else // default
                    Viewer::render($response->messageBody->view, $response->messageBody->model);
            }
            
            catch (Exception $e)
            {
                echo '<h1>Exception</h1>';
                echo '<p>'.$e->getMessage().'</p>';
                echo '<pre>'.$e->getTraceAsString().'</pre>';
                exit();
                // $response->statusCode = 500;
                // $response->dataType = 'html';
                // $response->model = GeneratorsFactory::getGenerator('basic.exceptionhtmldocument')->get(array('exception' => $e));
                // $response->view = 'basic.exceptionhtmldocument';
            }
        }


        /**
         * Accesses the builder
         * @var sting $_builderRef Builder reference in form of <module_key>.<builder_key>
         * @return AbstractBuilder Builder object
         */
        public static function getBuilder($_builderRef)
        {
            if (!array_key_exists($_builderRef, self::$builders))
            {
                ClassLoader::loadBuilderClass($_builderRef);
                list($moduleKey, $builderKey) = explode('.', $_builderRef);
                $builderClassName = $moduleKey.'_'.$builderKey.'_builder';
                self::$builders[$_builderRef] = new $builderClassName();
            }
            return self::$builders[$_builderRef];
        }


        /**
         * Accesses the service
         * @var sting $_serviceRef Service reference in form of <module_key>.<builder_key>
         * @return AbstractService Service object
         */
        public static function getService($_serviceRef)
        {
            if (!array_key_exists($_serviceRef, self::$services))
            {
                ClassLoader::loadServiceClass($_serviceRef);
                list($moduleKey, $serviceKey) = explode('.', $_serviceRef);
                $serviceClassName = $moduleKey.'_'.$serviceKey.'_service';
                self::$services[$_serviceRef] = new $serviceClassName();
            }
            return self::$services[$_serviceRef];
        }
        
        
        /**
         * Accesses the application user
         */
        // public static function getUser()
        // {
            // $user = null;
            // if (isset($_SESSION['user']))
            // {
                // $mySqlManager = self::getService('basic.mysqlmanager');
                // $user = $mySqlManager->select('*', 'users', array('id' => $_SESSION['user']['id']))->fetchAssoc();
            // }
            // return $user;
        // }
        
        
        /**
         * Tells if action is allowed for the specific user
         * @param string $actionKey Action key
         * @param integer $userRoleId User role ID or NULL (for non-authorized)
         */
        // public static function isActionAllowed($_actionKey, $_userRoleId)
        // {
            // if (array_key_exists($_actionKey, self::$actionPrivileges))
            // {
                // foreach (array_reverse(self::$actionPrivileges[$_actionKey]) as $accessType => $userRoleIds)
                // {
                    // if (is_array($userRoleIds) ? in_array($_userRoleId, $userRoleIds) : ($userRoleIds == 0  or  $_userRoleId == $userRoleIds))
                        // return $accessType == 'allow';
                // }
            // }
            // return false;
        // }
    
    }

?>