<?php

    /**
     * Basic_MySqlManager_Service class file.
     *
     
     */
    
    
    ClassLoader::loadServiceClass('basic.abstract');
    
    
    /**
     * MySql connect exception
     */
    class MySqlConnectException extends Exception {}
    
    
    /**
     * MySql query exception
     */
    class MySqlQueryException extends Exception {}
    
    
    /**
     * MySql function descriptor
     */
    class MySqlFunc
    {
        public $funcName = null;
        public function __construct($_funcName)
        {
            $this->funcName = $_funcName;
        }
    }
    

    /**
     * MySQL manager service
     *
     
     */
    class Basic_MySqlManager_Service extends Basic_Abstract_Service
    {

        /**
         * @var resource Database server link
         */
        private $link;

        
        /**
         * @var resource Query result resource
         */
        private $result = false;


        /**
         * Constructor
         */
        public function __construct()
        {
            $this->link = null;
        }
        
        
        /**
         * Destructor
         */
        public function __destruct()
        {
            // Free last result and close the connection
            $this->freeResult();
            $this->close();
        }
        
        
        /**
         * Tells if MySQL connection has been established
         */
        public function connected()
        {
            return !is_null($this->link);
        }


        /**
         * Performs connection to the database server
         */
        public function connect($_hostName, $_userName, $_password, $_dbName)
        {
            // Connect
            $this->link = @mysql_connect($_hostName, $_userName, $_password);
            if ($this->link === false) throw new MySqlConnectException(mysql_error());

            // Select database
            if (mysql_select_db($_dbName) === false) throw new MySqlConnectException(mysql_error());
            
            // Set encoding & timezone
            $this->query('SET NAMES \'UTF8\'');
            if (defined('MYSQL_TIMEZONE')) $this->query('SET time_zone=\''.MYSQL_TIMEZONE.'\'');
        }


        /**
         * Closes the database server connection
         */
        public function close()
        {
            if($this->link)
            {
                mysql_close($this->link);
                $this->link = null;
            }
        }

        
        /**
         * Accesses the ID generated from the previous INSERT operation
         * @return integer|boolean ID generated from the previous INSERT operation
         */
        public function getInsertId()
        {
            return mysql_insert_id($this->link);
        }

        
        /**
         * Performs SQL query
         * @param string $_query  SQL statemement
         */
        public function query($_query)
        {
            // Free previous result
            $this->freeResult();

            // Perform query
            $this->result = mysql_query($_query, $this->link);
            if ($this->result === false)
                throw new MySqlQueryException(mysql_error());
            
            return $this;
        }
        
        
        /**
         * Frees the result
         */
        public function freeResult()
        {
            if (is_resource($this->result))
            {
                mysql_free_result($this->result);
                $this->result = null;
            }
        }


        /**
         * Extracts record as associative map
         * @return array Record
         */
        public function fetchAssoc()
        {
            return mysql_fetch_assoc($this->result);
        }

        /**
         * Extracts record first column
         */
        public function fetchColumn()
        {
            $result = mysql_fetch_assoc($this->result);
            if($result){
                return reset($result);
            }
            return false;
        }
        
        
        /**
         * Extracts record
         * @param integer $_resultType Mapping type
         * @return array Record
         */
        public function fetchArray($_resultType = MYSQL_BOTH)
        {
            return mysql_fetch_array($this->result, $_resultType);
        }

        
        /**
         * Extracts all records
         * @param integer $_resultType Mapping type
         * @return array Fetched records
         */
        public function fetchAll($_resultType = MYSQL_ASSOC)
        {
            $records = array();
            while ($record = mysql_fetch_array($this->result, $_resultType))
                $records[] = $record;
            $this->freeResult();
            return $records;
        }

        
        /**
         * Extracts all records
         * @param integer $_resultType Mapping type
         * @return array Fetched records
         */
        public function fetchAllCellValue($_fieldKey, $_resultType = MYSQL_ASSOC)
        {
            $records = array();
            while ($record = mysql_fetch_array($this->result, $_resultType))
                $records[] = $record[$_fieldKey];
            $this->freeResult();
            return $records;
        }

        
        /**
         * Extracts all records as key-record map
         * @param string $_key Mapping key
         * @param integer $_resultType Mapping type
         * @return array Fetched records
         */
        public function fetchAllAsMap($_key = 'id', $_resultType = MYSQL_ASSOC)
        {
            $records = array();
            while ($record = mysql_fetch_array($this->result, $_resultType))
                $records[$record[$_key]] = $record;
            $this->freeResult();
            return $records;
        }

        
        /**
         * Extracts all records as key-value map
         * @param string $_keyFieldKey Field key whom value is to be a map key
         * @param string $_valueFieldKey Field key whom value is to be a map value
         * @return array
         */
        public function fetchAllAsKeyValueMap($_keyFieldKey, $_valueFieldKey)
        {
            $records = array();
            while ($record = mysql_fetch_array($this->result, MYSQL_ASSOC))
                $records[$record[$_keyFieldKey]] = $record[$_valueFieldKey];
            $this->freeResult();
            return $records;
        }

        
        /**
         * Extracts all records as name by ID
         * @param array Records set to be extracted
         * @param integer $_resultType Mapping type
         */
        public function fetchAllRecordsAsNameById(&$_records, $_resultType = MYSQL_BOTH)
        {
            $_records = array();
            while ($record = mysql_fetch_array($this->result, $_resultType))
                $_records[$record['id']] = $record['name'];
            $this->freeResult();
        }
        
        
        /**
         * Accesses the number of records in the result
         * @return integer|boolean The number of records in the result or false if no result
         */
        public function getRecordsCount()
        {
            return mysql_num_rows($this->result);
        }
        
        
        /**
         * Accesses the cell value
         * @param string Cell name
         * @return mixed Cell value on success, null otherwise
         */
        public function fetchCellValue($_cellName)
        {
            $record = mysql_fetch_assoc($this->result);
            return $record !== false ? $record[$_cellName] : null;
        }

        
        /**
         * Accesses the number of affected rows
         * @return int Affected rows count
         */
        public function getAffectedRows()
        {
            return mysql_affected_rows();
        }
        
        
        /**
         * Executes insert query
         * @param string $_tableName Table name
         * @param array $_data Data
         */
        public function insert($_tableName, $_data)
        {
            $query = 'INSERT INTO `'.$_tableName.'` SET ';
            foreach ($_data as $key => $value)
            {
                if (is_null($value))
                    $query .= '`'.$key.'` = NULL, ';
                elseif ($value instanceof MySqlFunc)
                    $query .= '`'.$key.'`='.$value->funcName.'(),';
                else
                    $query .= '`'.$key.'` = \''.mysql_real_escape_string($value).'\', ';
            }
            $query = substr($query, 0, strlen($query)-2);
            $this->query($query);
            return $this;
        }

        /**
         * Executes insert query
         * @param string $_tableName Table name
         * @param array $_data Data
         */
        public function insertMulti($_tableName, $_data)
        {
            $query = 'INSERT INTO `'.$_tableName.'` ';
            foreach ($_data as $dataKey => $dataValue)
            {
                foreach($dataValue as $key=>$value){
                    if (is_null($value))
                        $query .= '`'.$key.'` = NULL, ';
                    elseif ($value instanceof MySqlFunc)
                        $query .= '`'.$key.'`='.$value->funcName.'(),';
                    else
                        $query .= '`'.$key.'` = \''.mysql_real_escape_string($value).'\', ';    
                }
                
            }
            $query = substr($query, 0, strlen($query)-2);
            $this->query($query);
            return $this;
        }
        
        
        /**
         * WARNING: This function is deprecated, use insert instead
         * Inserts record
         * @param string $_tableName Table name
         * @param array $_data Data
         */
        public function insertRecord($_tableName, $_data)
        {
            return $this->insert($_tableName, $_data);
        }
        
        
        /**
         * Executes UPDATE query
         * @param string $_tableName Table name
         * @param array $_data Data
         * @param string $_criteria Update criteria (condition)
         */
        public function update($_tableName, $_data, $_criteria = '1')
        {
            $query = 'UPDATE `'.$_tableName.'` SET ';
            foreach ($_data as $key => $value)
            {
                if (is_null($value))
                    $query .= '`'.$key.'`=NULL,';
                elseif ($value instanceof MySqlFunc)
                {
                    $query .= '`'.$key.'`='.$value->funcName.'(),';
                }
                else
                    $query .= '`'.$key.'`=\''.mysql_real_escape_string($value).'\',';
            }
            $query = rtrim($query, ',');
            
            
            // Condition
            if (is_array($_criteria))
            {
                $query .= ' WHERE 1';
                foreach ($_criteria as $key => $value)
                {
                    if (is_array($value))
                    {
                        $query .= ' AND `'.$key.'` IN (';
                        foreach ($value as $valueItem)
                            $query .= '\''.mysql_real_escape_string($valueItem).'\',';
                        $query = trim($query, ',');
                        $query .= ')';
                    }
                    else
                        $query .= ' AND `'.$key.'`=\''.mysql_real_escape_string($value).'\'';
                }
            }
            else
                $query .= ' WHERE '.$_criteria;
                
            
            return $this->query($query);
        }

        
        /**
         * Deletes records with specified criteria
         * @param string $_tableName Table name
         * @param string $_criteria Criteria (condition)
         */
        public function delete($_tableName, $_criteria = '1')
        {
            $query = 'DELETE FROM `'.$_tableName.'`';
            
            
            // Condition
            if (is_array($_criteria))
            {
                $query .= ' WHERE 1';
                foreach ($_criteria as $key => $value)
                {
                    if (is_array($value))
                    {
                        $query .= ' AND `'.$key.'` IN (';
                        foreach ($value as $valueItem)
                            $query .= '\''.mysql_real_escape_string($valueItem).'\',';
                        $query = trim($query, ',');
                        $query .= ')';
                    }
                    else
                        $query .= ' AND `'.$key.'`=\''.mysql_real_escape_string($value).'\'';
                }
            }
            else
                $query .= ' WHERE '.$_criteria;
            
            
            $this->query($query);
        }
        
        
        /**
         * Executes START TRANSACTION query
         */
        public function startTransaction()
        {
            $this->query('START TRANSACTION');
        }
        
        
        /**
         * Executes ROLLBACK query
         */
        public function rollback()
        {
            $this->query('ROLLBACK');
        }
        
        
        /**
         * Executes COMMIT query
         */
        public function commit()
        {
            $this->query('COMMIT');
        }
        
        
        /**
         * Executes COUNT(*) query
         * @param string $_tableName Table name
         * @param string $_criteria Condition
         * @return integer Count value
         */
        public function count($_tableName, $_criteria = '1')
        {
            $query = 'SELECT COUNT(*) AS `c` FROM `'.$_tableName.'`';
            
            // Condition
            if (is_array($_criteria))
            {
                $query .= ' WHERE 1';
                foreach ($_criteria as $key => $value)
                {
                    if (is_array($value))
                    {
                        $query .= ' AND `'.$key.'` IN (';
                        foreach ($value as $valueItem)
                            $query .= '\''.mysql_real_escape_string($valueItem).'\',';
                        $query = trim($query, ',');
                        $query .= ')';
                    }
                    else
                    {
                        if (is_null($value))
                            $query .= ' AND `'.$key.'` IS NULL';
                        else
                            $query .= ' AND `'.$key.'`=\''.mysql_real_escape_string($value).'\'';
                    }
                }
            }
            else
                $query .= ' WHERE '.$_criteria;
            return (int) $this->query($query)->fetchCellValue('c');
        }
        
        
        /**
         * Executes SUM(`field_key`) query
         * @param string $_tableName Table name
         * @param string $_fieldKey Field key to sum
         * @param string $_criteria Condition
         * @return integer Count value
         */
        public function sum($_fieldKey, $_tableName, $_criteria = '1')
        {
            $query = 'SELECT SUM(`'.$_fieldKey.'`) AS `sum` FROM `'.$_tableName.'`';
            
            // Condition
            if (is_array($_criteria))
            {
                $query .= ' WHERE 1';
                foreach ($_criteria as $key => $value)
                {
                    if (is_array($value))
                    {
                        $query .= ' AND `'.$key.'` IN (';
                        foreach ($value as $valueItem)
                            $query .= '\''.mysql_real_escape_string($valueItem).'\',';
                        $query = trim($query, ',');
                        $query .= ')';
                    }
                    else
                    {
                        if (is_null($value))
                            $query .= ' AND `'.$key.'` IS NULL';
                        else
                            $query .= ' AND `'.$key.'`=\''.mysql_real_escape_string($value).'\'';
                    }
                }
            }
            else
                $query .= ' WHERE '.$_criteria;
            
            return $this->query($query)->fetchCellValue('sum');
        }
        
        
        /**
         * Executes MAX(`field_key`) query
         * @param string $_tableName Table name
         * @param string $_fieldKey Field key to sum
         * @param string $_criteria Condition
         * @return integer Max value
         */
        public function max($_fieldKey, $_tableName, $_criteria = '1')
        {
            $query = 'SELECT MAX(`'.$_fieldKey.'`) AS `max` FROM `'.$_tableName.'`';
            
            // Condition
            if (is_array($_criteria))
            {
                $query .= ' WHERE 1';
                foreach ($_criteria as $key => $value)
                {
                    if (is_array($value))
                    {
                        $query .= ' AND `'.$key.'` IN (';
                        foreach ($value as $valueItem)
                            $query .= '\''.mysql_real_escape_string($valueItem).'\',';
                        $query = trim($query, ',');
                        $query .= ')';
                    }
                    else
                    {
                        if (is_null($value))
                            $query .= ' AND `'.$key.'` IS NULL';
                        else
                            $query .= ' AND `'.$key.'`=\''.mysql_real_escape_string($value).'\'';
                    }
                }
            }
            else
                $query .= ' WHERE '.$_criteria;
            
            return $this->query($query)->fetchCellValue('max');
        }
        
        
        /**
         * Executes SUM(`field_key`) query
         * @param string $_tableName Table name
         * @param string $_fieldKey Field key to sum
         * @param string $_criteria Condition
         * @return integer Count value
         */
        public function sumOfProductions($_tableName, $_fieldKey1, $_fieldKey2, $_criteria = '1')
        {
            $query = 'SELECT SUM(`'.$_fieldKey1.'`*`'.$_fieldKey2.'`) AS `sum` FROM `'.$_tableName.'`';
            
            // Condition
            if (is_array($_criteria))
            {
                $query .= ' WHERE 1';
                foreach ($_criteria as $key => $value)
                {
                    if (is_array($value))
                    {
                        $query .= ' AND `'.$key.'` IN (';
                        foreach ($value as $valueItem)
                            $query .= '\''.mysql_real_escape_string($valueItem).'\',';
                        $query = trim($query, ',');
                        $query .= ')';
                    }
                    else
                    {
                        if (is_null($value))
                            $query .= ' AND `'.$key.'` IS NULL';
                        else
                            $query .= ' AND `'.$key.'`=\''.mysql_real_escape_string($value).'\'';
                    }
                }
            }
            else
                $query .= ' WHERE '.$_criteria;
            
            return $this->query($query)->fetchCellValue('sum');
        }
        
        
        /**
         * Executes COUNT(DISTINCT) query
         * @param string $_tableName Table name
         * @param string $_columnName Column name
         * @param string $_criteria Condition
         * @return integer Count value
         */
        public function countDistinct($_tableName, $_columnName, $_criteria = '1')
        {
            return $this->query('SELECT COUNT(DISTINCT `'.$_columnName.'`) AS `c` FROM `'.$_tableName.'` WHERE '.$_criteria)->fetchCellValue('c');
        }
        
        
        public function selectDistinct($_columnName, $_tableName, $_criteria = '1', $_order = null)
        {
            // Condition
            if (is_array($_criteria))
            {
                $criteria = '1';
                foreach ($_criteria as $key => $value)
                {
                    if (is_array($value))
                    {
                        $criteria .= ' AND `'.$key.'` IN (';
                        foreach ($value as $valueItem)
                            $criteria .= '\''.mysql_real_escape_string($valueItem).'\',';
                        $criteria = trim($criteria, ',');
                        $criteria .= ')';
                    }
                    else
                        $criteria .= ' AND `'.$key.'`=\''.mysql_real_escape_string($value).'\'';
                }
            }
            else
                $criteria = $_criteria;
            
            
            // Compose query
            $query = 'SELECT DISTINCT `'.$_columnName.'` FROM `'.$_tableName.'` WHERE '.$criteria.'';
            
            
            // Order clause
            if (!empty($_order))
                $query .= ' ORDER BY '.$_order;
            
            
            return $this->query($query);
        }
        
        
        public function countGroups($_select, $_tableName, $_groupColumnKey, $_criteria = '1')
        {
            return $this->query('SELECT '.$_select.', COUNT(*) AS `count` FROM `'.$_tableName.'` WHERE '.$_criteria.' GROUP BY `'.$_groupColumnKey.'`')->fetchAll();
        }
        
        
        /**
         * Executes SELECT query
         * @param mixed $_select Select clause
         * @param string $_tableName Table name
         * @param string $_criteria Condtion
         * @param mixed $_order Order
         * @return array Extracted records
         */
        public function select($_select, $_tableName, $_criteria = '1', $_order = null, $_limit = null, $_offset = null)
        {
            // Select clause
            $query = 'SELECT ';
            if (is_array($_select))
            {
                foreach ($_select as $_fieldKey)
                    $query .= '`'.$_fieldKey.'`,';
                $query = trim($query, ',');
            }
            else
                $query .= $_select;
            
            
            // From clause
            $query .= ' FROM `'.$_tableName.'`';
            
            
            // Condition
            if (is_array($_criteria))
            {
                $query .= ' WHERE 1';
                foreach ($_criteria as $key => $value)
                {
                    if (is_array($value))
                    {
                        $query .= ' AND `'.$key.'` IN (';
                        foreach ($value as $valueItem)
                            $query .= '\''.mysql_real_escape_string($valueItem).'\',';
                        $query = trim($query, ',');
                        $query .= ')';
                    }
                    else
                    {
                        if (is_null($value))
                            $query .= ' AND `'.$key.'` IS NULL';
                        else
                            $query .= ' AND `'.$key.'`=\''.mysql_real_escape_string($value).'\'';
                    }
                }
            }
            else
                $query .= ' WHERE '.$_criteria;
            
            
            // Order clause
            if (!empty($_order))
                $query .= ' ORDER BY `'.$_order.'`';
            
            
            // LIMIT clause
            if (!empty($_limit))
                $query .= ' LIMIT '.$_limit;
            
            
            // OFFSET clause
            if (!empty($_offset))
                $query .= ' OFFSET '.$_offset;
            
            
            // Execution
            return $this->query($query);
        }
        
        
        /**
         * Sets specified time zone
         * @param string UTC offset
         */
        public function setTimeZone($_utcOffset)
        {
            $this->query('SET time_zone=\''.$_utcOffset.'\'');
        }
        
        
        /**
         * Get record [by specified criteria]
         *
         * @param string $_tableName Table name
         * @param int $_criteria Criteria
         *
         * @return array|boolean Record data as array or false if record is not found
         */
        public function getRecord($_tableName, $_criteria = '1')
        {
            $query = 'SELECT * FROM `'.$_tableName.'`';
            if (is_array($_criteria))
            {
                $query .= ' WHERE 1';
                foreach ($_criteria as $key => $value)
                {
                    if (is_array($value))
                    {
                        $query .= ' AND `'.$key.'` IN (';
                        foreach ($value as $valueItem)
                            $query .= '\''.mysql_real_escape_string($valueItem).'\',';
                        $query = trim($query, ',');
                        $query .= ')';
                    }
                    else
                        $query .= ' AND `'.$key.'`=\''.mysql_real_escape_string($value).'\'';
                }
            }
            else
                $query .= ' WHERE '.$_criteria;
            return $this->query($query)->fetchAssoc();
        }
        
        
        /**
         * Get record by ID
         *
         * @param string $_tableName Table name
         * @param int $_id Record ID
         *
         * @return array|boolean Record data as array or false if record is not found
         */
        public function getRecordById($_tableName, $_id, $_order = null)
        {
            $query = 'SELECT * FROM `'.$_tableName.'` WHERE `id`='.$_id;
            if (!is_null($_order)) $query .= ' ORDER BY '.$_order;
            return $this->query($query)->fetchAssoc();
        }
        
        
        /**
         * Get records
         * 
         * @param string $_tableName Table name
         * @param string $_criteria Criteria (condition)
         *
         * @return array Array of records
         */
        public function getRecords($_tableName, $_criteria = '1', $_order = null)
        {
            if (is_array($_criteria))
            {
                $criteria = '1';
                foreach ($_criteria as $key => $value)
                {
                    if (is_array($value))
                    {
                        $criteria .= ' AND `'.$key.'` IN (';
                        foreach ($value as $valueItem)
                            $criteria .= '\''.mysql_real_escape_string($valueItem).'\',';
                        $criteria = trim($criteria, ',');
                        $criteria .= ')';
                    }
                    else
                        $criteria .= ' AND `'.$key.'`=\''.mysql_real_escape_string($value).'\'';
                }
            }
            else
                $criteria = $_criteria;
            $query = 'SELECT * FROM `'.$_tableName.'` WHERE '.$criteria;
            if (!is_null($_order)) $query .= ' ORDER BY '.$_order;
            return $this->query($query)->fetchAll();
        }

        /**
         * Executes SELECT query
         * @param mixed $_select Select clause
         * @param mixed $_tableName Table name
         * @param string $_criteria Condtion
         * @param mixed $_order Order
         * @return array Extracted records
         */
        public function mixedselectDistinct($_select, $_tableName, $_criteria = '1', $_order = null, $_limit = null, $_offset = null)
        {
            // Select clause
            $query = 'SELECT DISTINCT ';
            if (is_array($_select))
            {
                foreach ($_select as $_fieldKey)
                    $query .= '`'.$_fieldKey.'`,';
                $query = trim($query, ',');
            }
            else
                $query .= $_select;
            
            // From clause
            $query .= ' FROM ';
            if (is_array($_tableName))
            {
                foreach ($_tableName as $_fieldKey1)
                    $query .= '`'.$_fieldKey1.'`,';
                $query = trim($query, ',');
    }
            else
                $query .= '`'.$_tableName.'`';

            // Condition
            if (is_array($_criteria))
            {
                $query .= ' WHERE 1';
                foreach ($_criteria as $key => $value)
                {
                    if (is_array($value))
                    {
                        $query .= ' AND `'.$key.'` IN (';
                        foreach ($value as $valueItem)
                            $query .= '\''.mysql_real_escape_string($valueItem).'\',';
                        $query = trim($query, ',');
                        $query .= ')';
                    }
                    else
                    {
                        if (is_null($value))
                            $query .= ' AND `'.$key.'` IS NULL';
                        else
                            $query .= ' AND `'.$key.'`=\''.mysql_real_escape_string($value).'\'';
                    }
                }
            }
            else
                $query .= ' WHERE '.$_criteria;
            
            
            // Order clause
            if (!empty($_order))
                $query .= ' ORDER BY `'.$_order.'`';
            
            
            // LIMIT clause
            if (!empty($_limit))
                $query .= ' LIMIT '.$_limit;
            
            
            // OFFSET clause
            if (!empty($_offset))
                $query .= ' OFFSET '.$_offset;
            
            
            // Execution
            return $this->query($query);
        }

    }


?>