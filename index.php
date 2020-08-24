<?php

require "vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mysqli = new mysqli($_ENV['LOCALHOST'], $_ENV['DBUSER'], $_ENV['PASSWD'], $_ENV['DBNAME']);
$mysqli->set_charset("utf8");

/* check connection */
if ($mysqli->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    die();
}

$geocoder = new \OpenCage\Geocoder\Geocoder($_ENV['API_KEY']);
# set optional parameters
# see the full list: https://opencagedata.com/api#forward-opt
#

$query_results = $mysqli->query("SELECT * FROM ".$_ENV['TABLE'].";");

foreach ($query_results as $key => $list) {
    echo $list['id_marker'] . " -  \n";

    if($list['id_marker'] >= 5725){ // 5725  ate 6434
        $result = $geocoder->geocode($list['street'].' '.$list['city'].' '. $list['state'], ['language' => 'pt-Br', 'countrycode' => 'br']);

        if ($result && $result['total_results'] > 0) {
            $first = $result['results'][0];
            //echo $first['geometry']['lng'] . ';' . $first['geometry']['lat'] . ';' . $first['formatted'] . "\n";
            # 4.360081;43.8316276;6 Rue Massillon, 30020 Nîmes, Frankreich
            
            $sql = "UPDATE ".$_ENV['TABLE']." SET 
            `latitude` = '". $first['geometry']['lat'] ."',  
            `longitude` = '". $first['geometry']['lng'] ."'
                WHERE `id_marker`=".$list['id_marker'];

            if ($mysqli->query($sql) === TRUE) {
                echo "Record updated successfully \n";
            } else {
                echo "Error updating record: " . $mysqli->error;
            }
        }
    }

}