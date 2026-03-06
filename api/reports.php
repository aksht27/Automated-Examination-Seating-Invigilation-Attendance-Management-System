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

$con = getConnection();
$act = $_GET['action'] ?? '';

if($act == 'seating'){
    $examId = (int)($_GET['exam_id'] ?? 0);
    $rows   = [];
    $q = $con->query("SELECT e.exam_name, e.date, r.room_no, r.building,
                             s.roll_no, s.name, s.branch, s.semester, s.section,
                             sa.seat_no, sa.row_no
                      FROM seating_allocation sa
                      JOIN exams    e ON sa.exam_id    = e.exam_id
                      JOIN rooms    r ON sa.room_id    = r.room_id
                      JOIN students s ON sa.student_id = s.student_id
                      WHERE sa.exam_id=$examId
                      ORDER BY r.room_no, sa.seat_no ASC");
    while($row = $q->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($act == 'duty'){
    $examId = (int)($_GET['exam_id'] ?? 0);
    $rows   = [];
    $q = $con->query("SELECT e.exam_name, e.date, f.name as faculty_name, f.department,
                             r.room_no, r.building, ia.duty_type
                      FROM invigilation_allocation ia
                      JOIN exams   e ON ia.exam_id    = e.exam_id
                      JOIN faculty f ON ia.faculty_id = f.faculty_id
                      JOIN rooms   r ON ia.room_id    = r.room_id
                      WHERE ia.exam_id=$examId
                      ORDER BY r.room_no ASC");
    while($row = $q->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($act == 'faculty_summary'){
    $rows = [];
    $q = $con->query("SELECT f.name, f.department, f.total_duties,
                             COUNT(ia.duty_id) as actual_duties
                      FROM faculty f
                      LEFT JOIN invigilation_allocation ia ON f.faculty_id = ia.faculty_id
                      GROUP BY f.faculty_id
                      ORDER BY actual_duties DESC");
    while($row = $q->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($act == 'attendance'){
    $examId = (int)($_GET['exam_id'] ?? 0);
    $rows   = [];
    $q = $con->query("SELECT e.exam_name, e.date, s.roll_no, s.name, s.branch, s.section,
                             a.status, r.room_no
                      FROM attendance a
                      JOIN seating_allocation sa ON a.allocation_id = sa.allocation_id
                      JOIN exams    e ON sa.exam_id    = e.exam_id
                      JOIN students s ON sa.student_id = s.student_id
                      JOIN rooms    r ON sa.room_id    = r.room_id
                      WHERE sa.exam_id=$examId
                      ORDER BY r.room_no, s.roll_no ASC");
    while($row = $q->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($act == 'utilization'){
    $rows = [];
    $q = $con->query("SELECT r.room_no, r.building, r.capacity,
                             COUNT(sa.seat_no) as used
                      FROM rooms r
                      LEFT JOIN seating_allocation sa ON r.room_id = sa.room_id
                      GROUP BY r.room_id
                      ORDER BY r.building, r.room_no ASC");
    while($row = $q->fetch_assoc()){
        $row['util_pct'] = $row['capacity'] > 0 ? round($row['used'] / $row['capacity'] * 100) : 0;
        $rows[] = $row;
    }
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($act == 'timetable'){
    $rows = [];
    $q = $con->query("SELECT es.exam_date, es.session, es.subject_name,
                             es.start_time, es.end_time, e.exam_name
                      FROM exam_schedule es
                      JOIN exams e ON es.exam_id = e.exam_id
                      ORDER BY es.exam_date ASC, es.start_time ASC");
    while($row = $q->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($act == 'list_exams'){
    $exams = [];
    $q = $con->query("SELECT exam_id, exam_name, date FROM exams ORDER BY date DESC");
    while($row = $q->fetch_assoc()) $exams[] = $row;
    echo json_encode(['success'=>true, 'data'=>$exams]);
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Invalid action']);
closeConnection($con);
