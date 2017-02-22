<?php
require_once 'include/DB_Functions.php';

$db = new DB_Functions();
 
// get the conditions
echo $db->getConditions();
?>