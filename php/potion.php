<?php
$ings = json_decode($_GET['id'], false);
$ings_string = "";

for($j=0; $j<count($ings); $j++) {
    $ings_string .= "'$ings[$j] '";
    if($j < (count($ings)-1)) { $ings_string .= ","; }
}

//echo $ings_string;

require_once("login.php");

$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

mysql_select_db($db_database);

$query = "SELECT ing_name, weight, value, location
    FROM ingredients
    WHERE ing_name IN ($ings_string);";

$result = mysql_query($query);
if (!$result) die ("Database access failed: " . mysql_error());

$ing = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
   $ing[] = $row;
}

echo json_encode($ings);
 
?>
