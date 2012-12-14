<?php

/**
 * FDX_Session 类提供将 session 保存到数据库的能力
 *
 * 要使用 FDX_Session，必须完成下列准备工作：
 *
 * - 创建需要的数据表
 *
 *     字段名       类型             用途
 *     sess_id     varchar(64)     存储 session id
 *     sess_data   text            存储 session 数据
 *     activity    int(11)         该 session 最后一次读取/写入时间
 *
 */

class FDX_Session
{
    /**
     * 数据库访问对象
     *
     */
    var $db = null;

    /**
     * 保存 session 的数据表名称
     *
     * @var string
     */
    var $tableName = null;

    /**
     * 保存 session id 的字段名
     *
     * @var string
     */
    var $fieldId = null;

    /**
     * 保存 session 数据的字段名
     *
     * @var string
     */
    var $fieldData = null;

    /**
     * 保存 session 过期时间的字段名
     *
     * @var string
     */
    var $fieldActivity = null;

    /**
     * 指示 session 的有效期
     *
     * 0 表示由 PHP 运行环境决定，其他数值为超过最后一次活动时间多少秒后失效
     *
     * @var int
     */
    var $lifeTime = 0;

    /**
     * session name
     */

    var $sessionName = null;
    /**
     * 构造函数
     */

    function FDX_Session()
    {
        $config = Core::getConfig('Session');
        $this->tableName = $config['tableName'];
        $this->fieldId = $config['fieldId'];
        $this->fieldData = $config['fieldData'];
        $this->fieldActivity = $config['fieldActivity'];
        $this->lifeTime = intval($config['lifeTime']);
        
        session_set_save_handler(
                array(& $this, 'sessionOpen'),
                array(& $this, 'sessionClose'),
                array(& $this, 'sessionRead'),
                array(& $this, 'sessionWrite'),
                array(& $this, 'sessionDestroy'),
                array(& $this, 'sessionGc')
        );
    }

    /**
     * 析构函数
     */
    /*function __destruct()
    {
        session_write_close();
    }*/

    function start()
    {
        session_name('fan_s');
        session_start();
    }
    
    /**
     * 打开 session
     *
     * @param string $savePath
     * @param string $sessionName
     *
     * @return boolean
     */
    function sessionOpen($savePath, $sessionName)
    {
        $this->db = FDX_Model::getInstance()->getDb();
        if(!$this->db)
        {
            throw new FDX_Exception('unable to open db connection for session operation');
        }
        $this->sessionGc($this->lifeTime);
        return true;
    }

    /**
     * 关闭 session
     *
     * @return boolean
     */
    function sessionClose()
    {
        return true;
    }

    /**
     * 读取指定 id 的 session 数据
     *
     * @param string $sessid
     *
     * @return string
     */
    function sessionRead($sessid)
    {
        $sessid = $this->db->escapeString($sessid);
        $sql = "SELECT {$this->fieldData} FROM {$this->tableName} WHERE {$this->fieldId} = '{$sessid}'";
        if ($this->lifeTime > 0)
        {
            $time = time() - $this->lifeTime;
            $sql .= " AND {$this->fieldActivity} >= {$time}";
        }

        return $this->db->getOne($sql);
    }

    /**
     * 写入指定 id 的 session 数据
     *
     * @param string $sessid
     * @param string $data
     *
     * @return boolean
     */
    function sessionWrite($sessid, $data)
    {
        $sessid = $this->db->escapeString($sessid);
        $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE {$this->fieldId} = '{$sessid}'";
        $data= $this->db->escapeString($data);
        $activity = time();

        $fields = (array)$this->_beforeWrite($sessid);
        if ((int)$this->db->getOne($sql) > 0)
        {
            $sql = "UPDATE {$this->tableName} SET {$this->fieldData} = '{$data}', {$this->fieldActivity} = {$activity}";
            if (!empty($fields))
            {
                $arr = array();
                foreach ($fields as $field => $value)
                {
                    $arr[] = $field . ' = ' . $this->db->escapeString($value);
                }
                $sql .= ', ' . implode(', ', $arr);
            }
            $sql .= " WHERE {$this->fieldId} = '{$sessid}'";
        }
        else
        {
            $extraFields = '';
            $extraValues = '';
            if (!empty($fields))
            {
                foreach ($fields as $field => $value)
                {
                    $extraFields .= ', ' . $field;
                    $extraValues .= ', ' . $this->db->escapeString($value);
                }
            }

            $sql = "INSERT INTO {$this->tableName} ({$this->fieldId}, {$this->fieldData}, {$this->fieldActivity}{$extraFields}) VALUES ('{$sessid}', '{$data}', {$activity}{$extraValues})";
        }
        
        $this->db->execute($sql);
    }

    /**
     * 销毁指定 id 的 session
     *
     * @param string $sessid
     *
     * @return boolean
     */
    function sessionDestroy($sessid)
    {
        $sessid = $this->db->escapeString($sessid);
        $sql = "DELETE FROM {$this->tableName} WHERE {$this->fieldId} = '{$sessid}'";
        return $this->db->execute($sql);
    }

    /**
     * 清理过期的 session 数据
     *
     * @param int $maxlifetime
     *
     * @return boolean
     */
    function sessionGc($maxlifetime)
    {
        if ($this->lifeTime > 0)
        {
            $maxlifetime = $this->lifeTime;
        }
        $time = time() - $maxlifetime;
        $sql = "DELETE FROM {$this->tableName} WHERE {$this->fieldActivity} < {$time}";
        $this->db->execute($sql);
        return true;
    }

    /**
     * 获取未过期的 session 总数
     *
     * @return int
     */
    function getOnlineCount($lifetime = -1)
    {
        if ($this->lifeTime > 0)
        {
            $lifetime = $this->lifeTime;
        }
        else if ($lifetime <= 0)
        {
            $lifetime = (int)ini_get('session.gc_maxlifetime');
            if ($lifetime <= 0)
            {
                $lifetime = 1440;
            }
        }
        $sql = "SELECT COUNT(*) FROM {$this->tableName}";
        if ($this->lifeTime > 0)
        {
            $time = time() - $lifetime;
            $sql .= " WHERE {$this->fieldActivity} >= {$time}";
        }
        return (int)$this->db->getOne($sql);
    }

    /**
     * 返回要写入 session 的额外内容，开发者应该在继承类中覆盖此方法
     *
     * 例如返回：
     * return array(
     *      'username' => $username
     * );
     *
     * 数据表中要增加相应的 username 字段。
     *
     * @param string $sessid
     *
     * @return array
     */
    function _beforeWrite($sessid)
    {
        return array();
    }
}
