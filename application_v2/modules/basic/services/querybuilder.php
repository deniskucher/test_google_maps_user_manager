<?php

/**
 * Basic_MySqlManager_Service class file.
 *
 
 */


ClassLoader::loadServiceClass('basic.abstract');


class Basic_QueryBuilder_Service extends Basic_Abstract_Service
{

    public static function escape($value)
    {
        return '"' . mysql_real_escape_string($value) . '"';
    }

    public static function escapeKey($key, $table_name = false)
    {
        return '`' . $key . '`';
    }

    public static function buildQueryList($params = array())
    {
        foreach ($params as &$param) {
            if ($param === null) {
                $param = 'NULL';
            }
        }
        return ' (' . implode(',', $params) . ') ';
    }

    public static function buildQuery($query, $params = false, $row = false)
    {
        return $query . (self::buildQueryKeyEqualValue($params, true)) . ($row ? " LIMIT 1 " : "");
    }

    public static function buildQueryTable($table_name, $params = false)
    {
        return self::buildQueryTableColumn($table_name, false, $params);
    }

    public static function buildQueryTableColumn($table_name, $column_name = false, $params = false)
    {
        return "Select " . ($column_name ? self::escapeKey($column_name) : "*") . " from " . self::escapeKey($table_name,
            1) . (self::buildQueryKeyEqualValue($params, 1));
    }

    public static function buildQueryTableColumns($table_name, $columns = [], $params = false, $connectors = [])
    {
        foreach ($columns as &$column) {
            $column = static::escapeKey($column);
        }
        return "Select " . (!empty($columns) ? implode(',', $columns) : "*") . " from " . self::escapeKey($table_name,
            1) . self::buildQueryJoin($connectors) . (self::buildQueryKeyEqualValue($params,
            1)) . self::buildQueryGroup($connectors);
    }

    public static function buildQueryTableFunction($table_name, $function = false, $params = false)
    {
        return "Select " . $function . " from " . self::escapeKey($table_name,
            1) . (self::buildQueryKeyEqualValue($params, 1));
    }

    public static function buildQueryKeyEqualValue($fields = array(), $where = false, $exclude_key = false)
    {
        $query = '';
        if (is_array($fields)) {
            $queryPieces = [];
            foreach ($fields as $key => $value) {
                $queryPieces [] = self::buildQueryKeyEqualValueCondition($where, $exclude_key, $key, $value);
            }
            $query = implode(' AND ', $queryPieces);
        }
        if ($where && !empty($query)) {
            $query = ' WHERE ' . $query;
        }
        return $query;
    }

    public static function buildQueryKeyEqualValueCondition($where, $exclude_key, $key, $value)
    {
        $query = '';
        if ($exclude_key === $key) {
            return $query;
        }
        if ($key === 'OR') {
            $queryPieces = [];
            foreach ($value as $orKey => $orValue) {
                $queryAndPieces = [];
                foreach ($orValue as $andKey => $andValue) {
                    $queryAndPieces [] = self::buildQueryKeyEqualValueCondition($where, $exclude_key, $andKey,
                        $andValue);
                }
                $queryPieces[] = ' ( ' . implode(' AND ', $queryAndPieces) . ' ) ';
            }
            $query .= implode(' ' . $key . ' ', $queryPieces);
            return $query;
        } else {
            $query .= (empty($query) ? '' : ($where ? ' AND ' : ' , '));
        }
        $qKey = self::escapeKey($key);
        if (is_array($value) && $where) {
            $queryArray = [];
            foreach ($value as $kkey => $vvalue) {
                $kkey = strtolower($kkey);
                if ($kkey == 'in') {
                    $queryArray[] = $qKey . ' IN ' . self::buildQueryList($vvalue);
                } elseif (in_array($kkey, ['=', '!=', '<>', '<', '>', 'like'])) {
                    if ($vvalue === null) {
                        if ($kkey == '=') {
                            $newKey = ' is ';
                        } else {
                            $newKey = ' NOT is ';
                        }
                        $queryArray[] = $qKey . $newKey . ' NULL ';
                    } else {
                        if ($kkey == 'like') {
                            $queryArray[] = $qKey . $kkey . ' ' . self::escape('%' . $vvalue . '%');
                        } else {
                            $queryArray[] = $qKey . $kkey . ' ' . self::escape($vvalue);
                        }
                    }
                }
            }
            if (!empty($queryArray)) {
                $query .= '( ' . implode(' AND ', $queryArray) . ' ) ';
            }
        } elseif (!is_array($value)) {
            $query .= ($qKey . (($value === null && $where) ? ' is ' : ' = ') . ($value === null ? ' NULL ' : self::escape($value)));
        }
        return $query;
    }

