<?php
    
    /**
     * Viewer class file.
     *
     
     */
    
    
    /**
     * Viewer
     *
     
     */
    class Viewer
    {
        
        /**
         * Renders model with specified view
         * @param AbstractModel $_model Data model to be rendered
         * @param string $_viewRef View reference in form of <module_name>.<view_name>
         * @param array $_params Additional optional parameters
         */
        static public function render($_model, $_viewRef, array $_params = array())
        {
            list ($moduleName, $viewName) = explode('.', $_viewRef);
            require(MODULES_DIR_PATH.$moduleName.'/views/'.$viewName.'.htm');
        }
        
        
        /**
         * Renders model with specified XML view
         * @param string        $_resolvedViewName View name in form of <module_name>.<view_name>
         * @param AbstractModel $_model            Data model to be rendered
         * @param array         $_params           Additional parameters
         */
        static public function renderXml($_resolvedViewName, $_model, array $_params = array())
        {
            list ($moduleName, $viewName) = explode('.', $_resolvedViewName);
            echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            require(DOCUMENT_ROOT.MODULES_PATH.$moduleName.'/'.VIEWS_PATH.$viewName.'.xml');
        }
        
        
        /**
         * Resolves URL
         * @param $_unresolvedUrl Unresolved URL
         * @return string Resolved URL
         */
        static public function resolveUrl($_unresolvedUrl)
        {
            //return HTTP_HOSTNAME.(MULTILINGUAL ? $router->getLang().'/' : '').$_unresolvedUrl;
            return HTTP_HOSTNAME.SITE_PATH.$_unresolvedUrl;
        }
        
        
        /**
         * Resolves style (css-file) URL
         * @param $_styleRef Style reference in form of <module_name>.<style_name>
         * @return string Resolved style (css-file) URL
         */
        static public function resolveStyleUrl($_styleRef)
        {
            list ($moduleName, $styleName) = explode('.', $_styleRef);
            return MODULES_DIR_URL.$moduleName.'/styles/'.$styleName.'.css';
        }
        
        
        /**
         * Resolves script (js-file) URL
         * @param $_scriptRef Script reference in form of <module_name>.<script_name>
         * @return string Resolved script (js-file) URL
         */
        static public function resolveScriptUrl($_scriptRef)
        {
            list ($moduleName, $scriptName) = explode('.', $_scriptRef);
            return MODULES_DIR_URL.$moduleName.'/scripts/'.$scriptName.'.js';
        }
        
        
        /**
         * Resolves plugin script (js-file) URL
         * @param $_scriptRef Script reference
         * @return string Resolved script (js-file) URL
         */
        static public function resolvePluginScriptUrl($_scriptRef)
        {
            list ($pluginName, $scriptName) = explode('.', $_scriptRef);
            return PLUGINS_DIR_URL.$pluginName.'/'.$scriptName.'.js';
        }
        
        
        /**
         * Resolves plugin style (css-file) URL
         * @param $_styleRef Style reference
         * @return string Resolved style (css-file) URL
         */
        static public function resolvePluginStyleUrl($_styleRef)
        {
            list ($pluginName, $styleName) = explode('.', $_styleRef);
            return PLUGINS_DIR_URL.$pluginName.'/'.$styleName.'.css';
        }
        
    }
     
     
?>