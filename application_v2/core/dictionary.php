<?php

    /**
     * Dictionary
     *
     
     */
    final class Dictionary
    {
    
        /**
         * Instance holder
         * @var Dictionary
         */
        private static $instance = null;
        
        
        /**
         * Words container
         * @var array
         */
        private $words;
        
    
        /**
         * Prevent direct object creation
         */
        private function __construct()
        {
            $this->words = array();
        }
        
        
        /**
         * Prevent to clone the instance
         */
        private function __clone() {}
        
        
        /**
         * Instance creator/accessor
         */
        public function getInstance()
        {
            if (self::$instance === null)
                self::$instance = new Dictionary();
            return self::$instance;
        }
        
        
        /**
         * Loads words
         */
        public function loadWords($_dicFilename)
        {
            // Compose dictionary file name
            if (!file_exists($_dicFilename))
                throw new Exception('Failed to load dictionary file.');
            
            // Parse dictionary file
            $this->words = parse_ini_file($_dicFilename);
        }
        
        
        /**
         * Acsesse the word by key
         * @param string Key
         */
        public function getWord($_key)
        {
            return $this->words[$_key];
        }
    
    }
     
?>