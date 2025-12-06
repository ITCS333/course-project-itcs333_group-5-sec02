<?php
/**
 * Weekly Course Breakdown API
 * 
 * This is a RESTful API that handles all CRUD operations for weekly course content
 * and discussion comments. It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structures (for reference):
 * 
 * Table: weeks
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - week_id (VARCHAR(50), UNIQUE) - Unique identifier (e.g., "week_1")
 *   - title (VARCHAR(200))
 *   - start_date (DATE)
 *   - description (TEXT)
 *   - links (TEXT) - JSON encoded array of links
 *   - created_at (TIMESTAMP)
 *   - updated_at (TIMESTAMP)
 * 
 * Table: comments
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - week_id (VARCHAR(50)) - Foreign key reference to weeks.week_id
 *   - author (VARCHAR(100))
 *   - text (TEXT)
 *   - created_at (TIMESTAMP)
 * 
 * HTTP Methods Supported:
 *   - GET: Retrieve week(s) or comment(s)
 *   - POST: Create a new week or comment
 *   - PUT: Update an existing week
 *   - DELETE: Delete a week or comment
 * 
 * Response Format: JSON
 */

// ============================================================================
// SETUP AND CONFIGURATION
// ============================================================================

// TODO: Set headers for JSON response and CORS
// Set Content-Type to application/json
// Allow cross-origin requests (CORS) if needed
// Allow specific HTTP methods (GET, POST, PUT, DELETE, OPTIONS)
// Allow specific headers (Content-Type, Authorization)
header("Content-Type: application/json");
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods: GET ,POST , PUT , DELETE , OPTIONS");
header("Access-Control-Allow-Headers: Content-Type , Authorization");


// TODO: Handle preflight OPTIONS request
// If the request method is OPTIONS, return 200 status and exit
if($_SERVER['REQUEST_METHOD']==='OPTIONS'){
    http_response_code(200);
    exit();
}

// TODO: Include the database connection class
// Assume the Database class has a method getConnection() that returns a PDO instance
// Example: require_once '../config/Database.php';
require_once __DIR__ . '/../config/Database.php';

// TODO: Get the PDO database connection
// Example: $database = new Database();
//          $db = $database->getConnection();
$database = new Database();
$db = $database->getConnection();

// TODO: Get the HTTP request method
// Use $_SERVER['REQUEST_METHOD']
$method = $_SERVER['REQUEST_METHOD'];

// TODO: Get the request body for POST and PUT requests
// Use file_get_contents('php://input') to get raw POST data
// Decode JSON data using json_decode()
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput , true);

// TODO: Parse query parameters
// Get the 'resource' parameter to determine if request is for weeks or comments
// Example: ?resource=weeks or ?resource=comments
$resource = $_GET['resource']?? null;
$id = $_GET['id']?? null;
$week_id = $_GET['week_id']?? null;

// ============================================================================
// WEEKS CRUD OPERATIONS
// ============================================================================

/**
 * Function: Get all weeks or search for specific weeks
 * Method: GET
 * Resource: weeks
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by title or description
 *   - sort: Optional field to sort by (title, start_date)
 *   - order: Optional sort order (asc or desc, default: asc)
 */
