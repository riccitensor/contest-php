<?

	require( "config.php" );

//	require("library/Config.php");
//	require("library/models/Item.php" );

	$item = Item::findAllByDomainid(418);
	echo $item[0]->title;


/*	$item = new Item( array(
		"title" => "hallo",
		"domainid" => 418
	));*/


//	echo $item->title."\n";
//	$item->save();
//echo $item->delete();
/*	$item->title = "kkk";
	$item->title = "iii";
	echo $item->title."\n";

	print_r( $item );

	die("\n");*/

?>
