<?php

require_once("login.php");

$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

mysql_select_db($db_database);


$query = "SELECT ing_name FROM ingredients;";
$result = mysql_query($query);
if (!$result) die ("Database access failed: " . mysql_error());

$ings = array();
while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
    $ings[] = $row[0];
}

echo json_encode($ings);

?>