function getAllWeeks($db) {
    // TODO: Initialize variables for search, sort, and order from query parameters
    $search = $_GET['search']?? null;
    $sort = $_GET['sort']  ?? 'start_date';
    $order = $_GET['order'] ?? 'asc' ;
    // TODO: Start building the SQL query
    // Base query: SELECT week_id, title, start_date, description, links, created_at FROM weeks
    $sql = "SELECT week_id , title , start_date , description , links , created_at FROM weeks";
    $params = [];
    // TODO: Check if search parameter exists
    // If yes, add WHERE clause using LIKE for title and description
    // Example: WHERE title LIKE ? OR description LIKE ?
   if ($search) {
    $sql .= " WHERE title LIKE :search OR description LIKE :search ";
    $params[':search'] = "%" . $search . "%";
}

    // TODO: Check if sort parameter exists
    // Validate sort field to prevent SQL injection (only allow: title, start_date, created_at)
    // If invalid, use default sort field (start_date)
    $allowedSortFields = ['title' , 'start_date' , 'created_at'];
    if(!in_array($sort , $allowedSortFields)){
        $sort = 'start_date';
    }
    // TODO: Check if order parameter exists
    // Validate order to prevent SQL injection (only allow: asc, desc)
    // If invalid, use default order (asc)
    $order = strtolower($order);
    if(!in_array($order ,['asc', 'desc'])){
        $order = 'asc';
    }
    // TODO: Add ORDER BY clause to the query
    $sql .= " ORDER BY $sort $order";
    // TODO: Prepare the SQL query using PDO
    $stmt = $db->prepare($sql);
    // TODO: Bind parameters if using search
    // Use wildcards for LIKE: "%{$searchTerm}%"
    if($search){
        $stmt->bindParam(':search' , $params[':search']);
    }
    // TODO: Execute the query
    $stmt->execute();
    // TODO: Fetch all results as an associative array
    $weeks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // TODO: Process each week's links field
    // Decode the JSON string back to an array using json_decode()
    foreach($weeks as &$week)
    {
        $week['links'] = json_decode($week['links'],true)?? [];
    }
    // TODO: Return JSON response with success status and data
    // Use sendResponse() helper function
    sendResponse(true , "weeks fetched successfully" , $weeks);
}


/**
 * Function: Get a single week by week_id
 * Method: GET
 * Resource: weeks
 * 
 * Query Parameters:
 *   - week_id: The unique week identifier (e.g., "week_1")
 */
function getWeekById($db, $weekId) {
    // TODO: Validate that week_id is provided
    // If not, return error response with 400 status
    if(!$weekId){
        sendError("week_id is required", 400);
    }
    // TODO: Prepare SQL query to select week by week_id
    // SELECT week_id, title, start_date, description, links, created_at FROM weeks WHERE week_id = ?
    $sql = "SELECT week_id , title , start_date , description , links , created_at FROM weeks WHERE week_id = :week_id LIMIT 1";

    // TODO: Bind the week_id parameter
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':week_id', $weekId);
    // TODO: Execute the query
    $stmt->execute();
    // TODO: Fetch the result
    $week = $stmt->fetch(PDO::FETCH_ASSOC);
    // TODO: Check if week exists
    // If yes, decode the links JSON and return success response with week data
    // If no, return error response with 404 status
    if (!$week) {
        sendError("Week not found", 404);
    }

    $week['links'] = $week['links'] ? json_decode($week['links'], true) : [];
    sendResponse(true, "Week fetched", $week, 200);
}


/**
 * Function: Create a new week
 * Method: POST
 * Resource: weeks
 * 
 * Required JSON Body:
 *   - week_id: Unique week identifier (e.g., "week_1")
 *   - title: Week title (e.g., "Week 1: Introduction to HTML")
 *   - start_date: Start date in YYYY-MM-DD format
 *   - description: Week description
 *   - links: Array of resource links (will be JSON encoded)
 */
