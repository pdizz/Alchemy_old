<?php

$effs = json_decode(stripslashes($_GET['id']), false);
$effs_string = "";

for($j=0; $j<count($effs); $j++) {
    $effs_string .= "'$effs[$j] '";
    if($j < (count($effs)-1)) { $effs_string .= ","; }
}

//echo $effs_string;

require_once("login.php");

$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

mysql_select_db($db_database);

$query = "SELECT ing_name, eff_name, weight, value, location
FROM effects AS e
INNER JOIN ing_eff_xref AS ie
ON e.eff_id = ie.eff_id
INNER JOIN ingredients AS i
ON i.ing_id = ie.ing_id
WHERE eff_name IN ($effs_string)
ORDER BY ing_name;";

$result = mysql_query($query);
if (!$result) die ("Database access failed: " . mysql_error());

$eff = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
   $eff[] = $row;
}

echo json_encode($eff);

?>