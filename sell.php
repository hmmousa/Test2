<?php
 
require_once 'include/DB_Functions.php';

$db = new DB_Functions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_POST['name']) && isset($_POST['desc']) && isset($_POST['price']) && isset($_POST['category']) && isset($_POST['condition']) && isset($_POST['image']) && isset($_POST['uid'])) {
 
    // receiving the post params
    $name = $_POST['name'];
    $desc = $_POST['desc'];
    $price = $_POST['price'];
	$category = $_POST['category'];
	$condition = $_POST['condition'];
	$image = $_POST['image'];
	$uid = $_POST['uid'];
   
	// create a new 
	$product = $db->addProduct($name, $desc, $price, $category, $condition, $image, $uid);
	if ($product) {
		// product stored successfully
		$response["error"] = FALSE;
		echo json_encode($response);
	} else {
		// user failed to store
		$response["error"] = TRUE;
		$response["error_msg"] = "Unknown error occurred in adding!";
		echo json_encode($response);
	}
} else {
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameter is missing!";
    echo json_encode($response);
}
?>