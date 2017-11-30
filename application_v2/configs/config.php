<?php

    define('DEBUG', true);
    define('LF', "\n");
    
    define('MAIN_DOMAIN', 'googlemaps.local');
    define('HTTP_HOSTNAME', ((isset($_SERVER['HTTPS']) and $_SERVER['HTTPS']=='on')?'https':'http').'://'.$_SERVER['HTTP_HOST'].'/');
    define('SITE_PATH', substr($_SERVER['SCRIPT_NAME'], 1, strrpos($_SERVER['SCRIPT_NAME'], '/')));
    define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'].'/'.SITE_PATH);

    define('APP_DIR_PATH', DOCUMENT_ROOT.APP_DIR_NAME.'/');
    define('CORE_DIR_PATH', APP_DIR_PATH.'core/');
    define('MODULES_DIR_PATH', APP_DIR_PATH.'modules/');
    define('PLUGINS_DIR_PATH', DOCUMENT_ROOT.'plugins/');
    define('LIBS_DIR_PATH', DOCUMENT_ROOT.'libs/');
    define('LOGS_DIR_PATH', DOCUMENT_ROOT.'logs/');
    define('TMP_DIR_PATH', DOCUMENT_ROOT.'tmp/');
    define('UPLOADS_DIR_PATH', DOCUMENT_ROOT.'uploads/');
    define('FFMPEG_DIR', '/usr/local/bin/ffmpeg');
    
    define('APP_DIR_URL', HTTP_HOSTNAME.SITE_PATH.APP_DIR_NAME.'/');
    define('MODULES_DIR_URL', APP_DIR_URL.'modules/');
    define('PLUGINS_DIR_URL', APP_DIR_URL.'plugins/');
    define('ADMIN_PANEL_URL', APP_DIR_URL.'backend/');
    define('LOGS_DIR_URL', APP_DIR_URL.'logs/');
    define('UPLOADS_DIR_URL', HTTP_HOSTNAME.SITE_PATH.'uploads/');
    
    define('MYSQL_HOSTNAME', 'localhost');
    define('MYSQL_USERNAME', 'horses');
    define('MYSQL_PASSWORD', 'Hqae1?87');
    define('MYSQL_DBNAME', 'horses');
    define('MYSQL_TIMEZONE', '+2:00');
    
    define('MULTILINGUAL', true);
    define('ML', MULTILINGUAL);
    define('DEFAULT_LANG', 'en');
    define('DICS_PATH', 'dics/');
    
    // Regular expressions
    define('RE_EMAIL', '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i');
    define('RE_DATE', '/^\d{4}-\d{2}-\d{2}$/');

    define('SALT', '6347fujdkjfgjdr879457848');
    define('STATUS_ACTIVE', 'active');
    define('STATUS_NEW', 'new');
    define('STATUS_UNCONFIRMED', 'unconfirmed');
    define('REGISTRATIONS_PER_DAY', 4);
    
    define('VIDEOS_V2_PATH', '/uploads/videos_v2/');
    define('PHOTOS_ORIGINAL_PATH', 'uploads/photos/original/');
    define('PHOTOS_THUMBS_PATH', 'uploads/photos/thumbs/');
    define('PHOTOS_LARGE_PATH', 'uploads/photos/large/');
    define('PHOTOS_MEDIUM_PATH', 'uploads/photos/medium/');

    
?>