function createWeek($db, $data) {
    // TODO: Validate required fields
    // Check if week_id, title, start_date, and description are provided
    // If any field is missing, return error response with 400 status
    if (!is_array($data)) {
        sendError("Invalid JSON body", 400);
    }
    // TODO: Sanitize input data
    // Trim whitespace from title, description, and week_id
    $week_id = sanitizeInput($data['week_id'] ?? '');
    $title = sanitizeInput($data['title'] ?? '');
    $start_date = $data['start_date'] ?? '';
    $description = sanitizeInput($data['description'] ?? '');
    $links = $data['links'] ?? [];

    if (!$week_id || !$title || !$start_date || !$description) {
        sendError("Missing required fields: week_id, title, start_date, description", 400);
    }
    // TODO: Validate start_date format
    // Use a regex or DateTime::createFromFormat() to verify YYYY-MM-DD format
    // If invalid, return error response with 400 status
    if (!validateDate($start_date)) {
        sendError("start_date must be in YYYY-MM-DD format", 400);
    }
    // TODO: Check if week_id already exists
    // Prepare and execute a SELECT query to check for duplicates
    // If duplicate found, return error response with 409 status (Conflict)
    $stmt = $db->prepare("SELECT 1 FROM weeks WHERE week_id = :week_id LIMIT 1");
    $stmt->bindValue(':week_id', $week_id);
    $stmt->execute();
    if ($stmt->fetch()) {
        sendError("week_id already exists", 409);
    }
    // TODO: Handle links array
    // If links is provided and is an array, encode it to JSON using json_encode()
    // If links is not provided, use an empty array []
    $linksJson = json_encode(is_array($links) ? $links : []);
    // TODO: Prepare INSERT query
    // INSERT INTO weeks (week_id, title, start_date, description, links) VALUES (?, ?, ?, ?, ?)
    $sql = "INSERT INTO weeks (week_id, title, start_date, description, links, created_at, updated_at)
            VALUES (:week_id, :title, :start_date, :description, :links, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
    // TODO: Bind parameters
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':week_id', $week_id);
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':start_date', $start_date);
    $stmt->bindValue(':description', $description);
    $stmt->bindValue(':links', $linksJson);
    // TODO: Execute the query
    // TODO: Check if insert was successful
    // If yes, return success response with 201 status (Created) and the new week data
    // If no, return error response with 500 status
    if ($stmt->execute()) {
        $newWeek = [
            'week_id' => $week_id,
            'title' => $title,
            'start_date' => $start_date,
            'description' => $description,
            'links' => json_decode($linksJson, true)
        ];
        sendResponse(true, "Week created", $newWeek, 201);
    } else {
        sendError("Failed to create week", 500);
    }
}


/**
 * Function: Update an existing week
 * Method: PUT
 * Resource: weeks
 * 
 * Required JSON Body:
 *   - week_id: The week identifier (to identify which week to update)
 *   - title: Updated week title (optional)
 *   - start_date: Updated start date (optional)
 *   - description: Updated description (optional)
 *   - links: Updated array of links (optional)
 */
function updateWeek($db, $data) {
    // TODO: Validate that week_id is provided
    // If not, return error response with 400 status
    if (!is_array($data)) {
        sendError("Invalid JSON body", 400);
    }
    $week_id = sanitizeInput($data['week_id'] ?? '');
    if (!$week_id) {
        sendError("week_id is required to update", 400);
    }
    // TODO: Check if week exists
    // Prepare and execute a SELECT query to find the week
    // If not found, return error response with 404 status
    $stmt = $db->prepare("SELECT * FROM weeks WHERE week_id = :week_id LIMIT 1");
    $stmt->bindValue(':week_id', $week_id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        sendError("Week not found", 404);
    }
    // TODO: Build UPDATE query dynamically based on provided fields
    // Initialize an array to hold SET clauses
    // Initialize an array to hold values for binding
    $fields = [];
    $params = [];

    if (isset($data['title'])) {
        $fields[] = "title = :title";
        $params[':title'] = sanitizeInput($data['title']);
    }
    // TODO: Check which fields are provided and add to SET clauses
    // If title is provided, add "title = ?"
    // If start_date is provided, validate format and add "start_date = ?"
    // If description is provided, add "description = ?"
    // If links is provided, encode to JSON and add "links = ?"
     if (isset($data['start_date'])) {
        if (!validateDate($data['start_date'])) {
            sendError("start_date must be YYYY-MM-DD", 400);
        }
        $fields[] = "start_date = :start_date";
        $params[':start_date'] = $data['start_date'];
    }
    if (isset($data['description'])) {
        $fields[] = "description = :description";
        $params[':description'] = sanitizeInput($data['description']);
    }
    if (array_key_exists('links', $data)) {
        $linksJson = json_encode(is_array($data['links']) ? $data['links'] : []);
        $fields[] = "links = :links";
        $params[':links'] = $linksJson;
    }
    // TODO: If no fields to update, return error response with 400 status
    if (count($fields) === 0) {
        sendError("No fields to update", 400);
    }
    // TODO: Add updated_at timestamp to SET clauses
    // Add "updated_at = CURRENT_TIMESTAMP"
    $fields[] = "updated_at = CURRENT_TIMESTAMP";
    // TODO: Build the complete UPDATE query
    // UPDATE weeks SET [clauses] WHERE week_id = ?
     $sql = "UPDATE weeks SET " . implode(", ", $fields) . " WHERE week_id = :week_id";
    // TODO: Prepare the query
    $stmt = $db->prepare($sql);
    // TODO: Bind parameters dynamically
    // Bind values array and then bind week_id at the end
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':week_id', $week_id);
    // TODO: Execute the query
    // TODO: Check if update was successful
    if ($stmt->execute()) {
        $stmt = $db->prepare("SELECT week_id, title, start_date, description, links, created_at, updated_at FROM weeks WHERE week_id = :week_id LIMIT 1");
        $stmt->bindValue(':week_id', $week_id);
        $stmt->execute();
        $week = $stmt->fetch(PDO::FETCH_ASSOC);
        $week['links'] = $week['links'] ? json_decode($week['links'], true) : [];
    // If yes, return success response with updated week data
    sendResponse(true, "Week updated", $week, 200);
    } else {
    // If no, return error response with 500 status
    sendError("Failed to update week", 500);
    }
}


