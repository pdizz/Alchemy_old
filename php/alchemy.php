<?php
require_once("login.php");

try {
    $dbh = new PDO("mysql:host=$db_hostname;dbname=$db_database", 
                $db_username, $db_password);
    
}
catch(PDOException $e) {
    echo $e->getMessage();
}

/********************Get realm input********************/

if (isset($_GET['realm'])) {
    $p = cleanList($_GET['realm']);

    switch($p[0]) {
        case 'oblivion': 
            $ing_table = 'oblivion_ingredients';
            $eff_table = 'oblivion_effects';
            $xref_table = 'oblivion_ing_eff_xref';
            break;
        case 'morrowind':
            $ing_table = 'morrowind_ingredients';
            $eff_table = 'morrowind_effects';
            $xref_table = 'morrowind_ing_eff_xref';
            break;
        default:
            $ing_table = 'skyrim_ingredients';
            $eff_table = 'skyrim_effects';
            $xref_table = 'skyrim_ing_eff_xref';
            break;
    }
}
else {
    $ing_table = 'skyrim_ingredients';
    $eff_table = 'skyrim_effects';
    $xref_table = 'skyrim_ing_eff_xref';

}

/*******************Ingredients***************************/

if(isset($_GET['ing'])) {
    $ings = cleanList($_GET['ing']);
    if ($ings[0] == 'all') {
        $sth = $dbh->prepare("SELECT ing_name FROM $ing_table;");
        $sth->execute();
        
        //output
//        if(isset($_GET['csv'])) {
//            while ($row = $sth->fetch()) {
//                echo $row['ing_name'] . ',';
//            }
//        }
        
        if(isset($_GET['json'])) { //JSON for ajax app
            $ingList = array();
            while($row = $sth->fetch()) {
                $ingList[] = $row['ing_name'];
            }
            echo json_encode($ingList);
        }
        else {
            while ($row = $sth->fetch()) {
                echo '<li>' . $row['ing_name'] . '</li>';
            }
        }
		//end output
       
    } else {
        
        $ingsQuery = implode(',', array_fill(0, count($ings), '?'));        
        $sth = $dbh->prepare("SELECT e.eff_name, count( * ) AS value
                FROM $ing_table i
                INNER JOIN $xref_table ie ON ie.ing_id = i.ing_id
                INNER JOIN $eff_table e ON e.eff_id = ie.eff_id
                WHERE i.ing_name
                IN ($ingsQuery)
                GROUP BY e.eff_name
                ORDER BY e.eff_name;");

        foreach ($ings as $ing => $id) {
            $sth->bindValue(($ing + 1), $id);
        }
        
        $sth->execute();
        //output
        if(isset($_GET['json'])) {
            $effList = array();
            while($row = $sth->fetch()) {
                if($row['value'] > 1) {
                    $effList[] = $row;
                }
            }
            echo json_encode($effList);
        }
        else {
            while ($row = $sth->fetch()) {
                if ($row['value'] > 1) {
                    echo '<li>' . $row['eff_name'] . '</li>';
                }
            }
        }
		//end output
        
    }
}

/**********************EFFECTS*****************************************/

else if (isset($_GET['eff'])) {
    $effs = cleanList($_GET['eff']);
    if($effs[0] == 'all') {
        $sth = $dbh->prepare("SELECT eff_name FROM $eff_table;");
        $sth->execute();
        
        //output
        if (isset($_GET['json'])) {
            $effList = array();
            while ($row = $sth->fetch()) {
                $effList[] = $row['eff_name'];
            }
            echo json_encode($effList);
        }
        else {
            while ($row = $sth->fetch()) {
                echo '<li>' . $row['eff_name'] . '</li>';
            }
        }
		//end output
    }
    else {
        $effsQuery = implode(',', array_fill(0, count($effs), '?'));

        $sth = $dbh->prepare("SELECT ing_name, eff_name, weight, value, location
            FROM $eff_table AS e
            INNER JOIN $xref_table AS ie
            ON e.eff_id = ie.eff_id
            INNER JOIN $ing_table AS i
            ON i.ing_id = ie.ing_id
            WHERE eff_name IN ($effsQuery)
            ORDER BY ing_name;");
        foreach ($effs as $eff => $id) {
            $sth->bindValue(($eff + 1), $id);
        }
        $sth->execute();
        
        $recipe = array();
        while ($row = $sth->fetch()) {
            $recipe[] = $row;
        }
        //var_dump($recipe);
        
        for($i=0; $i<count($recipe); $i++) {
            $recipe[$i]['link'] = false;
        }
        //Reverse-iterate through array to check for duplicate ing_name.
        for($i=count($recipe)-1; $i>0; $i--) {
            if($recipe[$i]['ing_name'] == $recipe[$i-1]['ing_name']) { //check for duplicate. **array must be sorted by ing_name**
                $recipe[$i-1]['eff_name'] .= ', '.$recipe[$i]['eff_name']; //Combine eff_name of duplicates
                $recipe[$i-1]['link'] = true;
                array_slice($recipe, $i); //remove duplicate index
            }
        }

        //var_dump($recipe);
        
        //output
        
        if(isset($_GET['json'])) {
            echo json_encode($recipe);
        }
        else {
            foreach ($recipe as $ingredient) {
                if ($ingredient['link']) {
                    echo '<dt class="link">**' . $ingredient['ing_name'] . '**</dt>';
                    echo '<dd class="link">' . $ingredient['eff_name'] . '</dd>';
                    echo '<dd class="link">' . $ingredient['weight'] . '</dd>';
                    echo '<dd class="link">' . $ingredient['value'] . '</dd>';
                    echo '<dd class="link">' . $ingredient['location'] . '</dd>';
                } else {
                    echo '<dt>' . $ingredient['ing_name'] . '</dt>';
                    echo '<dd>' . $ingredient['eff_name'] . '</dd>';
                    echo '<dd>' . $ingredient['weight'] . '</dd>';
                    echo '<dd>' . $ingredient['value'] . '</dd>';
                    echo '<dd>' . $ingredient['location'] . '</dd>';
                }
            }
        }
        /*
        //output
        if (isset($_GET['csv'])) {
            while ($row = $sth->fetch()) {
                echo $row['ing_name'] . '`';
                echo $row['eff_name'] . '`';
                echo $row['weight'] . '`';
                echo $row['value'] . '`';
                echo $row['location'] . '~';
            }
        }
        else {
            while ($row = $sth->fetch()) {
                echo '<dt>' . $row['ing_name'] . '</dt>';
                echo '<dd>' . $row['eff_name'] . '</dd>';
                echo '<dd>' . $row['weight'] . '</dd>';
                echo '<dd>' . $row['value'] . '</dd>';
                echo '<dd>' . $row['location'] . '</dd>';
            }
        }
		//end output*/
    }
}

/**************************INSTRUCTIONS***********************************/

else {
    echo "<h1>INSTRUCTIONS</h1>";
    echo "<h2>Syntax:</h2>";
    echo "<code>?realm=oblivion|morrowind|skyrim</code><p>Defaults to Skyrim</p>";
    echo "<code>?ing='all'|list</code><p>'all' returns a list of all the ingredients in the realm.</p>";
    echo "<p>If given a list of ingredients, returns all effects shared by more than one ingredient</p>";
    echo "<code>?eff='all'|list</code><p>'all' returns a list of all the possible effects.</p>";
    echo "<p>If given a list of effects (max 3), will return the matching ingredients with their weight, value, and location.</p>";
}

/********************Functions************************************/

function cleanList($str) {
    $str = strtolower($str);
    $str = explode(',', $str);

    $str = preg_replace('/[\'\"]/', '', $str);
    for ($i = 0; $i < count($str); $i++) {
        $str[$i] = trim($str[$i]);
    }
    return $str;
}

function encodeQuery($arr) {
    $str = '';
    for ($i = 0; $i < count($arr); $i++) {
        $str .= "'$arr[$i]'";
        if ($i < (count($arr) - 1)) {
            $str .= ",";
        }
    }
    return $str;
}

?>

