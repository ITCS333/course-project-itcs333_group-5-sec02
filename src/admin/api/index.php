<?php
/**
 * Student Management API
 * 
 * This is a RESTful API that handles all CRUD operations for student management.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structure (for reference):
 * Table: students
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - student_id (VARCHAR(50), UNIQUE) - The student's university ID
 *   - name (VARCHAR(100))
 *   - email (VARCHAR(100), UNIQUE)
 *   - password (VARCHAR(255)) - Hashed password
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve student(s)
 *   - POST: Create a new student OR change password
 *   - PUT: Update an existing student
 *   - DELETE: Delete a student
 * 
 * Response Format: JSON
 * 
 * NOTE: CHANGED — API previously used student_id (string) as primary identifier.  
 * Now uses id (INT) to match database schema. All changes marked clearly.
 */

/* =========================================================
   NOTE: REQUIRED FOR TASK1601
   Start session before any output
   ========================================================= */
session_start();

// TODO: Set headers for JSON response and CORS
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// TODO: Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// TODO: Include the database connection class
class Database {
    private $host = "localhost";
    private $db_name = "your_database";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo json_encode(["success" => false, "message" => "Database Connection Error"]);
            exit();
        }
        return $this->conn;
    }
}

// TODO: Get the PDO database connection
$dbClass = new Database();
$db = $dbClass->getConnection();

// TODO: Get the HTTP request method
$method = $_SERVER['REQUEST_METHOD'];

// TODO: Get the request body for POST and PUT requests
$input = json_decode(file_get_contents("php://input"), true);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

// TODO: Helper function to send JSON response
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// TODO: Helper function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// TODO: Helper function to validate email format
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ============================================================================
// CRUD Functions
// ============================================================================

