<?php

    /**
     * Basic_HtmlResponse_Model class file.
     *
    
     */
    
    
    // Load abstract HTML document model class
    ClassLoader::loadModelClass('basic.abstract');
    
    
    /**
     * Response model
     *
    
     */
    class Basic_HtmlResponse_Model extends Basic_Abstract_Model
    {

        /**
         * @var integer HTTP status code
         */
        public $statusCode = 200;
        
        
        /**
         * @var string Reason message
         */
        public $reasonMessage = 'OK';
        
        
        /**
         * @var string Data type
         */
        public $dataType = 'html';
        
        
        /**
         * @var string Location header value
         */
        public $location = null;
        
        
        /**
         * @var string Message body data format (e.g. json could be passed withing html body)
         */
        public $messageBodyFormat = 'html';
        
        
        /**
         * @var mixed Message body
         */
        public $messageBody = null;

    }

?>