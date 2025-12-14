<?php
/**
 * Student Management API
 * 
 * This is a RESTful API that handles all CRUD operations for student management.
 * It uses PDO to interact with a MySQL database.
 * 
 * NOTE: CHANGED — API previously used student_id (string) as primary identifier.  
 * Now uses id (INT) to match database schema. All changes marked clearly.
 */

// ============================================================================
// HEADERS & CORS
// ============================================================================

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================================
// DATABASE
// ============================================================================

class Database {
    private $host = "localhost";
    private $db_name = "your_database";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $e) {
            echo json_encode(["success" => false, "message" => "Database Connection Error"]);
            exit();
        }
    }
}

$db = (new Database())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);

// ============================================================================
// HELPERS
// ============================================================================

function sendResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ============================================================================
// GET
// ============================================================================

function getStudents($db) {
    $stmt = $db->prepare("SELECT id, student_id, name, email, created_at FROM students");
    $stmt->execute();
    sendResponse(["success" => true, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}

function getStudentById($db, $id) {
    $stmt = $db->prepare("SELECT id, student_id, name, email, created_at FROM students WHERE id=:id");
    $stmt->bindValue(":id", intval($id));
    $stmt->execute();

    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) sendResponse(["success"=>false,"message"=>"Student not found"],404);

    sendResponse(["success"=>true,"data"=>$student]);
}

// ============================================================================
// CREATE
// ============================================================================

function createStudent($db, $data) {

    $student_id = sanitizeInput($data['student_id'] ?? '');
    $name = sanitizeInput($data['name'] ?? '');
    $email = sanitizeInput($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (!$student_id || !$name || !$email || !$password)
        sendResponse(["success"=>false,"message"=>"All fields required"],400);

    if (!validateEmail($email))
        sendResponse(["success"=>false,"message"=>"Invalid email"],400);

    $stmt = $db->prepare("SELECT COUNT(*) FROM students WHERE student_id=:sid OR email=:email");
    $stmt->execute([":sid"=>$student_id, ":email"=>$email]);

    if ($stmt->fetchColumn() > 0)
        sendResponse(["success"=>false,"message"=>"Student already exists"],409);

    $stmt = $db->prepare("
        INSERT INTO students (student_id, name, email, password, created_at)
        VALUES (:sid, :name, :email, :pass, NOW())
    ");

    $stmt->execute([
        ":sid"=>$student_id,
        ":name"=>$name,
        ":email"=>$email,
        ":pass"=>password_hash($password, PASSWORD_DEFAULT)
    ]);

    sendResponse(["success"=>true,"message"=>"Student created"],201);
}

// ============================================================================
// UPDATE
// ============================================================================

function updateStudent($db, $data) {

    // NOTE: CHANGED — id can come from body OR query
    //$id = intval($data['id'] ?? 0);

    $id = intval(
        $data['id']
        ?? $_GET['id']
        ?? 0
    );

    // NOTE: CHANGED — fallback using student_id
    if ($id <= 0 && !empty($data['student_id'])) {
        $stmt = $db->prepare("SELECT id FROM students WHERE student_id=:sid");
        $stmt->bindValue(":sid", $data['student_id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = intval($row['id'] ?? 0);
    }

    if ($id <= 0)
        sendResponse(["success"=>false,"message"=>"id required"],400);

    $stmt = $db->prepare("SELECT id FROM students WHERE id=:id");
    $stmt->bindValue(":id",$id);
    $stmt->execute();

    if (!$stmt->fetch())
        sendResponse(["success"=>false,"message"=>"Student not found"],404);

    $fields = [];
    $params = [];

    if (!empty($data['name'])) {
        $fields[] = "name=:name";
        $params[':name'] = sanitizeInput($data['name']);
    }

    if (!empty($data['email'])) {
        if (!validateEmail($data['email']))
            sendResponse(["success"=>false,"message"=>"Invalid email"],400);

        $fields[] = "email=:email";
        $params[':email'] = sanitizeInput($data['email']);
    }

    if (!$fields)
        sendResponse(["success"=>false,"message"=>"No fields to update"],400);

    $sql = "UPDATE students SET ".implode(", ",$fields)." WHERE id=:id";
    $stmt = $db->prepare($sql);

    foreach ($params as $k=>$v)
        $stmt->bindValue($k,$v);

    $stmt->bindValue(":id",$id);
    $stmt->execute();

    sendResponse(["success"=>true,"message"=>"Student updated"]);
}

// ============================================================================
// DELETE
// ============================================================================

function deleteStudent($db, $id) {

    $id = intval($id);
    if ($id <= 0)
        sendResponse(["success"=>false,"message"=>"id required"],400);

    // NOTE: CHANGED — existence check removed
    /*
    $stmt = $db->prepare("SELECT * FROM students WHERE id=:id");
    $stmt->execute();
    if (!$stmt->fetch()) sendResponse(...);
    */

    $stmt = $db->prepare("DELETE FROM students WHERE id=:id");
    $stmt->bindValue(":id",$id);
    $stmt->execute();

    // NOTE: CHANGED — return 204 No Content
    http_response_code(204);
    exit();
}

// ============================================================================
// PASSWORD
// ============================================================================

function changePassword($db, $data) {

    $id = intval($data['id'] ?? 0);
    $current = $data['current_password'] ?? '';
    $new = $data['new_password'] ?? '';

    if ($id<=0 || !$current || !$new)
        sendResponse(["success"=>false,"message"=>"All fields required"],400);

    if (strlen($new) < 8)
        sendResponse(["success"=>false,"message"=>"Password too short"],400);

    $stmt = $db->prepare("SELECT password FROM students WHERE id=:id");
    $stmt->bindValue(":id",$id);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) sendResponse(["success"=>false,"message"=>"Student not found"],404);

    if (!password_verify($current,$row['password']))
        sendResponse(["success"=>false,"message"=>"Wrong password"],401);

    $stmt = $db->prepare("UPDATE students SET password=:p WHERE id=:id");
    $stmt->execute([
        ":p"=>password_hash($new,PASSWORD_DEFAULT),
        ":id"=>$id
    ]);

    sendResponse(["success"=>true,"message"=>"Password updated"]);
}

// ============================================================================
// ROUTER
// ============================================================================

try {

    if ($method==='GET') {
        isset($_GET['id']) ? getStudentById($db,$_GET['id']) : getStudents($db);
    }

    elseif ($method==='POST') {
        ($_GET['action'] ?? '')==='change_password'
            ? changePassword($db,$input)
            : createStudent($db,$input);
    }

    elseif ($method==='PUT') updateStudent($db,$input);

    elseif ($method==='DELETE') {
        $id = $_GET['id'] ?? $input['id'] ?? 0;
        deleteStudent($db,$id);
    }

    else sendResponse(["success"=>false,"message"=>"Method Not Allowed"],405);

} catch(Exception $e) {
    sendResponse(["success"=>false,"message"=>"Server error"],500);
}
?>
