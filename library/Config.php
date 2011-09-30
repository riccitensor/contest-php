<?

/**
 * holds configuration files
 */
class Config {
	
	/**
	 * hold config vars
	 */
	protected static $configVars = array();

	/**
	 * define a variable with a default value
	 * @param type $name
	 * @param type $defaultValue
	 */
	public static function def( $name, $defaultValue ) {
		if( !isset(self::$configVars[$name]) ) {
			self::$configVars[$name] = $defaultValue;
		}
	}

	/**
	 * get a configuration var or explode on failure
	 * @param type $name
	 * @return mixed
	 */
	public static function get( $name ) {
		if( !isset(self::$configVars[$name]) ) {
			throw new Exception("Config var ".$name." not defined.");
		} else {
			return self::$configVars[$name];
		}
	}

	/**
	 * set the value of a config var
	 * @param type $name
	 * @param type $value
	 */
	public static function set( $name, $value ) {
		self::$configVars[$name] = $value;
	}
}

?>
