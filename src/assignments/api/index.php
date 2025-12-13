<?php
session_start();
$_SESSION['user'] = $_SESSION['user'] ?? null;
$_SESSION['user'] = 'test_user';
$_SESSION['user_id'] = 1;
/**
 * Assignment Management API
 * 
 * This is a RESTful API that handles all CRUD operations for course assignments
 * and their associated discussion comments.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structures (for reference):
 * 
 * Table: assignments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - title (VARCHAR(200))
 *   - description (TEXT)
 *   - due_date (DATE)
 *   - files (TEXT)
 *   - created_at (TIMESTAMP)
 *   - updated_at (TIMESTAMP)
 * 
 * Table: comments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - assignment_id (VARCHAR(50), FOREIGN KEY)
 *   - author (VARCHAR(100))
 *   - text (TEXT)
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve assignment(s) or comment(s)
 *   - POST: Create a new assignment or comment
 *   - PUT: Update an existing assignment
 *   - DELETE: Delete an assignment or comment
 * 
 * Response Format: JSON
 */

// ============================================================================
// HEADERS AND CORS CONFIGURATION
// ============================================================================


// TODO: Set Content-Type header to application/json
header("Content-Type: application/json");


// TODO: Set CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// TODO: Handle preflight OPTIONS request
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    http_response_code(200);
    exit();
}


// ============================================================================
// DATABASE CONNECTION
// ============================================================================

// TODO: Include the database connection class
require_once 'Database.php';


// TODO: Create database connection
$host = "localhost";    
$db_name = "test";      
$username = "root";    
$password = "";         
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit();
}

// TODO: Set PDO to throw exceptions on errors
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



// ============================================================================
// REQUEST PARSING
// ============================================================================

// TODO: Get the HTTP request method
$request_method = $_SERVER['REQUEST_METHOD'];

// TODO: Get the request body for POST and PUT requests
$request_body = file_get_contents("php://input");

// TODO: Parse query parameters
$query_params = $_GET; 


// ============================================================================
// ASSIGNMENT CRUD FUNCTIONS
// ============================================================================

/**
 * Function: Get all assignments
 * Method: GET
 * Endpoint: ?resource=assignments
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by title or description
 *   - sort: Optional field to sort by (title, due_date, created_at)
 *   - order: Optional sort order (asc or desc, default: asc)
 * 
 * Response: JSON array of assignment objects
 */
function getAllAssignments($db) {
    // TODO: Start building the SQL query
        $sql = "SELECT * FROM assignments WHERE 1=1";
    
    // TODO: Check if 'search' query parameter exists in $_GET
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $sql .= " AND (title LIKE :search OR description LIKE :search)";
    }
    
    // TODO: Check if 'sort' and 'order' query parameters exist
    $sort = "created_at";   
    $order = "ASC"; 
    if (isset($_GET['sort']) && $_GET['sort'] !== '') {
        $sort = $_GET['sort'];
    }

    if (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') {
        $order = "DESC";
    }

    $sql .= " ORDER BY $sort $order";

    // TODO: Prepare the SQL statement using $db->prepare()
        $stmt = $db->prepare($sql);

    
    // TODO: Bind parameters if search is used
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $searchParam = "%" . $_GET['search'] . "%";
        $stmt->bindParam(':search', $searchParam);
    }

    
    // TODO: Execute the prepared statement
        $stmt->execute();

    
    // TODO: Fetch all results as associative array
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    // TODO: For each assignment, decode the 'files' field from JSON to array
    foreach ($assignments as &$assignment) {
        if (!empty($assignment['files'])) {
            $assignment['files'] = json_decode($assignment['files'], true);
        }
    }
    
    // TODO: Return JSON response
        echo json_encode($assignments);

}


/**
 * Function: Get a single assignment by ID
 * Method: GET
 * Endpoint: ?resource=assignments&id={assignment_id}
 * 
 * Query Parameters:
 *   - id: The assignment ID (required)
 * 
 * Response: JSON object with assignment details
 */
