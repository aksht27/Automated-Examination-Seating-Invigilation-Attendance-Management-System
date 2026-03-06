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
    $list = [];
    $res  = $con->query("SELECT * FROM students ORDER BY roll_no ASC");
    while($row = $res->fetch_assoc()) $list[] = $row;
    echo json_encode(['success'=>true, 'data'=>$list]);
    exit;
}

if($method == 'POST' && $act == 'add'){
    $roll    = trim($body['roll_no']);
    $name    = trim($body['name']);
    $branch  = trim($body['branch']);
    $sem     = (int)$body['semester'];
    $section = trim($body['section']);

    $stmt = $con->prepare("INSERT INTO students (roll_no,name,branch,semester,section) VALUES(?,?,?,?,?)");
    $stmt->bind_param("sssis", $roll, $name, $branch, $sem, $section);
    if($stmt->execute()){
        $newId  = $stmt->insert_id;
        $hashed = password_hash('student123', PASSWORD_DEFAULT);
        $rl     = 'student';
        $us = $con->prepare("INSERT IGNORE INTO users (username,password,role,reference_id) VALUES(?,?,?,?)");
        $us->bind_param("sssi", $roll, $hashed, $rl, $newId);
        $us->execute();
        $us->close();
        echo json_encode(['success'=>true, 'message'=>'Student added successfully']);
    } else {
        echo json_encode(['success'=>false, 'message'=>$stmt->error]);
    }
    $stmt->close();
    exit;
}

if($method == 'POST' && $act == 'edit'){
    $sid     = (int)$body['student_id'];
    $roll    = trim($body['roll_no']);
    $name    = trim($body['name']);
    $branch  = trim($body['branch']);
    $sem     = (int)$body['semester'];
    $section = trim($body['section']);

    $stmt = $con->prepare("UPDATE students SET roll_no=?,name=?,branch=?,semester=?,section=? WHERE student_id=?");
    $stmt->bind_param("sssisi", $roll, $name, $branch, $sem, $section, $sid);
    if($stmt->execute()){
        echo json_encode(['success'=>true, 'message'=>'Student updated']);
    } else {
        echo json_encode(['success'=>false, 'message'=>$stmt->error]);
    }
    $stmt->close();
    exit;
}

if($method == 'POST' && $act == 'delete'){
    $sid = (int)$body['student_id'];
    $con->query("DELETE FROM users WHERE role='student' AND reference_id=$sid");
    $stmt = $con->prepare("DELETE FROM students WHERE student_id=?");
    $stmt->bind_param("i", $sid);
    if($stmt->execute()){
        echo json_encode(['success'=>true, 'message'=>'Student removed']);
    } else {
        echo json_encode(['success'=>false, 'message'=>$stmt->error]);
    }
    $stmt->close();
    exit;
}

if($method == 'POST' && $act == 'import'){
    $students = $body['students'] ?? [];
    $added = 0; $skipped = 0;
    foreach($students as $s){
        $roll    = trim($s['roll_no'] ?? '');
        $name    = trim($s['name'] ?? '');
        $branch  = trim($s['branch'] ?? '');
        $sem     = (int)($s['semester'] ?? 0);
        $section = trim($s['section'] ?? '');
        if(!$roll || !$name){ $skipped++; continue; }
        $stmt = $con->prepare("INSERT IGNORE INTO students (roll_no,name,branch,semester,section) VALUES(?,?,?,?,?)");
        $stmt->bind_param("sssis", $roll, $name, $branch, $sem, $section);
        if($stmt->execute() && $stmt->affected_rows > 0){
            $nid    = $stmt->insert_id;
            $hashed = password_hash('student123', PASSWORD_DEFAULT);
            $rl     = 'student';
            $us = $con->prepare("INSERT IGNORE INTO users (username,password,role,reference_id) VALUES(?,?,?,?)");
            $us->bind_param("sssi", $roll, $hashed, $rl, $nid);
            $us->execute(); $us->close();
            $added++;
        } else { $skipped++; }
        $stmt->close();
    }
    echo json_encode(['success'=>true, 'message'=>"Imported $added, skipped $skipped"]);
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Unknown action']);
closeConnection($con);
