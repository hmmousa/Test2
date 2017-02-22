<?php
require_once 'include/DB_Functions.php';

$db = new DB_Functions();
 
$CategoryName = $_POST['CategoryName'];
$Search = $_POST['Search'];
$UserID = $_POST['UserID'];

// get the conditions
echo $db->getProductsByCategoryID($CategoryName, $Search, $UserID);
?>