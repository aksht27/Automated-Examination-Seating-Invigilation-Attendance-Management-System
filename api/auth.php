<?php
ini_set('display_errors',0);
ob_start();
session_start();
ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$body   = json_decode(file_get_contents('php://input'), true);
$action = $body['action'] ?? $_GET['action'] ?? '';

if($action == 'login'){
    $uname = trim($body['username'] ?? '');
    $pwd   = trim($body['password'] ?? '');
    $role  = trim($body['role'] ?? '');

    if(!$uname || !$pwd || !$role){
        echo json_encode(['success'=>false, 'message'=>'All fields are required']);
        exit;
    }

    $con  = getConnection();
    $stmt = $con->prepare("SELECT * FROM users WHERE username=? AND role=?");
    $stmt->bind_param("ss", $uname, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        if(password_verify($pwd, $user['password'])){
            $_SESSION['user_id']      = $user['user_id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['role']         = $user['role'];
            $_SESSION['reference_id'] = $user['reference_id'];
            $_SESSION['logged_in']    = true;
            echo json_encode(['success'=>true, 'role'=>$user['role'], 'username'=>$user['username']]);
        } else {
            echo json_encode(['success'=>false, 'message'=>'Incorrect password']);
        }
    } else {
        echo json_encode(['success'=>false, 'message'=>'User not found for this role']);
    }
    $stmt->close();
    closeConnection($con);
    exit;
}

if($action == 'logout'){
    session_unset();
    session_destroy();
    echo json_encode(['success'=>true]);
    exit;
}

if($action == 'check'){
    echo json_encode([
        'logged_in' => isset($_SESSION['logged_in']) ? (bool)$_SESSION['logged_in'] : false,
        'role'      => $_SESSION['role'] ?? null,
        'username'  => $_SESSION['username'] ?? null
    ]);
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Invalid action']);
