<?php
require_once("login.php");

$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

mysql_select_db($db_database);

$query = "SELECT * FROM ingredients;";

$result = mysql_query($query);
$i = 1;
$e = array();
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
    echo $row[1];
    $str = trim(mysql_real_escape_string($row[1]));
    $e[] = $str;
    $i++;
       
}

//print_r($e);
/*
$i = 1;
foreach($e as $entry) {
    $query = "UPDATE ingredients
        SET ing_name = $entry
        WHERE ing_id = $i;";
    mysql_query($query);
    $i++;
}*/
?>
