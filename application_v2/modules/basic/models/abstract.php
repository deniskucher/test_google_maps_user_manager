<?php

    /**
     * Basic_Abstract_Model class file.
     
     */
    
    
    /**
     * Abstract model
     *
    
     */
    abstract class Basic_Abstract_Model
    {
    
        /**
         * Constructor
         * @param array $_params Parameters
         */
        public function __construct(array $_params = array())
        {
            foreach ($_params as $key => $value)
            {
                if (!property_exists($this, $key))
                    throw new Exception('Model property \''.get_class($this).'::'.$key.'\' does not exists.');
                $this->$key = $value;
            }
        }
    
    }

?>