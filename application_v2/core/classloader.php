<?php

    /**
     * Class loader
     *
     
     */
    final class ClassLoader
    {
        
        /**
         * Loads core class
         * @param string $_className Class name to be loaded
         */
        public static function loadCoreClass($_className)
        {
            $filename = CORE_DIR_PATH.$_className.'.php';
            if (file_exists($filename))
                require_once($filename);
            else
                throw new Exception('Core class does not exists (\''.$filename.'\').');
        }
        
        
        /**
         * Loads builder class
         * @param string $_builderRef Builder reference in form of <module_key>.<builder_key>
         */
        public static function loadBuilderClass($_builderRef)
        {
            list($moduleKey, $builderKey) = explode('.', $_builderRef);
            $filename = MODULES_DIR_PATH.$moduleKey.'/builders/'.$builderKey.'.php';
            if (!file_exists($filename))
                throw new Exception('Failed to load builder class (\''.$_builderRef.'\').');
            require_once($filename);
        }
        
        
        /**
         * Loads service class
         * @param string $_serviceRef Service reference in form of <module_key>.<builder_key>
         */
        public static function loadServiceClass($_serviceRef)
        {
            list($moduleKey, $serviceKey) = explode('.', $_serviceRef);
            $filename = MODULES_DIR_PATH.$moduleKey.'/services/'.$serviceKey.'.php';
            if (!file_exists($filename))
                throw new Exception('Failed to load service class (\''.$_serviceRef.'\').');
            require_once($filename);
        }
        
        
        /**
         * Loads model class
         * @param string $_modelRef Model reference in form of <module_key>.<model_key>
         */
        public static function loadModelClass($_modelRef)
        {
            list($moduleKey, $modelKey) = explode('.', $_modelRef);
            $filename = MODULES_DIR_PATH.$moduleKey.'/models/'.$modelKey.'.php';
            if (!file_exists($filename))
                throw new Exception('Failed to load model class (\''.$_modelRef.'\').');
            require_once($filename);
        }
        
        
        /**
         * Loads async action class
         * @param string $_actionRef Action reference in form of <module_key>.<action_key>
         */
        public static function loadAsyncActionClass($_actionRef)
        {
            list($moduleKey, $actionKey) = explode('.', $_actionRef);
            $filename = MODULES_DIR_PATH.$moduleKey.'/async/'.$actionKey.'.php';
            if (!file_exists($filename))
                throw new Exception('Failed to load async action class (\''.$_actionRef.'\').');
            require_once($filename);
        }
        
    }

?>