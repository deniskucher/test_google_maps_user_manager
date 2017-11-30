<?php

    /**
     * Basic_Abstract_Service class file.
     *
     
     */
    
    
    /**
     * Service exception
     */
    class ServiceException extends Exception {}
    
    
    /**
     * Abstract service
     *
    
     */
    abstract class Basic_Abstract_Service
    {
        /**
         * Returns current date and time as string
         */
        public function dateNow()
        {
            return date('Y-m-d H:i:s');
        }
    }

?>