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
    $rooms = [];
    $q     = $con->query("SELECT * FROM rooms ORDER BY building, room_no ASC");
    while($r = $q->fetch_assoc()) $rooms[] = $r;
    $totCap = $con->query("SELECT SUM(capacity) as t FROM rooms")->fetch_assoc();
    echo json_encode(['success'=>true, 'data'=>$rooms, 'total_capacity'=>(int)$totCap['t']]);
    exit;
}

if($method == 'POST' && $act == 'add'){
    $rno  = trim($body['room_no']);
    $cap  = (int)$body['capacity'];
    $bldg = trim($body['building']);

    $stmt = $con->prepare("INSERT INTO rooms (room_no,capacity,building) VALUES(?,?,?)");
    $stmt->bind_param("sis", $rno, $cap, $bldg);
    echo json_encode($stmt->execute()
        ? ['success'=>true,  'message'=>'Room added']
        : ['success'=>false, 'message'=>$stmt->error]);
    $stmt->close();
    exit;
}

if($method == 'POST' && $act == 'edit'){
    $rid  = (int)$body['room_id'];
    $rno  = trim($body['room_no']);
    $cap  = (int)$body['capacity'];
    $bldg = trim($body['building']);

    $stmt = $con->prepare("UPDATE rooms SET room_no=?,capacity=?,building=? WHERE room_id=?");
    $stmt->bind_param("sisi", $rno, $cap, $bldg, $rid);
    echo json_encode($stmt->execute()
        ? ['success'=>true,  'message'=>'Room updated']
        : ['success'=>false, 'message'=>$stmt->error]);
    $stmt->close();
    exit;
}

if($method == 'POST' && $act == 'delete'){
    $rid  = (int)$body['room_id'];
    $stmt = $con->prepare("DELETE FROM rooms WHERE room_id=?");
    $stmt->bind_param("i", $rid);
    echo json_encode($stmt->execute()
        ? ['success'=>true,  'message'=>'Room deleted']
        : ['success'=>false, 'message'=>$stmt->error]);
    $stmt->close();
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Invalid action']);
closeConnection($con);
