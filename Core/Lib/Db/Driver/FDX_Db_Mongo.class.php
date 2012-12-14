<?php

class FDX_Db_Mongo
{

    /**
     * MongoDB object
     * @var MongoDB
     */
    private $db = NULL;

    /**
     * current mongo collection
     * @var MongoCollection
     */
    private $c  = NULL;

    /**
     * whether to use a mongo replica set
     * if it is an array, the array should
     * contain host and port info of each node,
     * in the form of :
     * array(
     *   array("host"=>"127.0.0.1", "port"=>27018),
     *   ...
     * )
     * @var Boolean|Array
     */
    private $replicaSet = FALSE;

    /**
     * slaveOkay setting
     * @var Boolean
     */
    private $slaveOkay = FALSE;

    /**
     * Set to FALSE if you do NOT want to use
     * a persistent connection. Otherwise
     * use a string to indicate the name
     * of the persistent connection.
     * @var Boolean|String
     */
    private $persist = 'fdx_mongo';
	
	/*
	 * query time out, in millisec
	 */
	private $queryTimeOut = 2500;

    /**
     * Throws FDX_Exception if $config is invalid.<br/>
     * Throws MongoConnectionException if it fails to connect to MongoDB.
     * @param Array $config
     */
    public function __construct($config)
	{
		if(!is_array($config) )
		{
			throw new FDX_Exception("Invalid config for MongoDB initializition");
		}
		$rps = '';
		if( is_array($config['replica_set']) )
		{
			$this->replicaSet = $config['replica_set'];
			foreach($this->replicaSet as $v)
			{
				$rps = ','.$v['host'].':'.$v['port'];
			}
		}
		$this->slaveOkay = !empty($config['slave_ok'])?:FALSE;
		$this->persist   = !empty($config['persist'])?$config['persist']:FALSE;
		$this->queryTimeOut = $config['queryTimeOut']?:2500;

		$conString = 'mongodb://';
		if($config['user'] && $config['pwd'])
		{
			$conString .= $config['user'].':'.$config['pwd'].'@';
		}
		$conString .= $config['master']['host'].':'.$config['master']['port'].$rps;
		$conString .= '/'.$config['db'];

		$this->db = new Mongo($conString,
				array('persist'=>$this->persist,
		      'replicaSet'=>  !(FALSE===$this->replicaSet),
			  'connect' => $config['connect']?:TRUE,
			  'timeout' => $config['timeout']?:60
		));
		$this->db = $this->db->selectDB($config['db']);
		$this->db->setSlaveOkay($this->slaveOkay);
    }

    /**
     * Set the current collection it's operating on.
     * @param String $name collection name
     */
    public function setCollection($name)
	{
		$this->c = $this->db->selectCollection($name);
    }

    /**
     * Get one document
     * @param Array $cfg search criteria
     * @return Array  NULL on failure
     */
    public function get( $cfg=array())
	{
		return $this->c->findOne($cfg);
    }

    /**
     * get multiple documents matching the search criteria
     * @param type $cfg search criteria
     * @param type $opt additional options,e.g.:array('limit'=>10,'skip'=>20,
     *  'sort'=>array('name'=>1,'_id'=>-1))
     * @return MongoCursor
     */
    public function getList($cfg, $opt)
	{
		$cur = $this->c->find($cfg);
		if(isset($opt['sort']) && is_array($opt['sort']))
		{
			$cur = $cur->sort($opt['sort']);
		}
		if( isset($opt['skip']) && is_numeric($opt['skip']))
		{
			$cur = $cur->skip($opt['skip']);
		}
		if( isset($opt['limit']) && is_numeric($opt['limit']))
		{
			$cur = $cur->limit($opt['limit']);
		}
		return $cur->timeout($this->queryTimeOut);
    }

    /**
     * Insert a document into collection $col.<br/>
     * Throws MongoCursorException if the "safe" option is true and the insert fails.
     * @param Array $cfg values array('k'=>'val',...)
     * @param Array $opt options, e.g.:array('safe'=>TRUE)
     * @return mixed _id field of the document just inserted or else.
     */
    public function insert($cfg, $opt)
	{
		if( empty($cfg['_id']) )
		{
			$cfg['_id'] = new MongoId();
		}

		$ret = $this->c->insert($cfg, $opt);
		if( !empty($opt['safe']) )
		{
			return $cfg['_id'];
		}
		else
		{
			return $ret;
		}
    }

    /**
     * Update a collection
     * @param Array $crt
     * @param Array $new
     * @param Array $opt
     * @return Array update info
     */
    public function update($crt, $new, $opt)
    {
		return $this->c->update($crt, $new, $opt);
    }

    /**
     * Remove one or more documents.
     * @param Array $crt
     * @param Array $opt
     * @return mixed
     */
    public function remove($crt, $opt)
	{
		return $this->c->remove($crt, $opt);
    }

    /**
     * Save a document back to the collection.
     * @param Array $doc
     * @param Array $opt
     * @return mixed
     */
    public function save($doc, $opt)
	{
		return $this->c->save($doc,$opt);
    }
	
	public function command( $cmd ){
		return $this->db->command( $cmd );
	}
}

