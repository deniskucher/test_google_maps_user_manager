<?php

    /**
     * Application class file.
     *
    
     */
    
    
    /**
     * Request
     *
    
     */
    class Request
    {
    
        /**
         * @var string Hostname
         */
        private $hostname = null;
        
        
        /**
         * @var integer Domain ID
         */
        private $domainId = null;
    
        
        /**
         * @var string URL
         */
        private $url = null;
        
        
        /**
         * $var string Method ('GET' or 'POST')
         */
        private $method;
        
        
        /**
         * @var string Referrer
         */
        private $referrer;
        
        
        /**
         * @var array Parameters
         */
        private $params = array();
        
        
        /**
         * Constructor
         */
        public function __construct($_server, $_request)
        {
            $this->hostname = $_server['HTTP_HOST'];
            
            
            // Resolve domain ID
            $mySqlManager = Application::getService('basic.mysqlmanager');
            if (substr($this->hostname, strlen($this->hostname)-strlen(MAIN_DOMAIN)) == MAIN_DOMAIN)
            {
                $subDomainName = substr($this->hostname, 0, strpos($this->hostname, '.'));
                $this->domainId = (int) $mySqlManager->select('id', 'domains', array('name' => $subDomainName))->fetchCellValue('id');
            }
            else
                $this->domainId = (int) $mySqlManager->select('id', 'domains', array('domain' => $this->hostname))->fetchCellValue('id');
            
            
            $this->url = ($url = substr($_server['REQUEST_URI'], 1)) ? $url : '';
            $this->method = $_server['REQUEST_METHOD'];
            $this->params = $_request;
            $this->referrer = array_key_exists('HTTP_REFERER', $_server) ? $_server['HTTP_REFERER'] : null;
        }
        
        
        /**
         * Acceses the Hostname
         * @return string Hostname
         */
        public function getHostname() { return $this->hostname; }
        
        
        /**
         * Accesses the Domain ID
         * @return integer Domain ID
         */
        public function getDomainId() { return $this->domainId; }
        
        
        /**
         * Accesses the URL
         * @param boolean Indicates if URL params should be included
         * @return string request URL
         */
        public function getUrl($_truncateUrlParams = true)
        {
            if ($_truncateUrlParams)
            {
                $p = strpos($this->url, '?');
                if ($p === false)
                    return $this->url;
                else
                    return substr($this->url, 0, $p);
            }
            else
                return $this->url;
        }
        
    
        /**
         * Accesses the query string
         */
        public function getQueryString()
        {
            return $_SERVER['QUERY_STRING'];
        }
        
    
        /**
         * Accesses the URL token on specified position
         * @param integer $position URL token position
         * @return string URL token if $_position valid, empty string otherwise
         */
        public function getUrlToken($_position)
        {
            $tokens = explode('/', $this->url);
            return array_key_exists($_position, $tokens) ? $tokens[$_position] : '';
        }
        
        
        /**
         * Accesses the method
         * @return string Request method
         */
        public function getMethod() { return $this->method; }
        
        
        /**
         * Accesses the referrer
         * @return string Referrer
         */
        public function getReferrer()
        {
            return $this->referrer;
        }
        
        
        /**
         * Tells if parameter with specified key exists
         * @param string $_key Parameter key
         * @return boolean True if parameter exists, false otherwise
         */
        public function paramExists($_key)
        {
            return array_key_exists($_key, $this->params);
        }
        
        
        /**
         * Accesses the parameter value
         * @param string $_key Parameter key
         * @return string Parameter value
         */
        public function getParamValue($_key)
        {
            return $this->params[$_key];
        }
        
        
        /**
         * Accesses the parameter value by key if it exists
         * @param string $_key Parameter key
         * @param mixed $_defaultValue Default parameter value
         * @return mixed Parameter value if it exists, $_defaultValue otherwise
         */
        public function getParamValueIfExists($_key, $_defaultValue = '')
        {
            return $this->paramExists($_key) ? $this->getParamValue($_key) : $_defaultValue;
        }
        
        
        /**
         * Accesses the params
         */
        public function getParams() { return $this->params; }
    
    }

?>