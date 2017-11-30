<?php

    error_reporting(E_ALL^E_NOTICE);
    ini_set('display_errors', 1);
    
    // Load config
    define('APP_DIR_NAME', 'application_v2');
    require_once(APP_DIR_NAME.'/configs/config.php');
    
    // Load class loader
    require_once(CORE_DIR_PATH.'classloader.php');
    
    // Load application
    ClassLoader::loadCoreClass('application');
    
    // Run the application
    Application::run();
    
?>