function getAssignmentById($db, $assignmentId) {
    // TODO: Validate that $assignmentId is provided and not empty
        if (empty($assignmentId)) {
        echo json_encode([
            "status" => "error",
            "message" => "Assignment ID is required"
        ]);
        http_response_code(400);
        return;
    }
    
    // TODO: Prepare SQL query to select assignment by id
    $sql = "SELECT * FROM assignments WHERE id = :id";
    $stmt = $db->prepare($sql);
    
    // TODO: Bind the :id parameter
        $stmt->bindParam(':id', $assignmentId, PDO::PARAM_INT);

    
    // TODO: Execute the statement
        $stmt->execute();

    
    // TODO: Fetch the result as associative array
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    
    // TODO: Check if assignment was found
    if (!$assignment) {
        echo json_encode([
            "status" => "error",
            "message" => "Assignment not found"
        ]);
        http_response_code(404);
        return;
    }
    
    // TODO: Decode the 'files' field from JSON to array
    if (!empty($assignment['files'])) {
        $assignment['files'] = json_decode($assignment['files'], true);
    }
    
    // TODO: Return success response with assignment data
    echo json_encode([
        "status" => "success",
        "data" => $assignment
    ]);
}


/**
 * Function: Create a new assignment
 * Method: POST
 * Endpoint: ?resource=assignments
 * 
 * Required JSON Body:
 *   - title: Assignment title (required)
 *   - description: Assignment description (required)
 *   - due_date: Due date in YYYY-MM-DD format (required)
 *   - files: Array of file URLs/paths (optional)
 * 
 * Response: JSON object with created assignment data
 */
function createAssignment($db, $data) {
    // TODO: Validate required fields
    if (empty($data['title']) || empty($data['description']) || empty($data['due_date'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Title, description, and due date are required."
    ]);
    http_response_code(400);
    return;
}

    
    // TODO: Sanitize input data
    $title = htmlspecialchars(trim($data['title']));
    $description = htmlspecialchars(trim($data['description']));
    $due_date = trim($data['due_date']);

    
    // TODO: Validate due_date format
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $due_date)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid due_date format. Use YYYY-MM-DD."
    ]);
    http_response_code(400);
    return;
}

    
    // TODO: Generate a unique assignment ID

    // TODO: Handle the 'files' field
    $files = [];
    if (!empty($data['files']) && is_array($data['files'])) {
    $files = $data['files'];
    }
    $files_json = json_encode($files);

    
    // TODO: Prepare INSERT query
    $sql = "INSERT INTO assignments (id, title, description, due_date, files, created_at, updated_at)
            VALUES (:id, :title, :description, :due_date, :files, NOW(), NOW())";
    $stmt = $db->prepare($sql);
    
    // TODO: Bind all parameters
    //$stmt->bindParam(':id', $assignmentId);
    $newId = $db->lastInsertId();//CHANGED 
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':due_date', $due_date);
    $stmt->bindParam(':files', $files_json);

    
    // TODO: Execute the statement
    $stmt->execute();
    $newId = $db->lastInsertId();
    
    // TODO: Check if insert was successful
    if ($stmt->rowCount() > 0) {
    $insertSuccess = true;
} else {
    
    $insertSuccess = false;
}

    
    // TODO: If insert failed, return 500 error
    if (!$insertSuccess) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to create assignment."
    ]);
    return; // Stop execution
}

}


/**
 * Function: Update an existing assignment
 * Method: PUT
 * Endpoint: ?resource=assignments
 * 
 * Required JSON Body:
 *   - id: Assignment ID (required, to identify which assignment to update)
 *   - title: Updated title (optional)
 *   - description: Updated description (optional)
 *   - due_date: Updated due date (optional)
 *   - files: Updated files array (optional)
 * 
 * Response: JSON object with success status
 */
