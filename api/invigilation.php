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

if($method == 'GET' && $act == 'list_exams'){
    $exams = [];
    $q = $con->query("SELECT exam_id, exam_name, date, session FROM exams ORDER BY date DESC");
    while($r = $q->fetch_assoc()) $exams[] = $r;
    echo json_encode(['success'=>true, 'data'=>$exams]);
    exit;
}

if($method == 'GET' && $act == 'get_duties'){
    $examId = (int)$_GET['exam_id'];
    $duties = [];
    $q = $con->query("SELECT ia.duty_id, f.name as faculty_name, f.department,
                             r.room_no, r.building, ia.duty_type
                      FROM invigilation_allocation ia
                      JOIN faculty f ON ia.faculty_id = f.faculty_id
                      JOIN rooms   r ON ia.room_id    = r.room_id
                      WHERE ia.exam_id=$examId
                      ORDER BY r.room_no ASC");
    while($r = $q->fetch_assoc()) $duties[] = $r;
    echo json_encode(['success'=>true, 'data'=>$duties]);
    exit;
}

if($method == 'POST' && $act == 'allocate'){
    $examId = (int)$body['exam_id'];
    $con->query("DELETE FROM invigilation_allocation WHERE exam_id=$examId");

    $rooms = [];
    $rq = $con->query("SELECT room_id FROM rooms ORDER BY building, room_no");
    while($r = $rq->fetch_assoc()) $rooms[] = $r['room_id'];

    $staff = [];
    $fq = $con->query("SELECT faculty_id FROM faculty ORDER BY total_duties ASC");
    while($r = $fq->fetch_assoc()) $staff[] = $r['faculty_id'];

    if(empty($staff) || empty($rooms)){
        echo json_encode(['success'=>false, 'message'=>'No faculty or rooms found']);
        exit;
    }

    $stmt = $con->prepare("INSERT INTO invigilation_allocation (exam_id,faculty_id,room_id,duty_type) VALUES(?,?,?,?)");
    $assigned = 0;
    foreach($rooms as $i => $rid){
        $fid  = $staff[$i % count($staff)];
        $type = ($i == 0) ? 'Chief Invigilator' : 'Invigilator';
        $stmt->bind_param("iiis", $examId, $fid, $rid, $type);
        $stmt->execute();
        $con->query("UPDATE faculty SET total_duties=total_duties+1 WHERE faculty_id=$fid");
        $assigned++;
    }
    $stmt->close();
    echo json_encode(['success'=>true, 'message'=>"Duties assigned for $assigned rooms"]);
    exit;
}

if($method == 'POST' && $act == 'clear'){
    $examId = (int)$body['exam_id'];
    $con->query("DELETE FROM invigilation_allocation WHERE exam_id=$examId");
    echo json_encode(['success'=>true, 'message'=>'Duties cleared']);
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Invalid action']);
closeConnection($con);
