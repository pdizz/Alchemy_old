<?php

$ings = json_decode(stripslashes($_GET['id']), false);
$ing_string = "";

for($i=0; $i < count($ings); $i++) {
    $ing_string .= "'$ings[$i]'";
    if($i < (count($ings)-1)) { $ing_string .= ","; }
}

//echo $ing_string;

require_once("login.php");

$db_server = mysql_connect($db_hostname, $db_username, $db_password);
if (!$db_server) die("Unable to connect to MySQL: " . mysql_error());

mysql_select_db($db_database);

$query = "SELECT e.eff_name, count( * ) AS value
FROM ingredients i
INNER JOIN ing_eff_xref ie ON ie.ing_id = i.ing_id
INNER JOIN effects e ON e.eff_id = ie.eff_id
WHERE i.ing_name
IN ($ing_string)
GROUP BY e.eff_name
ORDER BY e.eff_name;";

$result = mysql_query($query);
if (!$result) die ("Database access failed: " . mysql_error());

$eff = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
   $eff[] = $row;
}

echo json_encode($eff);

?>