/**
 * Function: Delete a week
 * Method: DELETE
 * Resource: weeks
 * 
 * Query Parameters or JSON Body:
 *   - week_id: The week identifier
 */
function deleteWeek($db, $weekId) {
    // TODO: Validate that week_id is provided
    // If not, return error response with 400 status
     if (!$weekId) {
        sendError("week_id is required to delete", 400);
    }
    // TODO: Check if week exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
    $stmt = $db->prepare("SELECT 1 FROM weeks WHERE week_id = :week_id LIMIT 1");
    $stmt->bindValue(':week_id', $weekId);
    $stmt->execute();
    if (!$stmt->fetch()) {
        sendError("Week not found", 404);
    }
    // TODO: Delete associated comments first (to maintain referential integrity)
    // Prepare DELETE query for comments table
    // DELETE FROM comments WHERE week_id = ?
    $stmt = $db->prepare("DELETE FROM comments WHERE week_id = :week_id");
    $stmt->bindValue(':week_id', $weekId);
    // TODO: Execute comment deletion query
     $stmt->execute();
    // TODO: Prepare DELETE query for week
    // DELETE FROM weeks WHERE week_id = ?
     $stmt = $db->prepare("DELETE FROM weeks WHERE week_id = :week_id");
    // TODO: Bind the week_id parameter
     $stmt->bindValue(':week_id', $weekId);
    // TODO: Execute the query
    // TODO: Check if delete was successful
    // If yes, return success response with message indicating week and comments deleted
    // If no, return error response with 500 status
    if ($stmt->execute()) {
        sendResponse(true, "Week and associated comments deleted", ['week_id' => $weekId], 200);
    } else {
        sendError("Failed to delete week", 500);
    }
}


// ============================================================================
// COMMENTS CRUD OPERATIONS
// ============================================================================

/**
 * Function: Get all comments for a specific week
 * Method: GET
 * Resource: comments
 * 
 * Query Parameters:
 *   - week_id: The week identifier to get comments for
 */
function getCommentsByWeek($db, $weekId) {
    // TODO: Validate that week_id is provided
    // If not, return error response with 400 status
    if (!$weekId) {
        sendError("week_id is required to fetch comments", 400);
    }
    // TODO: Prepare SQL query to select comments for the week
    // SELECT id, week_id, author, text, created_at FROM comments WHERE week_id = ? ORDER BY created_at ASC
    $stmt = $db->prepare("SELECT id, week_id, author, text, created_at FROM comments WHERE week_id = :week_id ORDER BY created_at ASC");
    // TODO: Bind the week_id parameter
    $stmt->bindValue(':week_id', $weekId);
    // TODO: Execute the query
    $stmt->execute();
    // TODO: Fetch all results as an associative array
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // TODO: Return JSON response with success status and data
    // Even if no comments exist, return an empty array
    sendResponse(true, "Comments fetched", $comments, 200);
}


/**
 * Function: Create a new comment
 * Method: POST
 * Resource: comments
 * 
 * Required JSON Body:
 *   - week_id: The week identifier this comment belongs to
 *   - author: Comment author name
 *   - text: Comment text content
 */
