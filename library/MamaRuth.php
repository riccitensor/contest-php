<?

/**
 * parent model class
 */
abstract class MamaRuth {

	/**
	 * possible datatypes
	 */
	const DATATYPE_INT = 1;
	const DATATYPE_STRING = 2;
	const DATATYPE_FLOAT = 3;
	const DATATYPE_DATE = 4;

	/**
	 * possible datastore engines
	 */
	const DATASTORE_REDIS = 1;
	const DATASTORE_MYSQL = 2;

	/**
	 * holds the current data
	 */
	protected $data = array();

	/**
	 * hold the object's changes
	 */
	protected $changedFields = array();

	/**
	 * field type configuration
	 * should be link this to the db scheme?
	 */
	// static
	public static $fieldTypes = array();

	/**
	 * datastores to use for the current class
	 * in the order, the will be asked in finder
	 */
	protected static $dataStores = array( self::DATASTORE_REDIS, self::DATASTORE_MYSQL );

	/**
	 * datastore engine names
	 */
	protected static $dataStoreEngines = array(
//		self::DATASTORE_REDIS => "DataStoreRedis",
		self::DATASTORE_MYSQL => "DataStoreMySql"
	);

	/**
	 * description
	 * @param type $name description
	 * @return type
	 */
	public function __construct( array $data = null, $initialized = false ) {
		if( $data ) {
			if( $initialized == true ) {
				$this->data = $data;
			} else {
				foreach( $data as $name => $value ) {
					$this->$name = $value;
				}
			}
		}
	}

	/**
	 * getter for model attributes
	 * @param string $name name of the attribute to get
	 * @return attribute value
	 */
	public function __get( $name ) {
		if( isset( $this->data[$name] ) ) {
			$value = $this->data[$name];
			if( isset(static::$fieldTypes[$name]) ) {
				switch( static::$fieldTypes[$name] ) {
					case self::DATATYPE_INT: $value = intval($value); break;
					case self::DATATYPE_STRING: $value = (string) $value; break;
					case self::DATATYPE_FLOAT: $value = floatval($value); break;
				}
			}
			return $value;
		} else {
			return null;
		}
	}

	/**
	 * setter for attributes
	 * @param type $name
	 * @param type $value
	 */
	public function __set( $name, $value ) {
		if( !array_key_exists( $name, $this->changedFields ) ) {
			$this->changedFields[$name] = $this->$name;
		}

		$this->data[$name] = $value;
	}

	/**
	 * saves the current object
	 */
	public function save() {
		if( count( $this->changedFields ) > 0 ) {
			$this->updated_at = time();
			if( !$this->created_at ) $this->created_at = time();
			foreach( static::getDataStores() as $engine ) {
				$this->id = $engine::saveObject( get_class($this), $this->data );
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * delets the current object from the database
	 */
	public function delete() {
		foreach( static::getDataStores() as $engine ) {
			$engine::deleteObject( get_class($this), $this->data );
		}
	}

	/**
	 * finder with id
	 */
	public static function find( $id ) {
		$obj = null;
		$currentClass = get_called_class();
		foreach( static::getDataStores() as $engine ) {
			$obj = $engine::findObject( $currentClass, array("conditions" => array("id" => $id), "limit" => 2) );
			if( $obj ) break;
		}

		if( $obj && isset($obj[0]) ) return new $currentClass( $obj[0], true );
		return null;
	}

	/**
	 * catch dynamic finders
	 */
	public static function __callStatic($name, $arguments) {
		$options = array("conditions" => array());
		if( strpos( $name, "findAllBy" ) === 0 ) {
			$name = preg_replace('/^findAllBy/', "", $name);
		} elseif( strpos( $name, "findBy" ) === 0 ) {
			$options["limit"] = 1;
			$name = preg_replace('/^findBy/', "", $name);
		} else {
			throw new Exception( "Unknown static function ".$name." by ".get_called_class() );
		}

		$conds = preg_split( '/And/', $name );
		for( $i = 0; $i < count($conds); $i++ ) {
			$options["conditions"][strtolower($conds[$i])] = $arguments[$i];
		}

		$objs = array();
		$currentClass = get_called_class();
		foreach( static::getDataStores() as $engine ) {
			$objs = $engine::findObject( $currentClass, $options );
			if( $objs ) break;
		}

		if( isset($options["limit"]) && $options["limit"] === 1 ) {
			if( isset($objs[0]) ) {
				return new $currentClass( $objs[0], true );
			} else {
				return null;
			}
		} else {
			for( $j = 0; $j < count($objs); $j++ ) $objs[$j] = new $currentClass($objs[$j], true);
			return $objs;
		}
	}

	/**
	 * get all store engines of the current class
	 * @return array of store engine names
	 */
	protected static function getDataStores() {
		$stores = array();
		foreach( static::$dataStores as $store ) {
			if( isset(self::$dataStoreEngines[$store]) ) {
				if( !class_exists(self::$dataStoreEngines[$store]) ) {
					require(__DIR__."/storeengines/".self::$dataStoreEngines[$store].".php");
				}
				$stores[] = self::$dataStoreEngines[$store];
			}
		}
		return $stores;
	}
}

?>