function updateAssignment($db, $data) {
    // TODO: Validate that 'id' is provided in $data
    if (!isset($data['id']) || empty($data['id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Assignment ID is required"]);
    return;
}

    
    // TODO: Store assignment ID in variable
    $assignmentId = $data['id'];

    
    // TODO: Check if assignment exists
    $stmtCheck = $db->prepare("SELECT * FROM assignments WHERE id = :id");
    $stmtCheck->bindParam(':id', $assignmentId);
    $stmtCheck->execute();

    $assignment = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Assignment not found"]);
    return;
    }

    
    // TODO: Build UPDATE query dynamically based on provided fields
    $fieldsToUpdate = [];
    $params = [];

    
    // TODO: Check which fields are provided and add to SET clause
    if (isset($data['title'])) {
    $fieldsToUpdate[] = "title = :title";
    $params[':title'] = $data['title'];
}
if (isset($data['description'])) {
    $fieldsToUpdate[] = "description = :description";
    $params[':description'] = $data['description'];
}
if (isset($data['due_date'])) {
    $fieldsToUpdate[] = "due_date = :due_date";
    $params[':due_date'] = $data['due_date'];
}
if (isset($data['files'])) {
    $fieldsToUpdate[] = "files = :files";
    $params[':files'] = json_encode($data['files']);
}

    
    // TODO: If no fields to update (besides updated_at), return 400 error
    if (empty($fieldsToUpdate)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No fields provided to update"]);
    return;
}

    
    // TODO: Complete the UPDATE query
    $sql = "UPDATE assignments SET " . implode(", ", $fieldsToUpdate) . ", updated_at = NOW() WHERE id = :id";
    $params[':id'] = $assignmentId;

    
    // TODO: Prepare the statement
    $stmt = $db->prepare($sql);

    
    // TODO: Bind all parameters dynamically
    foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

    
    // TODO: Execute the statement
    $stmt->execute();

    
    // TODO: Check if update was successful
    $success = $stmt->rowCount() > 0;  // returns true if at least 1 row was updated

    
    // TODO: If no rows affected, return appropriate message
    if ($success) {
    echo json_encode([
        "status" => "success",
        "message" => "Assignment updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => "info",
        "message" => "No changes were made to the assignment."
    ]);
}

}


/**
 * Function: Delete an assignment
 * Method: DELETE
 * Endpoint: ?resource=assignments&id={assignment_id}
 * 
 * Query Parameters:
 *   - id: Assignment ID (required)
 * 
 * Response: JSON object with success status
 */
function deleteAssignment($db, $assignmentId) {
    // TODO: Validate that $assignmentId is provided and not empty
    if (!isset($assignmentId) || empty($assignmentId)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Assignment ID is required"]);
    return;
}

    
    // TODO: Check if assignment exists
    $stmtCheck = $db->prepare("SELECT * FROM assignments WHERE id = :id");
    $stmtCheck->bindParam(':id', $assignmentId);
    $stmtCheck->execute();

    $assignment = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Assignment not found"]);
    return;
}

    
    // TODO: Delete associated comments first (due to foreign key constraint)
    $stmtComments = $db->prepare("DELETE FROM comments WHERE assignment_id = :id");
    $stmtComments->bindParam(':id', $assignmentId);
    $stmtComments->execute();

    
    // TODO: Prepare DELETE query for assignment
    $sql = "DELETE FROM assignments WHERE id = :id";
    $stmt = $db->prepare($sql);

    
    // TODO: Bind the :id parameter
    $stmt->bindParam(':id', $assignmentId);

    
    // TODO: Execute the statement
    $stmt->execute();

    
    // TODO: Check if delete was successful
    $success = $stmt->rowCount() > 0;

    
    // TODO: If delete failed, return 500 error
    if ($success) {
    echo json_encode([
        "status" => "success",
        "message" => "Assignment deleted successfully."
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to delete assignment."
    ]);
}

}


// ============================================================================
// COMMENT CRUD FUNCTIONS
// ============================================================================

/**
 * Function: Get all comments for a specific assignment
 * Method: GET
 * Endpoint: ?resource=comments&assignment_id={assignment_id}
 * 
 * Query Parameters:
 *   - assignment_id: The assignment ID (required)
 * 
 * Response: JSON array of comment objects
 */
