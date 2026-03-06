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

if($method == 'GET' && $act == 'get_sheet'){
    $examId = (int)$_GET['exam_id'];
    $roomId = (int)$_GET['room_id'];
    $rows   = [];
    $q = $con->query("SELECT sa.allocation_id, sa.seat_no, s.student_id,
                             s.roll_no, s.name, s.branch,
                             COALESCE(a.status,'Absent') as status
                      FROM seating_allocation sa
                      JOIN students s ON sa.student_id = s.student_id
                      LEFT JOIN attendance a ON a.allocation_id = sa.allocation_id
                      WHERE sa.exam_id=$examId AND sa.room_id=$roomId
                      ORDER BY sa.seat_no ASC");
    while($row = $q->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($method == 'GET' && $act == 'get_rooms'){
    $examId = (int)$_GET['exam_id'];
    $rooms  = [];
    $q = $con->query("SELECT DISTINCT r.room_id, r.room_no, r.building
                      FROM seating_allocation sa
                      JOIN rooms r ON sa.room_id = r.room_id
                      WHERE sa.exam_id=$examId
                      ORDER BY r.room_no ASC");
    while($row = $q->fetch_assoc()) $rooms[] = $row;
    echo json_encode(['success'=>true, 'data'=>$rooms]);
    exit;
}

if($method == 'POST' && $act == 'mark'){
    $records = $body['records'] ?? [];
    $saved   = 0;
    $stmt = $con->prepare("INSERT INTO attendance (allocation_id,status) VALUES(?,?)
                           ON DUPLICATE KEY UPDATE status=VALUES(status)");
    foreach($records as $rec){
        $allocId = (int)$rec['allocation_id'];
        $status  = in_array($rec['status'], ['Present','Absent']) ? $rec['status'] : 'Absent';
        $stmt->bind_param("is", $allocId, $status);
        $stmt->execute();
        $saved++;
    }
    $stmt->close();
    echo json_encode(['success'=>true, 'message'=>"$saved records saved"]);
    exit;
}

if($method == 'GET' && $act == 'list_exams'){
    $exams = [];
    $q = $con->query("SELECT exam_id, exam_name, date, session FROM exams ORDER BY date DESC");
    while($row = $q->fetch_assoc()) $exams[] = $row;
    echo json_encode(['success'=>true, 'data'=>$exams]);
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Invalid action']);
closeConnection($con);
