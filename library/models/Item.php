<?

require_once( dirname(__FILE__)."/../MamaRuth.php" );

/**
 * item model
 * assume table name: item
 */
class Item extends MamaRuth {

	public static $fieldTypes = array(
		// id
		"title" => MamaRuth::DATATYPE_STRING,
		"domainid" => MamaRuth::DATATYPE_INT
		// created_at
		// updated_at
	);

}

?>
