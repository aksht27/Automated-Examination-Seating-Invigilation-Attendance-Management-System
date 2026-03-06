<?php
ini_set('display_errors',0);
ob_start();
session_start();
ob_clean();

header('Content-Type: application/json');
require_once '../config/database.php';

if(!isset($_SESSION['logged_in'])){
    echo json_encode(['success'=>false, 'message'=>'Not logged in']);
    exit;
}

$con    = getConnection();
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$method = $_SERVER['REQUEST_METHOD'];
$act    = $body['action'] ?? $_GET['action'] ?? '';

if($method == 'GET' && $act == 'list'){
    $rows = [];
    $q = $con->query("SELECT * FROM faculty ORDER BY total_duties ASC, name ASC");
    while($r = $q->fetch_assoc()) $rows[] = $r;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($method == 'POST' && $act == 'add'){
    $name  = trim($body['name']);
    $dept  = trim($body['department']);
    $desig = trim($body['designation']);

    $stmt = $con->prepare("INSERT INTO faculty (name,department,designation,total_duties) VALUES(?,?,?,0)");
    $stmt->bind_param("sss", $name, $dept, $desig);
    if($stmt->execute()){
        $fid   = $stmt->insert_id;
        $uname = 'fac'.$fid;
        $hash  = password_hash('faculty123', PASSWORD_DEFAULT);
        $rl    = 'faculty';
        $us = $con->prepare("INSERT INTO users (username,password,role,reference_id) VALUES(?,?,?,?)");
        $us->bind_param("sssi", $uname, $hash, $rl, $fid);
        $us->execute(); $us->close();
        echo json_encode(['success'=>true, 'message'=>"Faculty added. Login: $uname / faculty123"]);
    } else {
        echo json_encode(['success'=>false, 'message'=>$stmt->error]);
    }
    $stmt->close();
    exit;
}

if($method == 'POST' && $act == 'edit'){
    $fid   = (int)$body['faculty_id'];
    $name  = trim($body['name']);
    $dept  = trim($body['department']);
    $desig = trim($body['designation']);

    $stmt = $con->prepare("UPDATE faculty SET name=?,department=?,designation=? WHERE faculty_id=?");
    $stmt->bind_param("sssi", $name, $dept, $desig, $fid);
    echo json_encode($stmt->execute()
        ? ['success'=>true,  'message'=>'Faculty updated']
        : ['success'=>false, 'message'=>$stmt->error]);
    $stmt->close();
    exit;
}

if($method == 'POST' && $act == 'delete'){
    $fid = (int)$body['faculty_id'];
    $con->query("DELETE FROM users WHERE role='faculty' AND reference_id=$fid");
    $stmt = $con->prepare("DELETE FROM faculty WHERE faculty_id=?");
    $stmt->bind_param("i", $fid);
    echo json_encode($stmt->execute()
        ? ['success'=>true,  'message'=>'Faculty deleted']
        : ['success'=>false, 'message'=>$stmt->error]);
    $stmt->close();
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Invalid action']);
closeConnection($con);