function getCommentsByAssignment($db, $assignmentId) {
    // TODO: Validate that $assignmentId is provided and not empty
    if (!isset($assignmentId) || empty($assignmentId)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Assignment ID is required"]);
    return;
}

    
    // TODO: Prepare SQL query to select all comments for the assignment
    $sql = "SELECT * FROM comments WHERE assignment_id = :assignment_id ORDER BY created_at ASC";
    $stmt = $db->prepare($sql);

    
    // TODO: Bind the :assignment_id parameter
    $stmt->bindParam(':assignment_id', $assignmentId);

    
    // TODO: Execute the statement
    $stmt->execute();

    
    // TODO: Fetch all results as associative array
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    // TODO: Return success response with comments data
    echo json_encode([
    "status" => "success",
    "count" => count($comments),
    "data" => $comments
]);

}


/**
 * Function: Create a new comment
 * Method: POST
 * Endpoint: ?resource=comments
 * 
 * Required JSON Body:
 *   - assignment_id: Assignment ID (required)
 *   - author: Comment author name (required)
 *   - text: Comment content (required)
 * 
 * Response: JSON object with created comment data
 */
function createComment($db, $data) {
    // TODO: Validate required fields
    if (!isset($data['assignment_id'], $data['author'], $data['text']) ||
    empty($data['assignment_id']) || empty($data['author']) || empty($data['text'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "All fields (assignment_id, author, text) are required"]);
    return;
}

    
    // TODO: Sanitize input data
    $assignmentId = trim($data['assignment_id']);
    $author = trim($data['author']);
    $text = trim($data['text']);

    
    // TODO: Validate that text is not empty after trimming
    if ($text === '') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Comment text cannot be empty"]);
    return;
}

    
    // TODO: Verify that the assignment exists
    $stmtCheck = $db->prepare("SELECT id FROM assignments WHERE id = :id");
    $stmtCheck->bindParam(':id', $assignmentId);
    $stmtCheck->execute();
    if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Assignment not found"]);
        return;
}

    
    // TODO: Prepare INSERT query for comment
    $sql = "INSERT INTO comments (assignment_id, author, text, created_at) VALUES (:assignment_id, :author, :text, NOW())";
    $stmt = $db->prepare($sql);

    
    // TODO: Bind all parameters
    $stmt->bindParam(':assignment_id', $assignmentId);
    $stmt->bindParam(':author', $author);
    $stmt->bindParam(':text', $text);

    
    // TODO: Execute the statement
    $stmt->execute();

    
    // TODO: Get the ID of the inserted comment
    $commentId = $db->lastInsertId();

    
    // TODO: Return success response with created comment data
    echo json_encode([
    "status" => "success",
    "data" => [
        "id" => $commentId,
        "assignment_id" => $assignmentId,
        "author" => $author,
        "text" => $text,
        "created_at" => date('Y-m-d H:i:s')
    ]
]);

}


/**
 * Function: Delete a comment
 * Method: DELETE
 * Endpoint: ?resource=comments&id={comment_id}
 * 
 * Query Parameters:
 *   - id: Comment ID (required)
 * 
 * Response: JSON object with success status
 */
