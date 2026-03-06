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
    $rows   = [];
    $role   = $_SESSION['role'];
    $baseQ  = "SELECT rl.*, e.exam_name, e.date,
                      f1.name as requestor, COALESCE(f2.name,'N/A') as replacement_name, r.room_no
               FROM replacement_log rl
               JOIN exams    e  ON rl.exam_id             = e.exam_id
               JOIN faculty  f1 ON rl.original_faculty_id = f1.faculty_id
               LEFT JOIN faculty f2 ON rl.replacement_faculty_id = f2.faculty_id
               JOIN rooms    r  ON rl.room_id             = r.room_id";

    if($role == 'faculty'){
        $fid = (int)$_SESSION['reference_id'];
        $q   = $con->query($baseQ." WHERE rl.original_faculty_id=$fid OR rl.replacement_faculty_id=$fid ORDER BY rl.requested_at DESC");
    } else {
        $q = $con->query($baseQ." ORDER BY rl.requested_at DESC");
    }
    while($row = $q->fetch_assoc()) $rows[] = $row;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($method == 'POST' && $act == 'request'){
    $examId  = (int)$body['exam_id'];
    $repFid  = (int)$body['replacement_faculty_id'];
    $roomId  = (int)$body['room_id'];
    $reason  = trim($body['reason']);
    $origFid = (int)$_SESSION['reference_id'];

    $stmt = $con->prepare("INSERT INTO replacement_log (exam_id,original_faculty_id,replacement_faculty_id,room_id,reason,status) VALUES(?,?,?,?,?,'Pending')");
    $stmt->bind_param("iiiis", $examId, $origFid, $repFid, $roomId, $reason);
    echo json_encode($stmt->execute()
        ? ['success'=>true,  'message'=>'Replacement request submitted']
        : ['success'=>false, 'message'=>$stmt->error]);
    $stmt->close();
    exit;
}

if($method == 'POST' && $act == 'process'){
    $repId  = (int)$body['log_id'];
    $status = in_array($body['status'], ['Approved','Rejected']) ? $body['status'] : 'Rejected';

    $stmt = $con->prepare("UPDATE replacement_log SET status=?, processed_at=NOW() WHERE replacement_id=?");
    $stmt->bind_param("si", $status, $repId);
    $ok = $stmt->execute();
    $stmt->close();

    if($ok && $status == 'Approved'){
        $rl = $con->query("SELECT * FROM replacement_log WHERE replacement_id=$repId")->fetch_assoc();
        if($rl){
            $con->query("UPDATE invigilation_allocation
                         SET faculty_id={$rl['replacement_faculty_id']}
                         WHERE exam_id={$rl['exam_id']} AND room_id={$rl['room_id']}
                         AND faculty_id={$rl['original_faculty_id']}");
        }
    }
    echo json_encode($ok
        ? ['success'=>true,  'message'=>"Request $status"]
        : ['success'=>false, 'message'=>'Update failed']);
    exit;
}

if($method == 'GET' && $act == 'faculty_list'){
    $rows = [];
    $q = $con->query("SELECT faculty_id, name, department FROM faculty ORDER BY name ASC");
    while($r = $q->fetch_assoc()) $rows[] = $r;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($method == 'GET' && $act == 'list_exams'){
    $rows = [];
    $q = $con->query("SELECT exam_id, exam_name, date FROM exams ORDER BY date DESC");
    while($r = $q->fetch_assoc()) $rows[] = $r;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

if($method == 'GET' && $act == 'list_rooms'){
    $rows = [];
    $q = $con->query("SELECT room_id, room_no, building FROM rooms ORDER BY room_no ASC");
    while($r = $q->fetch_assoc()) $rows[] = $r;
    echo json_encode(['success'=>true, 'data'=>$rows]);
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Invalid action']);
closeConnection($con);
