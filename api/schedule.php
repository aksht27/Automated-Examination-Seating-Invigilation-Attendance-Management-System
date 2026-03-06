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
    $q = $con->query("SELECT e.*, es.subject_name, es.start_time, es.end_time, es.duration
                      FROM exams e
                      LEFT JOIN exam_schedule es ON e.exam_id = es.exam_id
                      ORDER BY e.date DESC");
    while($row = $q->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($method == 'POST' && $act == 'add'){
    $ename  = trim($body['exam_name']);
    $subj   = trim($body['subject_name']);
    $edate  = $body['exam_date'];
    $stime  = $body['start_time'];
    $etime  = $body['end_time'];
    $sess   = $body['session'];
    $dur    = (strtotime($etime) - strtotime($stime)) / 60;

    $chk = $con->prepare("SELECT COUNT(*) as cnt FROM exam_schedule
                          WHERE exam_date=? AND session=? AND (start_time < ? AND end_time > ?)");
    $chk->bind_param("ssss", $edate, $sess, $etime, $stime);
    $chk->execute();
    $cf = $chk->get_result()->fetch_assoc();
    $chk->close();

    if($cf['cnt'] > 0){
        echo json_encode(['success'=>false, 'message'=>'Time conflict with existing exam!']);
        exit;
    }

    $stmt = $con->prepare("INSERT INTO exams (exam_name,date,session) VALUES(?,?,?)");
    $stmt->bind_param("sss", $ename, $edate, $sess);
    if($stmt->execute()){
        $eid = $stmt->insert_id;
        $ss  = $con->prepare("INSERT INTO exam_schedule (exam_id,subject_name,exam_date,start_time,end_time,session,duration) VALUES(?,?,?,?,?,?,?)");
        $ss->bind_param("isssssi", $eid, $subj, $edate, $stime, $etime, $sess, $dur);
        $ss->execute(); $ss->close();
        echo json_encode(['success'=>true, 'message'=>'Exam added to schedule']);
    } else {
        echo json_encode(['success'=>false, 'message'=>$stmt->error]);
    }
    $stmt->close();
    exit;
}

if($method == 'POST' && $act == 'delete'){
    $eid  = (int)$body['exam_id'];
    $stmt = $con->prepare("DELETE FROM exams WHERE exam_id=?");
    $stmt->bind_param("i", $eid);
    echo json_encode($stmt->execute()
        ? ['success'=>true,  'message'=>'Exam removed']
        : ['success'=>false, 'message'=>$stmt->error]);
    $stmt->close();
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Invalid action']);
closeConnection($con);
