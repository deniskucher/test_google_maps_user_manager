<?php

    
    abstract class Basic_Abstract_AsyncAction
    {
    
        /**
         * @var array Data
         */
        protected $data = array();
        
        
        /**
         * @var array Errors
         */
        protected $errors = array();
        
    
        /**
         * @var string Redirect URL
         */
        protected $redirectUrl = null;
        
    
        /**
         * Performs the action
         * @param Request Request object
         */
        abstract public function perform($_domainId = null, array $_params = array());
        
        
        /**
         * Accesses the data
         * @return array Data
         */
        public function getData() { return $this->data; }
        
        
        /**
         * Accesses the errors
         * @return array Errors
         */
        public function getErrors() { return $this->errors; }
        
        
        /**
         * Accesses the redirect URL
         * @return string Redirect URL
         */
        public function getRedirectUrl() { return $this->redirectUrl; }
        
        
        /**
         */
        protected function getFromArray($_key, $_array, $_default = null)
        {
            return isset($_array[$_key]) ? $_array[$_key] : $_default;
        }
        
        
        /**
         * Sends mail
         */
        public function mail($_to, $_subject, $_message, $_headers = null)
        {
            if (MAIL) mail($_to, $_subject, $_message, $_headers);
            if (LOG_MAIL_TO_FILE) $this->logMail($_to, $_subject, $_message, $_headers);
        }
        
        
        /**
         * Logs mail data to a file
         */
        public function logMail($_to, $_subject, $_message, $_headers, $_attachment = array())
        {
            $logFilename = 'logs/mail.log';
            $logFile = fopen($logFilename, 'ab');
            fwrite($logFile, "\r\n".date('Y-m-d H:i:s')."\r\n"."\r\n");
            fwrite($logFile, 'Headers: '.$_headers."\r\n");
            if (is_array($_to))
                fwrite($logFile, 'To: '.implode(',', $_to)."\r\n");
            else
                fwrite($logFile, 'To: '.$_to."\r\n");
            fwrite($logFile, 'Subject: '.$_subject."\r\n");
            if (count($_attachment)) fwrite($logFile, 'Attachment: \''.implode('\', \'', $_attachment).'\''."\r\n");
            fwrite($logFile, 'Message: '."\r\n".$_message."\r\n"."\r\n");
            fclose($logFile);
        }
        
        
        /**
         * Add tags around all hyperlinks in $string
         */
        public function plainToHtmlHyperlinks($_string)
        {
            // $result = preg_replace(
                // "/(?<![\>https?:\/\/|href=\"'])(?<http>(https?:[\/][\/]|www\.)([a-z]|[A-Z]|[0-9]|[\-\/.&?=_]|[~])*)/",
                // "<a href=\"$1\" target=\"_blank\">$1</a>",
                // $_string
            // );
            $result = preg_replace(
                "/(?<![\>https?:\/\/|href=\"'])(?<http>(https?:[\/][\/]|www\.)([a-z]|[A-Z]|[0-9]|[\-\/.&?=_]|[~])*)/",
                "$1",
                $_string
            );
            if (substr($result, 0, 3) == 'www')
            {
                $result = 'http://'.$result;
                $result = "<a href=\"$result\" target=\"_blank\">$result</a>";
            }
            return $result;
        }

        
        /**
         * Add tags around all email addresses in $string
         */
        public function plainToHtmlEmails($_string)
        {
            return preg_replace(
                "/([^@\s]*@[^@\s]*\.[^@\s]*)/",
                "<a href=\"mailto:$1\">$1</a>",
                $_string
            );
        }

        
        /**
         * Replace all line feeds with HTML '<br />' tag
         */
        public function plainToHtmlLineFeeds($_string)
        {
            $tmp = $_string;
            $tmp = str_replace("\r\n", '<br />', $tmp);
            $tmp = str_replace("\n", '<br />', $tmp);
            $tmp = str_replace("\r", '<br />', $tmp);
            return $tmp;
        }    
        
        
        /**
         * Removes all spaces and replaces them with the specified delimiter
         */
        public function removeSpaces($_emails, $_delimiter = ',')
        {
            $emails = str_replace(array("\r\n", "\n", "\r", "\t"), ' ', $_emails);
            do { $emails = str_replace('  ', ' ', $emails, $c); } while ($c);
            $emails = trim($emails);
            $emails = str_replace(' ', $_delimiter, $emails);
            return $emails;
        }
        
        
        /**
         * Returns current datetime
         */
        public function dateNow()
        {
            return date('Y-m-d H:i:s');
        }
        
        
        /**
         * Exports data to the xls-file
         */
        protected function exportToXls($_exportFilename, $_fields, $_records = array())
        {
            // Create PHPExcel object
            require_once(LIBS_DIR_PATH.'PHPExcel-1.7.9/PHPExcel.php');
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator('VOS Backend Application');
            $activeSheet = $objPHPExcel->setActiveSheetIndex(0);


            // Put fields
            for ($i = 0, $col = 'A'; $i < count($_fields); ++$i, ++$col)
                $activeSheet->setCellValue($col.'1', $_fields[$i]);
             
            
            // Put records
            $row = 1;
            foreach ($_records as $record)
            {
                $row += 1;
                $col = 'A';
                foreach ($record as $cell)
                    $activeSheet->setCellValue($col++.$row, $cell);
            }


            // Dump data
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save($_exportFilename);
        }
        
        
        /**
         * Exports data as xls-file to a browser
         */
        protected function exportAsXlsToBrowser($_exportFilename, $_fields, $_records = array())
        {
            set_time_limit(0);
            ini_set('memory_limit', '512M');
            
            // Set header
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$_exportFilename.'"');
            header('Cache-Control: max-age=0');
            
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0
            
            
            // Send data to browser
            $this->exportToXls('php://output', $_fields, $_records);
        }
        
        
        /**
         * Reads xls-file
         */
        protected function readFromXls($_filename, $_params = array())
        {
            // This is due to PHPExcel produces PHP Notices and Warnings if provided file is cvs or other non-valid type
            $errorReporting = error_reporting(E_ERROR);
            
            
            // Extract params
            $startRowIndex = $this->getFromArray('startRowIndex', $_params, 1);
            $columnsNumber = $this->getFromArray('columnsNumber', $_params, 3);
            
            
            // Read file
            require_once(LIBS_DIR_PATH.'PHPExcel-1.7.9/PHPExcel.php');
            $phpExcelInput = PHPExcel_IOFactory::load($_filename);
            $phpExcelInputSheet = $phpExcelInput->getActiveSheet();
            $records = array();
            for ($rowIndex = $startRowIndex; $rowIndex <= $phpExcelInputSheet->getHighestRow(); ++$rowIndex)
            {
                $record = array();
                $empty = true;
                for ($columnIndex = 0; $columnIndex < $columnsNumber; ++$columnIndex)
                {
                    $cell = trim($phpExcelInputSheet->getCellByColumnAndRow($columnIndex, $rowIndex)->getValue());
                    $record[] = $cell;
                    $empty = $empty && empty($cell);
                }
                if (!$empty)
                $records[] = $record;
            }
            
            
            error_reporting($errorReporting);
            return $records;
        }
        
        
        protected function getMailTextFromString($_messageTemplate, $_lookups)
        {
            // Replace placeholders with corresponsing values
            $placeholders = array(); $values = array();
            $matches = null;
            preg_match_all('/<!!(.+)!!>/U', $_messageTemplate, $matches);
            for ($i = 0; $i < count($matches[0]); ++$i)
            {
                $placeholder = $matches[0][$i];
                if (!in_array($placeholder, $placeholders))
                {
                    $placeholders[] = $placeholder;
                    $ref = $matches[1][$i];
                    
                    unset($internalLookup);
                    if ($ref{0} == '{') // Internal lookup
                    {
                        $tmp = trim($ref, ' ');
                        $tmp = substr($tmp, 1, strlen($tmp)-2);
                        list($ref, $lookupStr) = explode(':', $tmp, 2);
                        $lookupStr = trim($lookupStr, ' ');
                        $internalLookup = json_decode($lookupStr, true);
                    }
                    
                    $value = null;
                    if (strpos($ref, '.'))
                        list ($lookupKey, $propertyKey) = explode('.', $ref);
                    else
                        list ($lookupKey, $propertyKey) = array('', $ref);
                    if ($lookupKey == 'appConfig')
                        $value = constant($propertyKey);
                    elseif (isset($_lookups[$lookupKey][$propertyKey]))
                        $value = $_lookups[$lookupKey][$propertyKey];
                    else    
                        $value = '';
                    if (isset($internalLookup))
                        $value = $internalLookup[$value];
                    $values[] = $value;
                }
            }
            return str_replace($placeholders, $values, $_messageTemplate);
        }
        
        
        protected function verifyCaptcha(array $_params = array())
        {
            $sessionCaptcha = $this->getFromArray('captcha', $_SESSION, null);
            if (empty($sessionCaptcha))
            {
                $this->errors['captcha'] = 'Verification code has not been generated.';
                throw new AsyncActionException('Verification code has not been generated.');
            }
            $captcha = $this->getFromArray('captcha', $_params, null);
            if (empty($captcha))
            {
                $this->errors['captcha'] = 'Please, enter verification code.';
                throw new AsyncActionException('Please, enter verification code.');
            }
            if ($sessionCaptcha != $captcha)
            {
                $this->errors['captcha'] = 'You have entered the code incorrectly - please try again.';
                throw new AsyncActionException('You have entered the code incorrectly - please try again.');
            }
        }
        
        
        protected function _getPositiveInteger($_key, $_params, $_required = false, $_default = null)
        {
            $value = isset($_params[$_key]) ? $_params[$_key] : $_default;
            if (is_null($value))
            {
                if ($_required)
                    throw new AsyncActionException('Missing required argument: \''.$_key.'\'.');
            }
            else // not null
            {
                if (filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) === false)
                    throw new AsyncActionException('Invalid argument (\''.$_key.'\'). Must be positive integer.');
            }
            return $value;
        }
        
        
        protected function _getPositiveFloat($_key, $_params, $_required = false, $_default = null)
        {
            $value = isset($_params[$_key]) ? $_params[$_key] : $_default;
            if (is_null($value))
            {
                if ($_required)
                    throw new AsyncActionException('Missing required argument: \''.$_key.'\'.');
            }
            else // not null
            {
                if (filter_var($value, FILTER_VALIDATE_FLOAT, array('options' => array('min_range' => 1))) === false)
                    throw new AsyncActionException('Invalid argument (\''.$_key.'\'). Must be positive float.');
            }
            return $value;
        }
        
        
        protected function _getNonNegativeInteger($_key, $_params, $_required = false, $_default = null)
        {
            $value = isset($_params[$_key]) ? $_params[$_key] : $_default;
            if (is_null($value))
            {
                if ($_required)
                    throw new AsyncActionException('Missing required argument: \''.$_key.'\'.');
            }
            else // not null
            {
                if (filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === false)
                    throw new AsyncActionException('Invalid argument (\''.$_key.'\'). Must be positive integer.');
            }
            return $value;
        }
        
        
        protected function _getBoolean($_key, $_params, $_required = false, $_default = null)
        {
            $value = isset($_params[$_key]) ? $_params[$_key] : $_default;
            if (is_null($value))
            {
                if ($_required)
                    throw new AsyncActionException('Missing required argument: \''.$_key.'\'.');
            }
            else // not null
            {
                if (filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0, 'max_range' => 1))) === false)
                    throw new AsyncActionException('Invalid argument (\''.$_key.'\'). Must be boolean (0 or 1).');
            }
            return $value;
        }
        
        
        protected function _getString($_key, $_params, $_required = false, $_default = null, $_options = null)
        {
            $label = isset($_options['label']) ? $_options['label'] : $_key;
            $value = isset($_params[$_key]) ? $_params[$_key] : $_default;
            if (!isset($_options['no_trim'])) $value = trim($value);
            if ($_required  and  (is_null($value) or $value == '')) throw new AsyncActionException($label.' is required.');
            if (isset($_options['max_length']) and strlen($value) > $_options['max_length']) throw new AsyncActionException($label.' is too long.');
            return $value;
        }
        
        
        protected function _getArray($_key, $_params, $_required = false, $_default = null)
        {
            $value = isset($_params[$_key]) ? $_params[$_key] : $_default;
            if (is_null($value))
            {
                if ($_required)
                    throw new AsyncActionException('Missing required argument: \''.$_key.'\'.');
            }
            else // not null
            {
                if (!is_array($value))
                    throw new AsyncActionException('Invalid argument (\''.$_key.'\'). Must be array.');
            }
            return $value;
        }

        
        protected function _deleteFile($_filePath)
        {
            if (file_exists($_filePath)) unlink($_filePath);
        }
        
        
        /**
         * Debug purpose
         */
        protected function _varDumpExit($_arg)
        {
            echo '<pre>'; var_dump($_arg); exit();
        }

        
        /**
         * Get image size
         */
        protected function _getImageSize($_src = false)
        {
            $size = array('width' => 200, 'height' => null); // Default image size
            if ($_src)
            {
                $imageFilename = '.'.$_src;
                if (file_exists($imageFilename))
                {
                    $tmp = getimagesize($imageFilename);
                    if ($tmp) $size = array('width' => $tmp[0], 'height' => $tmp[1]);
                }
            }
            return $size;
        }

        /**
         * create Thumb from video
         */

        protected function _createThumb($src, $dest, $width, $height, $quality) {
            if (!file_exists($src)) {
                //echo('Source file isn\'t exists');
                return false;
            }

            $size = getimagesize($src);

            if ($size === false) {
                //echo('Cannot get image info about source');
                return false;
            }

            $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
            $icfunc = "imagecreatefrom" . $format;
            if (!function_exists($icfunc)) {
                echo('Does not exists the function in GD to create image - "'. $icfunc. '"');
                return false;
            }

            $x_ratio = $width / $size[0];
            $y_ratio = $height / $size[1];

            $ratio       = min($x_ratio, $y_ratio);
            $use_x_ratio = ($x_ratio == $ratio);

            $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
            $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);

            $isrc = $icfunc($src);
            $idest = imagecreatetruecolor($new_width, $new_height);

            //    imagefill($idest, 0, 0, $rgb);
            imagecopyresampled($idest, $isrc, 0, 0, 0, 0, $new_width, $new_height, $size[0], $size[1]);

            imagejpeg($idest, $dest, $quality);

            imagedestroy($isrc);
            imagedestroy($idest);

            return array('width' => $new_width, 'height' => $new_height);
        }

    }

?>