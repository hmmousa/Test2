<?php
require_once 'include/DB_Functions.php';

$db = new DB_Functions();
 
$ProductID = $_POST['ProductID'];

// get the conditions
echo $db->getProductByID($ProductID);
?>