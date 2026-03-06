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
    while($row = $q->fetch_assoc()) $exams[] = $row;
    echo json_encode(['success'=>true, 'data'=>$exams]);
    exit;
}

if($method == 'GET' && $act == 'get_chart'){
    $examId = (int)$_GET['exam_id'];
    $seats  = [];
    $q = $con->query("SELECT sa.seat_no, sa.row_no, r.room_no, r.building,
                             s.roll_no, s.name, s.branch, s.semester, s.section
                      FROM seating_allocation sa
                      JOIN rooms    r ON sa.room_id    = r.room_id
                      JOIN students s ON sa.student_id = s.student_id
                      WHERE sa.exam_id=$examId
                      ORDER BY r.room_no, sa.seat_no ASC");
    while($row = $q->fetch_assoc()) $seats[] = $row;
    echo json_encode(['success'=>true, 'data'=>$seats]);
    exit;
}

if($method == 'POST' && $act == 'allocate'){
    $examId = (int)$body['exam_id'];
    $con->query("DELETE FROM seating_allocation WHERE exam_id=$examId");

    $students = [];
    $sq = $con->query("SELECT student_id FROM students ORDER BY branch, roll_no");
    while($r = $sq->fetch_assoc()) $students[] = $r['student_id'];

    $rooms = [];
    $rq = $con->query("SELECT room_id, capacity FROM rooms ORDER BY building, room_no");
    while($r = $rq->fetch_assoc()) $rooms[] = $r;

    if(empty($students) || empty($rooms)){
        echo json_encode(['success'=>false, 'message'=>'No students or rooms found']);
        exit;
    }

    $seatNo = 1; $rowNo = 1; $colNo = 1; $rIdx = 0; $rCount = 0;
    $stmt = $con->prepare("INSERT INTO seating_allocation (exam_id,student_id,room_id,seat_no,row_no) VALUES(?,?,?,?,?)");

    foreach($students as $sid){
        if($rIdx >= count($rooms)) break;
        $rm = $rooms[$rIdx];
        $stmt->bind_param("iiiii", $examId, $sid, $rm['room_id'], $seatNo, $rowNo);
        $stmt->execute();
        $rCount++; $seatNo++; $colNo++;
        if($colNo > 4){ $colNo = 1; $rowNo++; }
        if($rCount >= $rm['capacity']){
            $rIdx++; $rCount = 0; $seatNo = 1; $rowNo = 1; $colNo = 1;
        }
    }
    $stmt->close();
    echo json_encode(['success'=>true, 'message'=>count($students).' students allocated']);
    exit;
}

if($method == 'POST' && $act == 'clear'){
    $examId = (int)$body['exam_id'];
    $con->query("DELETE FROM seating_allocation WHERE exam_id=$examId");
    echo json_encode(['success'=>true, 'message'=>'Seating cleared']);
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Invalid action']);
closeConnection($con);