function createComment($db, $data) {
    // TODO: Validate required fields
    // Check if week_id, author, and text are provided
    // If any field is missing, return error response with 400 status
     if (!is_array($data)) {
        sendError("Invalid JSON body", 400);
    }
    // TODO: Sanitize input data
    // Trim whitespace from all fields
    $week_id = sanitizeInput($data['week_id'] ?? '');
    $author = sanitizeInput($data['author'] ?? '');
    $text = sanitizeInput($data['text'] ?? '');
    // TODO: Validate that text is not empty after trimming
    // If empty, return error response with 400 status
     if (!$week_id || !$author || !$text) {
        sendError("Missing required fields: week_id, author, text", 400);
    }
    // TODO: Check if the week exists
    // Prepare and execute a SELECT query on weeks table
    // If week not found, return error response with 404 status
    $stmt = $db->prepare("SELECT 1 FROM weeks WHERE week_id = :week_id LIMIT 1");
    $stmt->bindValue(':week_id', $week_id);
    $stmt->execute();
    if (!$stmt->fetch()) {
        sendError("Referenced week not found", 404);
    }
    // TODO: Prepare INSERT query
    // INSERT INTO comments (week_id, author, text) VALUES (?, ?, ?)
    $sql = "INSERT INTO comments (week_id, author, text, created_at) VALUES (:week_id, :author, :text, CURRENT_TIMESTAMP)";
    $stmt = $db->prepare($sql);
    // TODO: Bind parameters
    $stmt->bindValue(':week_id', $week_id);
    $stmt->bindValue(':author', $author);
    $stmt->bindValue(':text', $text);
    // TODO: Execute the query
    
    // TODO: Check if insert was successful
    // If yes, get the last insert ID and return success response with 201 status
    // Include the new comment data in the response
    // If no, return error response with 500 status
    if ($stmt->execute()) {
        $insertId = $db->lastInsertId();
        $newComment = [
            'id' => (int)$insertId,
            'week_id' => $week_id,
            'author' => $author,
            'text' => $text,
        ];
        sendResponse(true, "Comment created", $newComment, 201);
    } else {
        sendError("Failed to create comment", 500);
    }
}


/**
 * Function: Delete a comment
 * Method: DELETE
 * Resource: comments
 * 
 * Query Parameters or JSON Body:
 *   - id: The comment ID to delete
 */
