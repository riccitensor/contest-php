<?

// config vars
Config::def("mysql/host", "localhost");
Config::def("mysql/user", "root");
Config::def("mysql/password", "");
Config::def("mysql/database", "contest");

/**
 * store engine for mysql data
 */
class DataStoreMySql {

	/**
	 * hold current conenction
	 */
	protected static $currentMysqlResource = null;

	/**
	 * Save in mysql database
	 */
	static public function saveObject( $class, $data ) {
		$data = self::prepareValues( $class, $data );

		$sql = "INSERT INTO ".self::getTablename( $class )." (".implode(array_keys($data), ", ").") VALUES (";
		$sql.= '"'.implode($data, '", "').'"';
		$sql.= ") ";

		if(isset($data["id"])) unset($data["id"]);
		$updateFields = array();
		foreach( $data as $key => $value ) $updateFields[] = ''.$key.' = "'.$value.'"';
		$sql.= "ON DUPLICATE KEY UPDATE ".implode($updateFields, ", ");

		$link = self::initConnection();
		$res = mysql_query( $sql, $link );
		if( !$res ) throw new Exception( mysql_error() );

		return mysql_insert_id( $link );
	}

	/**
	 * Delete in mysql database
	 */
	static public function deleteObject( $class, $data ) {		
		$data = self::prepareValues( $class, $data );

		if( !isset($data["id"]) ) throw new Exception("Unique id required for delete operation");

		$sql = "DELETE FROM ".self::getTablename( $class )." WHERE id = ".$data["id"];
		$link = self::initConnection();
		$res = mysql_query( $sql, $link );
		if( !$res ) throw new Exception( mysql_error() );
	}

	/**
	 * encapsulate finder function
	 */
	public static function findObject( $class, $options ) {
		$sql = "SELECT * FROM ".self::getTablename($class)." WHERE ";

		$cond = self::prepareValues( $class, $options["conditions"] );
		$whereFields = array();
		foreach( $cond as $key => $value ) $whereFields[] = ''.$key.' = "'.$value.'"';
		$sql.= implode($whereFields, " AND ");

		if( isset($options["limit"]) ) $sql.= " LIMIT ".$options["limit"];

		$link = self::initConnection();
		$res = mysql_query( $sql, $link );
		if( !$res ) throw new Exception( mysql_error() );

		$data = array();
		while( $row = mysql_fetch_assoc($res) ) $data[] = $row;

		mysql_free_result($res);
		return $data;
	}

	/**
	 * get a tablename, based on the class name
	 */
	static protected function getTablename( $class ) {
		return strtolower($class)."s";
	}

	/**
	 * prepare table values, based on the models' definition
	 */
	static protected function prepareValues( $class, $data ) {
		$values = array();
		foreach( $data as $key => $value ) {
			if( $key == "id" || (isset($class::$fieldTypes[$key]) && $class::$fieldTypes[$key] == MamaRuth::DATATYPE_INT ) ) {
				$values[$key] = intval($value);
			} elseif( $key == "updated_at" || $key == "created_at" ||
			    (isset($class::$fieldTypes[$key]) && $class::$fieldTypes[$key] == MamaRuth::DATATYPE_DATE ) ) {
				$values[$key] = date("Y-m-d H:i:s", $value);
			} elseif( (isset($class::$fieldTypes[$key]) && $class::$fieldTypes[$key] == MamaRuth::DATATYPE_STRING ) ) {
				$values[$key] = mysql_escape_string($value);
			}
		}
		return $values;
	}

	/**
	 * get current connection
	 */
	static protected function initConnection() {
		if( !self::$currentMysqlResource ) {
			self::$currentMysqlResource = mysql_connect( Config::get("mysql/host"), Config::get("mysql/user"), Config::get("mysql/password") );
			mysql_select_db( Config::get("mysql/database"), self::$currentMysqlResource );
		}
		return self::$currentMysqlResource;
	}
}

?>
