<?php

    /**
     * Basic_Abstract_Builder class file.
     *
     
     */
    
    
    /**
     * Abstract builder
    
     */
    abstract class Basic_Abstract_Builder
    {
        
        /**
         * General build routine
         * @param array $_params Array of parameters
         * @return AbstractModel Built data model
         */
        public function get(array $_params = array())
        {
            $modelClassName = str_replace('_Builder', '_Model', get_class($this));
            return new $modelClassName($_params);
        }
        
        
        public function getByRequest(Request $_request = null)
        {
            $modelClassName = str_replace('_Builder', '_Model', get_class($this));
            return new $modelClassName(array());
        }
        
        
        /**
         * Debug purpose
         */
        protected function _varDumpExit($_arg)
        {
            echo '<pre>'; var_dump($_arg); exit();
        }
        
        
        /**
         */
        protected function _simpleXmlLoadFileRetriable($_filename)
        {
            $tries = 1;
            while (!($xmlObject = @simplexml_load_file($_filename))  and  (XML_LOAD_MAX_TRIES_NUM==0 or $tries++ < XML_LOAD_MAX_TRIES_NUM))
                sleep(XML_LOAD_DELAY);
            return $xmlObject;
        }
        
    }

?>