function deleteComment($db, $commentId) {
    // TODO: Validate that $commentId is provided and not empty
    if (!isset($commentId) || empty($commentId)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Comment ID is required"]);
    return;
}

    
    // TODO: Check if comment exists
    $stmtCheck = $db->prepare("SELECT id FROM comments WHERE id = :id");
    $stmtCheck->bindParam(':id', $commentId);
    $stmtCheck->execute();

if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Comment not found"]);
    return;
}

    
    // TODO: Prepare DELETE query
    $sql = "DELETE FROM comments WHERE id = :id";
    $stmt = $db->prepare($sql);

    
    // TODO: Bind the :id parameter
    $stmt->bindParam(':id', $commentId);

    
    // TODO: Execute the statement
    $stmt->execute();

    
    // TODO: Check if delete was successful
    $success = $stmt->rowCount() > 0;

    
    // TODO: If delete failed, return 500 error
    if ($success) {
    echo json_encode(["status" => "success", "message" => "Comment deleted successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to delete the comment"]);
}

}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Get the 'resource' query parameter to determine which resource to access
    $resource = isset($_GET['resource']) ? $_GET['resource'] : '';

    
    // TODO: Route based on HTTP method and resource type
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        // TODO: Handle GET requests
        if ($resource === 'assignments') {
            // TODO: Check if 'id' query parameter exists
            if (isset($_GET['id']) && !empty($_GET['id'])) {
            getAssignmentById($pdo, $_GET['id']);
    } else {
        getAllAssignments($pdo);
    }
        } elseif ($resource === 'comments') {
            // TODO: Check if 'assignment_id' query parameter exists
            if (isset($_GET['assignment_id']) && !empty($_GET['assignment_id'])) {
        getCommentsByAssignment($pdo, $_GET['assignment_id']);
    } else {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "assignment_id query parameter is required"
        ]);
    }
        } else {
            // TODO: Invalid resource, return 400 error
            http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Invalid resource"
    ]);
        }
        
    } elseif ($method === 'POST') {
        // TODO: Handle POST requests (create operations)
        
        if ($resource === 'assignments') {
            // TODO: Call createAssignment($db, $data)
            $data = json_decode(file_get_contents('php://input'), true);
        createAssignment($pdo, $data);

        } elseif ($resource === 'comments') {
            // TODO: Call createComment($db, $data)
            $data = json_decode(file_get_contents('php://input'), true);
        createComment($pdo, $data);

        } else {
            // TODO: Invalid resource, return 400 error
            http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid resource for POST method"
        ]);
        }
        
    } elseif ($method === 'PUT') {
        // TODO: Handle PUT requests (update operations)
        
        if ($resource === 'assignments') {
            // TODO: Call updateAssignment($db, $data)
             $data = json_decode(file_get_contents('php://input'), true);
        updateAssignment($pdo, $data);

        } else {
            // TODO: PUT not supported for other resources
            http_response_code(405);
        echo json_encode([
            "status" => "error",
            "message" => "PUT method not supported for this resource"
        ]);
        }
        
    } elseif ($method === 'DELETE') {
        // TODO: Handle DELETE requests
        
        if ($resource === 'assignments') {
            // TODO: Get 'id' from query parameter or request body
            if (isset($_GET['id']) && !empty($_GET['id'])) {
            deleteAssignment($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Assignment ID is required for DELETE"
            ]);
        }
        } elseif ($resource === 'comments') {
            // TODO: Get comment 'id' from query parameter
            if (isset($_GET['id']) && !empty($_GET['id'])) {
            deleteComment($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Comment ID is required for DELETE"
            ]);
        }
        } else {
            // TODO: Invalid resource, return 400 error
            
        }
        
    } else {
        // TODO: Method not supported
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Invalid resource for DELETE method"
        ]);
    }
    
} catch (PDOException $e) {
    // TODO: Handle database errors
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    // TODO: Handle general errors
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Error: " . $e->getMessage()
    ]);
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response and exit
 * 
 * @param array $data - Data to send as JSON
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
        http_response_code($statusCode);

    
    // TODO: Ensure data is an array
    if (!is_array($data)) {
        $data = ["message" => $data];
    }
    
    // TODO: Echo JSON encoded data
        echo json_encode($data);

    
    // TODO: Exit to prevent further execution
    
        exit();
}


/**
 * Helper function to sanitize string input
 * 
 * @param string $data - Input data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Trim whitespace from beginning and end
        $data = trim($data);

    
    // TODO: Remove HTML and PHP tags
        $data = strip_tags($data);

    
    // TODO: Convert special characters to HTML entities
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    
    // TODO: Return the sanitized data
        return $data;

}


/**
 * Helper function to validate date format (YYYY-MM-DD)
 * 
 * @param string $date - Date string to validate
 * @return bool - True if valid, false otherwise
 */
function validateDate($date) {
    // TODO: Use DateTime::createFromFormat to validate
        $d = DateTime::createFromFormat('Y-m-d', $date);

    
    // TODO: Return true if valid, false otherwise
        return $d && $d->format('Y-m-d') === $date;

}


/**
 * Helper function to validate allowed values (for sort fields, order, etc.)
 * 
 * @param string $value - Value to validate
 * @param array $allowedValues - Array of allowed values
 * @return bool - True if valid, false otherwise
 */
function validateAllowedValue($value, $allowedValues) {
    // TODO: Check if $value exists in $allowedValues array
        $isValid = in_array($value, $allowedValues, true);

    
    // TODO: Return the result
        return $isValid;

}

?>