    public static function buildQueryUpdate($table, $fields = array(), $where = array())
    {
        return "UPDATE " . self::escapeKey($table,
            1) . " SET " . self::buildQueryKeyEqualValue($fields) . ' ' . self::buildQueryKeyEqualValue($where,
            true);
    }

    public static function buildQueryDelete($table, $where = array())
    {
        return "DELETE FROM " . self::escapeKey($table, 1) . ' ' . self::buildQueryKeyEqualValue($where, true);
    }

    public static function buildQueryDuplicateUpdate($fields, $exclude_key)
    {
        $query = self::buildQueryKeyEqualValue($fields, false, $exclude_key);
        return (empty($query) ? '' : (" ON DUPLICATE KEY UPDATE " . $query));
    }

    public static function buildQueryInsert($table, $fields)
    {
        $arguments = array();
        $keys = array();
        foreach ($fields as $field_key => $field) {
            if (is_array($field)) {
                foreach ($field as $key => $value) {
                    $field[$key] = static::escape($value);
                }
                if (empty($keys)) {
                    $keys = array_keys($field);
                    foreach ($keys as &$_key) {
                        $_key = self::escapeKey($_key);
                    }
                }
                $arguments[] = self::buildQueryList($field);
            } else {
                $keys[] = self::escapeKey($field_key);
                $fields[$field_key] = static::escape($field);

            }
        }
        if (empty($arguments)) {
            $arguments[] = self::buildQueryList($fields);
        }
        return "INSERT INTO " . self::escapeKey($table, 1) . self::buildQueryList($keys) . ' VALUES ' . (implode(',',
            $arguments));
    }

    public static function buildQuerySort($fields)
    {
        $order = "";
        if (is_array($fields) && !empty($fields)) {
            $new_arr = array();
            $order = " ORDER BY ";
            foreach ($fields as $key => $value) {
                $new_arr[] = self::escapeKey($key) . (strtoupper($value) == 'DESC' ? ' DESC ' : ' ASC ');
            }
            $order .= implode(',', $new_arr);
        }
        return $order;
    }

    public static function buildQueryGroup($connectors = [])
    {
        $group = "";
        if (is_array($connectors) && !empty($connectors)) {
            $new_arr = array();
            $group = " GROUP BY ";
            foreach ($connectors as $key => $value) {
                $new_arr[] = $value['externalField'];
            }
            $group .= implode(',', $new_arr);
        }
        return $group;
    }

    public static function buildQueryJoin($connectors = [])
    {
        $join = "";
        if (is_array($connectors) && !empty($connectors)) {
            $new_arr = array();

            foreach ($connectors as $key => $value) {
                $new_arr[] = ' left join ' . self::escapeKey($value['table']) . ' on ' .
                    self::escapeKey($value['externalField']) . ' = ' . self::escapeKey($value['internalField']) . ' ';
            }
            $join .= implode(' ', $new_arr);
        }
        return $join;
    }

    public static function buildQueryPagination($params = [])
    {
        if (!empty($params['row'])) {
            return ' LIMIT 1 ';
        }
        if (!empty($params['all'])) {
            return ' ';
        }
        $page = isset($params['page']) ? $params['page'] : 1;
        $page_count = isset($params['page_count']) ? $params['page_count'] : 20;
        $count_all = isset($params['count_all']) ? $params['count_all'] : 20;
        if (($count_all % $page_count) > 0) {
            $pages_count = floor($count_all / $page_count) + 1;
        } else {
            $pages_count = floor($count_all / $page_count);
        }
        $page_current = $page;
        if ($page_current < 0) {
            $page_current = 1;
        }
        if ($page_current > $pages_count) {
            $page_current = $pages_count;
        }
        if (is_numeric($page) && is_numeric($page_count)) {
            return " LIMIT " . ((($page_current - 1) * $page_count)) . "," . $page_count . " ";
        }
        return " LIMIT 0," . $page_count . " ";
    }

    public function buildQueryFull($table, $columns = [], $params = false, $order = [], $paging = [], $connectors = [])
    {
        return self::buildQueryTableColumns($table, $columns, $params,
            $connectors) . self::buildQuerySort($order) . self::buildQueryPagination($paging);
    }

    public function buildQueryCount($table, $params = false)
    {
        return self::buildQueryTableFunction($table, 'count(*)', $params);
    }

    public static function buildQueryInsertFull($table, $fields, $duplicate = array())
    {
        $query = self::buildQueryInsert($table, $fields);
        if (!empty($duplicate) && $duplicate['type'] == 'update') {
            $query .= self::buildQueryDuplicateUpdate($fields, $duplicate['key']);
        }
        return $query;
    }

    public static function buildQueryInsertMulti($table, $fields)
    {
        $query = self::buildQueryInsert($table, $fields);
        return $query;
    }
}