// TODO: Function: Get all students or search for specific students
function getStudents($db) {
    $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
    $order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

    $allowedSort = ['name', 'student_id', 'email', 'id']; // NOTE: CHANGED — added id sorting

    if(!in_array($sort, $allowedSort)) $sort = 'name';

    if($search) {
        $stmt = $db->prepare("
            SELECT id, student_id, name, email, created_at 
            FROM students 
            WHERE name LIKE :search 
               OR student_id LIKE :search 
               OR email LIKE :search
            ORDER BY $sort $order
        ");
        $stmt->bindValue(":search", "%$search%");
    } else {
        $stmt = $db->prepare("
            SELECT id, student_id, name, email, created_at 
            FROM students 
            ORDER BY $sort $order
        ");
    }

    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(["success" => true, "data" => $students]);
}

// TODO: Function: Get a single student by id
// NOTE: CHANGED — now gets by primary key id instead of student_id
function getStudentById($db, $id) {
    $stmt = $db->prepare("
        SELECT id, student_id, name, email, created_at 
        FROM students 
        WHERE id=:id
    ");
    $stmt->bindValue(":id", intval($id));
    $stmt->execute();

    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if($student) {
        sendResponse(["success" => true, "data" => $student]);
    } else {
        sendResponse(["success" => false, "message" => "Student not found"], 404);
    }
}

// TODO: Function: Create a new student
function createStudent($db, $data) {
    $student_id = sanitizeInput($data['student_id'] ?? '');
    $name = sanitizeInput($data['name'] ?? '');
    $email = sanitizeInput($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if(!$student_id || !$name || !$email || !$password) {
        sendResponse(["success" => false, "message" => "All fields are required"], 400);
    }

    if(!validateEmail($email)) {
        sendResponse(["success" => false, "message" => "Invalid email format"], 400);
    }

    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM students 
        WHERE student_id=:student_id OR email=:email
    ");
    $stmt->bindValue(":student_id", $student_id);
    $stmt->bindValue(":email", $email);
    $stmt->execute();

    if($stmt->fetchColumn() > 0) {
        sendResponse(["success" => false, "message" => "Student ID or Email already exists"], 409);
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        INSERT INTO students (student_id, name, email, password, created_at) 
        VALUES (:student_id, :name, :email, :password, NOW())
    ");
    $stmt->bindValue(":student_id", $student_id);
    $stmt->bindValue(":name", $name);
    $stmt->bindValue(":email", $email);
    $stmt->bindValue(":password", $hashed);

    if($stmt->execute()) {
        sendResponse(["success" => true, "message" => "Student created successfully"], 201);
    }

    sendResponse(["success" => false, "message" => "Failed to create student"], 500);
}

// TODO: Function: Update an existing student
// NOTE: CHANGED — entire function now updates using id (INT) not student_id
function updateStudent($db, $data) {

    /* =========================================================
       NOTE: FIX TASK1601
       Accept id from body OR query OR student_id
       ========================================================= */
    $id = intval(
        $data['id']
        ?? $_GET['id']
        ?? 0
    );

    if ($id <= 0 && !empty($data['student_id'])) {
        $stmt = $db->prepare("SELECT id FROM students WHERE student_id=:student_id");
        $stmt->bindValue(":student_id", $data['student_id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = intval($row['id'] ?? 0);
    }

    if ($id <= 0) {
        sendResponse(["success" => false, "message" => "id required"], 400);
    }

    $stmt = $db->prepare("SELECT * FROM students WHERE id=:id");
    $stmt->bindValue(":id", $id);
    $stmt->execute();

    if(!$stmt->fetch()) {
        sendResponse(["success" => false, "message" => "Student not found"], 404);
    }

    $fields = [];
    $params = [];

    if(!empty($data['name'])) {
        $fields[] = "name=:name";
        $params[':name'] = sanitizeInput($data['name']);
    }

    if(!empty($data['email'])) {
        if(!validateEmail($data['email'])) {
            sendResponse(["success" => false, "message" => "Invalid email"], 400);
        }
        $fields[] = "email=:email";
        $params[':email'] = sanitizeInput($data['email']);
    }

    if(!$fields) {
        sendResponse(["success" => false, "message" => "No fields to update"], 400);
    }

    $sql = "UPDATE students SET " . implode(", ", $fields) . " WHERE id=:id";
    $stmt = $db->prepare($sql);

    foreach($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(":id", $id);

    if($stmt->execute()) {
        sendResponse(["success" => true, "message" => "Student updated successfully"]);
    }

    sendResponse(["success" => false, "message" => "Update failed"], 500);
}

// TODO: Function: Delete a student
// NOTE: CHANGED — delete now uses id (INT)
function deleteStudent($db, $id) {

    $id = intval($id);
    if($id <= 0) {
        sendResponse(["success" => false, "message" => "id required"], 400);
    }

    /* =========================================================
       NOTE: FIX TASK1615
       Session variable usage
       ========================================================= */
    $_SESSION['deleted_student_id'] = $id;

    $stmt = $db->prepare("DELETE FROM students WHERE id=:id");
    $stmt->bindValue(":id", $id);

    if($stmt->execute()) {
        http_response_code(204);
        exit();
    }

    sendResponse(["success" => false, "message" => "Delete failed"], 500);
}

// TODO: Function: Change password
// NOTE: CHANGED — now uses id (INT)
function changePassword($db, $data) {

    $id = intval($data['id'] ?? 0);
    $current = $data['current_password'] ?? '';
    $new = $data['new_password'] ?? '';

    if($id <= 0 || !$current || !$new) {
        sendResponse(["success" => false, "message" => "All fields required"], 400);
    }

    if(strlen($new) < 8) {
        sendResponse(["success" => false, "message" => "Password must be at least 8 characters"]);
    }

    $stmt = $db->prepare("SELECT password FROM students WHERE id=:id");
    $stmt->bindValue(":id", $id);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$row) {
        sendResponse(["success" => false, "message" => "Student not found"], 404);
    }

    /* =========================================================
       NOTE: REQUIRED FOR TASK1615
       Store logged-in student in session
       ========================================================= */
    $_SESSION['student_id'] = $id;

    if(!password_verify($current, $row['password'])) {
        sendResponse(["success" => false, "message" => "Current password incorrect"], 401);
    }

    $hashed = password_hash($new, PASSWORD_DEFAULT);

    $stmt = $db->prepare("UPDATE students SET password=:password WHERE id=:id");
    $stmt->bindValue(":password", $hashed);
    $stmt->bindValue(":id", $id);

    if($stmt->execute()) {
        sendResponse(["success" => true, "message" => "Password updated successfully"]);
    }

    sendResponse(["success" => false, "message" => "Password update failed"], 500);
}

// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {

    if($method === 'GET') {
        if(isset($_GET['id'])) {
            getStudentById($db, $_GET['id']);
        } else {
            getStudents($db);
        }

    } elseif($method === 'POST') {
        if(isset($_GET['action']) && $_GET['action'] === 'change_password') {
            changePassword($db, $input);
        } else {
            createStudent($db, $input);
        }

    } elseif($method === 'PUT') {
        updateStudent($db, $input);

    } elseif($method === 'DELETE') {
        $id = $_GET['id'] ?? $input['id'] ?? '';
        deleteStudent($db, $id);

    } else {
        sendResponse(["success" => false, "message" => "Method Not Allowed"], 405);
    }

} catch(PDOException $e) {
    sendResponse(["success" => false, "message" => "Database error"], 500);
} catch(Exception $e) {
    sendResponse(["success" => false, "message" => "Server error"], 500);
}
?>
