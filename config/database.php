<?php
ini_set('display_errors','0');
error_reporting(0);

define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','Password123');
define('DB_NAME','exam_management');

function getConnection(){
    $con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if($con->connect_error){
        header('Content-Type: application/json');
        echo json_encode(['success'=>false, 'message'=>'DB error: '.$con->connect_error]);
        exit;
    }
    $con->set_charset('utf8mb4');
    return $con;
}

function closeConnection($con){
    if($con) $con->close();
}
