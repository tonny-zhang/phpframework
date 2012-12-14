<?php
/**
 *  SQL拼装工具
 *
 */

class FDX_Db_SqlBuilder
{
    function create($table , $fields)
    {
        $sql_t  = $this->getFieldsSql($fields , 'insert');
        $sql    = "INSERT INTO {$table} ({$sql_t['field']}) VALUES ({$sql_t['value']})";
        return $sql;
    }

    function update($table , $fields , $where)
    {
        $where  = $this->getWhere($where);
        $sql    = "UPDATE {$table} SET ".$this->getFieldsSql($fields , 'update')." {$where}";
        return $sql;
    }

    function delete($table , $where)
    {
        $where  = $this->getWhere($where);
        $sql    = "DELETE FROM {$table} {$where}";
        return $sql;
    }

    function find($table , $where = null , $order = null , $limit = 0 , $field = '*')
    {
        $field  = $this->getField($field);
        $where  = $this->getWhere($where);
        $order  = $this->getOrder($order);
        $limit  = $this->getLimit($limit);
        $sql    = "SELECT {$field} FROM {$table} {$where} {$order} {$limit}";
        return $sql;
    }

    function getField($fields = null)
    {
        if (is_null($fields))
        {
            return '*';
        }
        elseif (is_string($fields))
        {
            return $fields;
        }
        elseif (is_array($fields))
        {
            foreach ($fields as $key => $value)
            {
                $fields[$key]   = "`{$value}`";
            }
            return implode(' , ' , $fields);
        }
    }

    function getWhere($where = null)
    {
        if (!$where)
        {
            return '';
        }
        elseif (is_string($where))
        {
            return "WHERE {$where}";
        }
        elseif (is_array($where))
        {
            return "WHERE ".implode(' AND ' , $where);
        }
    }

    function getOrder($fields = null)
    {
        if (is_null($fields))
        {
            return ;
        }
        elseif (is_string($fields))
        {
            return "ORDER BY ".$fields;
        }
        elseif (is_array($fields))
        {
            foreach ($fields as $key => $value)
            {
                $fields[$key]   = "`{$key}` {$value}";
            }
            return "ORDER BY ".implode(' , ' , $fields);
        }
    }

    function getLimit($limit = 0 , $offset = 0)
    {
        if (!$limit)
        {
            return;
        }
        else
        {
            return "LIMIT {$limit}";
        }
    }

    /**
     * 将 fields 数组转换为 可用于sql查询的字串
     *
     * @param array $fields
     * @param string $type
     * @return array
     */
    function getFieldsSql($fields , $type)
    {
        /**
         * $fields 是否是数组
         * 否则返回false
         */
        if (!is_array($fields) || !count($fields))
        {
            return false;
        }
        
        /**
         * 遍历字段，生成用于 insert 或 update 的字串
         * insert 返回数组 field 和 value
         * 用于 (field) VALUES (value)
         * update 类型返回字串
         */
        foreach ($fields as $key => $value)
        {
            /**
             * update 用的 set 字段
             */
            if ($type == 'update')
            {
                $field['update'][]  = "`{$key}` = '{$value}'";
            }
            /**
             * insert 所用字段
             */
            elseif ($type == 'insert')
            {
                $field['field'][]  = "`{$key}`";
                $field['value'][]  = "'{$value}'";
            }
        }

        if ($type == 'update')
        {
            $field['update']    = implode(' , ' , $field['update']);
            return $field['update'];
        }
        elseif ($type == 'insert')
        {
            $field['field'] = implode(' , ' , $field['field']);
            $field['value'] = implode(' , ' , $field['value']);
            return $field;
        }
    }
}
