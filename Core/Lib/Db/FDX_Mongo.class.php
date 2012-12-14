<?php

class FDX_Mongo
{

	private static $_instance = NULL;
	private $backend = NULL;

	static function getInstance ( $config=NULL )
	{
		if ( is_null ( self::$_instance ) )
		{
			if ( !is_array ( $config ) )
				$config = Core::getConfig ( 'mongodb' );
			try
			{
				self::$_instance = new self ( $config );
			}
			catch ( Exception $ex )
			{
				self::$_instance =  new FDX_NullMongo($ex);
			}
		}
		return self::$_instance;
	}

	private function __construct ( $config )
	{
		$this->backend = new FDX_Db_Mongo ( $config );
	}

	/**
	 * Get a document by its id
	 * @param String $col
	 * @param miex $id
	 * @return array NULL on failure
	 */
	public function getById ( $col, $id )
	{
		$this->backend->setCollection ( $col );
		return $this->backend->get ( array('_id' => $id) );
	}

	/**
	 * get multiple documents matching the search criteria
	 * @param type $col collection name
	 * @param type $cfg search criteria
	 * @param type $opt additional options,e.g.:array('limit'=>10,'skip'=>20,
	 *  'sort'=>array('name'=>1,'_id'=>-1))
	 * @return Array
	 */
	public function getList ( $col, $cfg, $opt=array('limit' => 10, 'skip' => 0, 'key' => '_id') )
	{
		$this->backend->setCollection ( $col );
		$cur = $this->backend->getList ( $cfg, $opt );
		$ret = array();
		if ( empty ( $opt['key'] ) )
		{
			foreach ( $cur as $key => $val )
			{
				$ret[$key] = $val;
			}
		}
		else
		{
			foreach ( $cur as $val )
			{
				$ret[$val[$opt['key']] . ''] = $val;
			}
		}
		return $ret;
	}

	/**
	 * Get the number of matching documents.
	 * @param String $col collection name
	 * @param Array $cfg search criteria
	 * @param Array $opt optional.
	 * @return Int number of documents found.
	 */
	public function count ( $col, $cfg, $opt=array() )
	{
		$this->backend->setCollection ( $col );

		$cur = $this->backend->getList ( $cfg, $opt );

		return $cur->count ();
	}

	/**
	 * Insert a document into collection $col.<br/>
	 * Throws MongoCursorException if the "safe" option is true and the insert fails.
	 * @param String $col collection name
	 * @param Array $vals values array('k'=>'val',...)
	 * @param Array $opt options, defaults to array('safe'=>TRUE)
	 * @return mixed _id field of the document just inserted if safe is TRUE, or else.
	 */
	public function insert ( $col, $vals, $opt=array('safe' => FALSE) )
	{
		$this->backend->setCollection ( $col );
		return $this->backend->insert ( $vals, $opt );
	}

	/**
	 * Update a collection
	 * @param String $col collection name
	 * @param Array $crt
	 * @param Array $new
	 * @param Array $opt
	 * @return Integer|Boolean return the number of documents updated on
	 *  success, or FALSE on failure.<br/>
	 *  Notice: if there are more than on documents matching the criteria but
	 *  'multiple' is set to FALSE, FALSE will be returned.
	 */
	public function update ( $col, $crt, $new, $opt=array('multiple' => FALSE,
		'upsert' => FALSE, 'safe' => FALSE) )
	{
		$this->backend->setCollection ( $col );
		$ret = $this->backend->update ( $crt, $new, $opt );
		if ( !empty ( $opt['safe'] ) )
			return $ret['n'];
		return 1 == $ret['ok'];
	}

	/**
	 * remove documents
	 * @param String $col collection name
	 * @param Array $crt
	 * @param Array $opt
	 * @return Integer|Boolean return the number of documents removed on
	 *  success, or FALSE on failure
	 */
	public function remove ( $col, $crt, $opt=array('justOne' => FALSE, 'safe' => FALSE) )
	{
		$this->backend->setCollection ( $col );
		$ret = $this->backend->remove ( $crt, $opt );
		if ( !empty ( $opt['safe'] ) )
		{
			return $ret['n'];
		}
		return $ret;
	}

	/**
	 * Save a document back to the collection.
	 * @param String $col collection name
	 * @param Array $doc
	 * @param Array $opt
	 * @return Boolean
	 */
	public function save ( $col, $doc, $opt=array('safe' => FALSE) )
	{
		$this->backend->setCollection ( $col );
		$ret = $this->backend->save ( $doc, $opt );
		if ( !empty ( $opt['safe'] ) )
			return $ret;
		return 1 == $ret['ok'];
	}
	
	public function command( $cmd )
	{
		return $this->backend->command( $cmd );
	}

}

class FDX_NullMongo
{
	public $isCrap = TRUE;
	function __construct ( $ex=NULL )
	{
		$this->exception = $ex;
	}
	function count(){return 0;}
	function getById(){return array();}
	function getList(){return array();}
	function insert(){return FALSE;}
	function remove(){return FALSE;}
	function save(){return FALSE;}
	function update(){return FALSE;}
	function command(){return FALSE;}
}