function deleteComment($db, $commentId) {
    // TODO: Validate that id is provided
    // If not, return error response with 400 status
     if (!$commentId) {
        sendError("Comment id is required", 400);
    }
    // TODO: Check if comment exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
    $stmt = $db->prepare("SELECT 1 FROM comments WHERE id = :id LIMIT 1");
    $stmt->bindValue(':id', $commentId);
    $stmt->execute();
    if (!$stmt->fetch()) {
        sendError("Comment not found", 404);
    }
    // TODO: Prepare DELETE query
    // DELETE FROM comments WHERE id = ?
     $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");
    // TODO: Bind the id parameter
    $stmt->bindValue(':id', $commentId);
    // TODO: Execute the query
    
    // TODO: Check if delete was successful
    // If yes, return success response
    // If no, return error response with 500 status
    if ($stmt->execute()) {
        sendResponse(true, "Comment deleted", ['id' => (int)$commentId], 200);
    } else {
        sendError("Failed to delete comment", 500);
    }
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Determine the resource type from query parameters
    // Get 'resource' parameter (?resource=weeks or ?resource=comments)
    // If not provided, default to 'weeks'
     $resource = $_GET['resource'] ?? 'weeks';
    $id = $_GET['id'] ?? null;
    $week_id = $_GET['week_id'] ?? null ;
    
    // Route based on resource type and HTTP method
    
    // ========== WEEKS ROUTES ==========
    if ($resource === 'weeks') {
        
        if ($method === 'GET') {
            // TODO: Check if week_id is provided in query parameters
            // If yes, call getWeekById()
            // If no, call getAllWeeks() to get all weeks (with optional search/sort)
            if ($id) {
                getWeekById($conn, $id);
            } 
            else {
                getAllWeeks($conn);
            }
        } elseif ($method === 'POST') {
            // TODO: Call createWeek() with the decoded request body
            $data = json_decode(file_get_contents("php://input"), true);
            createWeek($conn, $data);
        } elseif ($method === 'PUT') {
            // TODO: Call updateWeek() with the decoded request body
            if (!$id) {
                sendError("Missing week id", 400);
            }
            $data = json_decode(file_get_contents("php://input"), true);
            updateWeek($conn, $id, $data);
        } elseif ($method === 'DELETE') {
            // TODO: Get week_id from query parameter or request body
            // Call deleteWeek()
            if (!$id) {
                sendError("Missing week id", 400);
            }

            deleteWeek($conn, $id);
        } else {
            // TODO: Return error for unsupported methods
            // Set HTTP status to 405 (Method Not Allowed)
            sendError("Method not allowed", 405);
        }
    }
    
    // ========== COMMENTS ROUTES ==========
    elseif ($resource === 'comments') {
        
        if ($method === 'GET') {
            // TODO: Get week_id from query parameters
            // Call getCommentsByWeek()
            if (!$id) {
                sendError("Missing week id", 400);
            }

            getCommentsByWeek($conn, $id);
        } elseif ($method === 'POST') {
            // TODO: Call createComment() with the decoded request body
             $data = json_decode(file_get_contents("php://input"), true);
            createComment($conn, $data);
        } elseif ($method === 'DELETE') {
            // TODO: Get comment id from query parameter or request body
            // Call deleteComment()
            if (!$id) {
                sendError("Missing comment id", 400);
            }
            deleteComment($conn, $id);
        } else {
            // TODO: Return error for unsupported methods
            // Set HTTP status to 405 (Method Not Allowed)
            sendError("Method not allowed", 405);
        }
    }
    
    // ========== INVALID RESOURCE ==========
    else {
        // TODO: Return error for invalid resource
        // Set HTTP status to 400 (Bad Request)
        // Return JSON error message: "Invalid resource. Use 'weeks' or 'comments'"
        sendError("Invalid resource. Use 'weeks' or 'comments'", 400);
    }
 } catch (PDOException $e) {
    // TODO: Handle database errors
    // Log the error message (optional, for debugging)
    // error_log($e->getMessage());
     error_log($e->getMessage());
    // TODO: Return generic error response with 500 status
    // Do NOT expose database error details to the client
    // Return message: "Database error occurred"
    sendError("Database error occurred", 500);
} catch (Exception $e) {
    // TODO: Handle general errors
    // Log the error message (optional)
    error_log($e->getMessage());
    // Return error response with 500 status
    sendError("Internal server error", 500);
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response
 * 
 * @param mixed $data - Data to send (will be JSON encoded)
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
    // Use http_response_code($statusCode)
    http_response_code($statusCode);
    // TODO: Echo JSON encoded data
    // Use json_encode($data)
     echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);
    // TODO: Exit to prevent further execution
    exit();
}


/**
 * Helper function to send error response
 * 
 * @param string $message - Error message
 * @param int $statusCode - HTTP status code
 */
function sendError($message, $statusCode = 400) {
    // TODO: Create error response array
    // Structure: ['success' => false, 'error' => $message]
    $response = [
        'success' => false,
        'error'   => $message
    ];
    // TODO: Call sendResponse() with the error array and status code
    sendResponse($response, $statusCode);
}


/**
 * Helper function to validate date format (YYYY-MM-DD)
 * 
 * @param string $date - Date string to validate
 * @return bool - True if valid, false otherwise
 */
function validateDate($date) {
    // TODO: Use DateTime::createFromFormat() to validate
    // Format: 'Y-m-d'
    // Check that the created date matches the input string
    // Return true if valid, false otherwise
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}


/**
 * Helper function to sanitize input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Trim whitespace
    $data = trim($data);
    // TODO: Strip HTML tags using strip_tags()
    $data = strip_tags($data);
    // TODO: Convert special characters using htmlspecialchars()
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    // TODO: Return sanitized data
     return $data;
}


/**
 * Helper function to validate allowed sort fields
 * 
 * @param string $field - Field name to validate
 * @param array $allowedFields - Array of allowed field names
 * @return bool - True if valid, false otherwise
 */
function isValidSortField($field, $allowedFields) {
    // TODO: Check if $field exists in $allowedFields array
    // Use in_array()
    // Return true if valid, false otherwise
     return in_array($field, $allowedFields, true);
}

?>
