<?php
/**
 * 公共组件，Mongo
 *
 */
class ZsMongoComponent extends Object {
	
	
	/**
	 * mongodb connect db
	 *
	 * @var resource
	 */
	protected static $udb;
	
	/**
	 * construct init udb
	 *
	 */
	function __construct() {
		
		if(self::$udb == false) {
			Configure::load ( 'envDefine' );
			$this->Ip = Configure::read ( 'mongo.ip' );
			$this->Port = Configure::read ( 'mongo.port' );
			$this->UserNmae = Configure::read ( 'mongo.user' );
			$this->PassWord = Configure::read ( 'mongo.pass' );
			$this->DBName = Configure::read ( 'mongo.dbn' );
		
		
			$DB = array ('IP' => $this->Ip, 'PORT' => $this->Port, 'DBNAME' => $this->DBName);
			$conn = new Mongo ( $DB ['IP'] . ':' . $DB ['PORT'] );
			$udb = $conn->$DB ['DBNAME'];
			self::$udb = $udb;
		}
	}
	
	
	/**
	 * MongoDB Insert Data
	 *
	 * @param array $array
	 * @param string $tabName
	 * @return bool
	 */
	function insert($array,$tabName){
		$udb = self::$udb;
		$result = $udb->$tabName;
		$result->insert ( $array );
		return true;
	} 
	
	
	/**
	 * MongoDB Count
	 *
	 * @param array $qurryArr
	 * @param string $tabName
	 * @return array
	 */
	function counts($qurryArr=array(),$tabName){
		$udb = self::$udb;
		$result = $udb->$tabName;
		if(count($qurryArr)>0){
			$results = $result->count($qurryArr);
		}else{
			$results = $result->count();
		}
		return $results;
	}	
	
	
	/**
	 * MongoDB findAll
	 *
	 * @param string $tabName
	 * @return array
	 */
	function findAll($tabName) {
		$udb = self::$udb;
		$result = $udb->$tabName;
		$results = $result->find ();
		foreach ($results as $k=>$v){
			$backArr[]=$v;
		}
		return $backArr;
	}
	
}
	
