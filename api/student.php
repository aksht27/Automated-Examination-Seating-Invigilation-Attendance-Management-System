<?php
ini_set('display_errors',0);
ob_start();
session_start();
ob_clean();

header('Content-Type: application/json');
require_once '../config/database.php';

if(!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'student'){
    echo json_encode(['success'=>false, 'message'=>'Access denied']);
    exit;
}

$con = getConnection();
$uid = (int)$_SESSION['reference_id'];
$act = $_GET['action'] ?? '';

if($act == 'info'){
    $s = $con->query("SELECT * FROM students WHERE student_id=$uid")->fetch_assoc();
    echo json_encode(['success'=>true, 'data'=>$s]);
    exit;
}

if($act == 'schedule'){
    $rows = [];
    $q = $con->query("SELECT e.exam_name, es.subject_name, es.exam_date,
                             es.start_time, es.end_time, es.session
                      FROM exam_schedule es
                      JOIN exams e ON es.exam_id = e.exam_id
                      ORDER BY es.exam_date ASC");
    while($row = $q->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($act == 'seating'){
    $rows = [];
    $q = $con->query("SELECT e.exam_name, e.date, es.subject_name,
                             es.start_time, es.end_time,
                             r.room_no, r.building, sa.seat_no, sa.row_no,
                             s.roll_no, s.name, s.branch, s.semester
                      FROM seating_allocation sa
                      JOIN exams    e  ON sa.exam_id    = e.exam_id
                      JOIN rooms    r  ON sa.room_id    = r.room_id
                      JOIN students s  ON sa.student_id = s.student_id
                      LEFT JOIN exam_schedule es ON es.exam_id = e.exam_id
                      WHERE sa.student_id=$uid
                      ORDER BY e.date ASC");
    while($row = $q->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true, 'data'=>$rows, 'student_id'=>$uid]);
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Invalid action']);
closeConnection($con);
