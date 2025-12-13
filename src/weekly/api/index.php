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
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
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
$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? 'weeks';
$id = $_GET['id'] ?? null;
$week_id = $_GET['week_id'] ?? null;
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// TODO: Get the HTTP request method
// Use $_SERVER['REQUEST_METHOD']


// TODO: Get the request body for POST and PUT requests
// Use file_get_contents('php://input') to get raw POST data
// Decode JSON data using json_decode()

// TODO: Parse query parameters
// Get the 'resource' parameter to determine if request is for weeks or comments
// Example: ?resource=weeks or ?resource=comments
function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data"    => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}
function sendError($message, $statusCode = 400) {
    sendResponse(false, $message, null, $statusCode);
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
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
    // TODO: Start building the SQL query
    // Base query: SELECT week_id, title, start_date, description, links, created_at FROM weeks
    // TODO: Check if search parameter exists
    // If yes, add WHERE clause using LIKE for title and description
    // Example: WHERE title LIKE ? OR description LIKE ?
    $search = $_GET['search'] ?? null;
    $sort = $_GET['sort'] ?? 'start_date';
    $order = strtolower($_GET['order'] ?? 'asc');

    $allowedSortFields = ['title', 'start_date', 'created_at'];
    if (!in_array($sort, $allowedSortFields)) $sort = 'start_date';
    if (!in_array($order, ['asc', 'desc'])) $order = 'asc';
    $sql = "SELECT week_id, title, start_date, description, links, created_at FROM weeks";
    $params = [];

    if ($search) {
        $sql .= " WHERE title LIKE :search OR description LIKE :search";
        $params[':search'] = "%$search%";
    }

    $sql .= " ORDER BY $sort $order";
    $stmt = $db->prepare($sql);
    if ($search) $stmt->bindValue(':search', $params[':search']);

    $stmt->execute();
    $weeks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($weeks as &$week) {
        $week['links'] = $week['links'] ? json_decode($week['links'], true) : [];
    }

    sendResponse(true, "Weeks retrieved successfully", $weeks);

}

    // TODO: Check if sort parameter exists
    // Validate sort field to prevent SQL injection (only allow: title, start_date, created_at)
    // If invalid, use default sort field (start_date)
    

    // TODO: Check if order parameter exists
    // Validate order to prevent SQL injection (only allow: asc, desc)
    // If invalid, use default order (asc)
    
    
    // TODO: Add ORDER BY clause to the query
    
    // TODO: Prepare the SQL query using PDO
   
    // TODO: Bind parameters if using search
    // Use wildcards for LIKE: "%{$searchTerm}%"
    
    
    // TODO: Execute the query
    
    // TODO: Fetch all results as an associative array
   
    // TODO: Process each week's links field
    // Decode the JSON string back to an array using json_decode()
    
    // TODO: Return JSON response with success status and data
    // Use sendResponse() helper function



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
    // TODO: Prepare SQL query to select week by week_id
    // SELECT week_id, title, start_date, description, links, created_at FROM weeks WHERE week_id = ?
    // TODO: Bind the week_id parameter
    // TODO: Execute the query
    // TODO: Fetch the result
    // TODO: Check if week exists
    // If yes, decode the links JSON and return success response with week data
    // If no, return error response with 404 status
if (!$weekId) sendError("week_id is required", 400);

    $stmt = $db->prepare("SELECT week_id, title, start_date, description, links, created_at FROM weeks WHERE week_id = :week_id LIMIT 1");
    $stmt->bindValue(':week_id', $weekId);
    $stmt->execute();
    $week = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$week) sendError("Week not found", 404);

    $week['links'] = $week['links'] ? json_decode($week['links'], true) : [];
    sendResponse(true, "Week retrieved successfully", $week);
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
    // TODO: Sanitize input data
    // Trim whitespace from title, description, and week_id
    // TODO: Validate start_date format
    // Use a regex or DateTime::createFromFormat() to verify YYYY-MM-DD format
    // If invalid, return error response with 400 status
    // TODO: Check if week_id already exists
    // Prepare and execute a SELECT query to check for duplicates
    // If duplicate found, return error response with 409 status (Conflict)
    // TODO: Handle links array
    // If links is provided and is an array, encode it to JSON using json_encode()
    // If links is not provided, use an empty array []
    // TODO: Prepare INSERT query
    // INSERT INTO weeks (week_id, title, start_date, description, links) VALUES (?, ?, ?, ?, ?)
    // TODO: Bind parameters
    // TODO: Execute the query
    // TODO: Check if insert was successful
    // If yes, return success response with 201 status (Created) and the new week data
    // If no, return error response with 500 status
    if (!is_array($data)) sendError("Invalid JSON body", 400);

    $week_id = sanitizeInput($data['week_id'] ?? '');
    $title = sanitizeInput($data['title'] ?? '');
    $start_date = $data['start_date'] ?? '';
    $description = sanitizeInput($data['description'] ?? '');
    $links = $data['links'] ?? [];

    if (!$week_id || !$title || !$start_date || !$description) {
        sendError("Missing required fields", 400);
    }
    if (!validateDate($start_date)) sendError("start_date must be YYYY-MM-DD", 400);

    $stmt = $db->prepare("SELECT 1 FROM weeks WHERE week_id = :week_id LIMIT 1");
    $stmt->bindValue(':week_id', $week_id);
    $stmt->execute();
    if ($stmt->fetch()) sendError("week_id already exists", 409);

    $linksJson = json_encode(is_array($links) ? $links : []);
    $stmt = $db->prepare("INSERT INTO weeks (week_id, title, start_date, description, links, created_at, updated_at)
                          VALUES (:week_id, :title, :start_date, :description, :links, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    $stmt->bindValue(':week_id', $week_id);
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':start_date', $start_date);
    $stmt->bindValue(':description', $description);
    $stmt->bindValue(':links', $linksJson);

    if ($stmt->execute()) {
        $newWeek = [
            'week_id' => $week_id,
            'title' => $title,
            'start_date' => $start_date,
            'description' => $description,
            'links' => json_decode($linksJson, true)
        ];
        sendResponse(true, "Week created successfully", $newWeek, 201);
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
    // TODO: Check if week exists
    // Prepare and execute a SELECT query to find the week
    // If not found, return error response with 404 status
    // TODO: Build UPDATE query dynamically based on provided fields
    // Initialize an array to hold SET clauses
    // Initialize an array to hold values for binding
    // TODO: Check which fields are provided and add to SET clauses
    // If title is provided, add "title = ?"
    // If start_date is provided, validate format and add "start_date = ?"
    // If description is provided, add "description = ?"
    // If links is provided, encode to JSON and add "links = ?"
    // TODO: If no fields to update, return error response with 400 status
    // TODO: Add updated_at timestamp to SET clauses
    // Add "updated_at = CURRENT_TIMESTAMP"
    // TODO: Build the complete UPDATE query
    // UPDATE weeks SET [clauses] WHERE week_id = ?
    // TODO: Prepare the query
    // TODO: Bind parameters dynamically
    // Bind values array and then bind week_id at the end
    // TODO: Execute the query
    // TODO: Check if update was successful
    // If yes, return success response with updated week data
    // If no, return error response with 500 status
    if (!is_array($data)) sendError("Invalid JSON body", 400);
    $week_id = sanitizeInput($data['week_id'] ?? '');
    if (!$week_id) sendError("week_id is required to update", 400);

    $stmt = $db->prepare("SELECT * FROM weeks WHERE week_id = :week_id LIMIT 1");
    $stmt->bindValue(':week_id', $week_id);
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) sendError("Week not found", 404);

    $fields = [];
    $params = [];
    if (isset($data['title'])) { $fields[] = "title = :title"; $params[':title'] = sanitizeInput($data['title']); }
    if (isset($data['start_date'])) {
        if (!validateDate($data['start_date'])) sendError("start_date must be YYYY-MM-DD", 400);
        $fields[] = "start_date = :start_date"; $params[':start_date'] = $data['start_date'];
    }
    if (isset($data['description'])) { $fields[] = "description = :description"; $params[':description'] = sanitizeInput($data['description']); }
    if (array_key_exists('links', $data)) { $fields[] = "links = :links"; $params[':links'] = json_encode(is_array($data['links']) ? $data['links'] : []); }

    if (count($fields) === 0) sendError("No fields to update", 400);
    $fields[] = "updated_at = CURRENT_TIMESTAMP";
    $sql = "UPDATE weeks SET " . implode(", ", $fields) . " WHERE week_id = :week_id";
    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':week_id', $week_id);

    if ($stmt->execute()) {
        $stmt = $db->prepare("SELECT week_id, title, start_date, description, links, created_at, updated_at FROM weeks WHERE week_id = :week_id LIMIT 1");
        $stmt->bindValue(':week_id', $week_id);
        $stmt->execute();
        $week = $stmt->fetch(PDO::FETCH_ASSOC);
        $week['links'] = $week['links'] ? json_decode($week['links'], true) : [];
        sendResponse(true, "Week updated successfully", $week);
        } else {
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
    // TODO: Check if week exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
    // TODO: Delete associated comments first (to maintain referential integrity)
    // Prepare DELETE query for comments table
    // DELETE FROM comments WHERE week_id = ?
    // TODO: Execute comment deletion query
    // TODO: Prepare DELETE query for week
    // DELETE FROM weeks WHERE week_id = ?
    // TODO: Bind the week_id parameter
    // TODO: Execute the query
    // TODO: Check if delete was successful
    // If yes, return success response with message indicating week and comments deleted
    // If no, return error response with 500 status
   if (!$weekId) sendError("week_id is required to delete", 400);

    $stmt = $db->prepare("SELECT 1 FROM weeks WHERE week_id = :week_id LIMIT 1");
    $stmt->bindValue(':week_id', $weekId);
    $stmt->execute();
    if (!$stmt->fetch()) sendError("Week not found", 404);

    $stmt = $db->prepare("DELETE FROM comments WHERE week_id = :week_id");
    $stmt->bindValue(':week_id', $weekId);
    $stmt->execute();

    $stmt = $db->prepare("DELETE FROM weeks WHERE week_id = :week_id");
    $stmt->bindValue(':week_id', $weekId);
    if ($stmt->execute()) {
        sendResponse(true, "Week deleted successfully");
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
    // TODO: Prepare SQL query to select comments for the week
    // SELECT id, week_id, author, text, created_at FROM comments WHERE week_id = ? ORDER BY created_at ASC
    // TODO: Bind the week_id parameter
    // TODO: Execute the query
    // TODO: Fetch all results as an associative array
    // TODO: Return JSON response with success status and data
    // Even if no comments exist, return an empty array
    if (!$weekId) sendError("week_id is required", 400);

    $stmt = $db->prepare("SELECT id, week_id, author, text, created_at FROM comments WHERE week_id = :week_id ORDER BY created_at ASC");
    $stmt->bindValue(':week_id', $weekId);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(true, "Comments retrieved successfully", $comments);

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
    // TODO: Sanitize input data
    // Trim whitespace from all fields
    // TODO: Validate that text is not empty after trimming
    // If empty, return error response with 400 status
    // TODO: Check if the week exists
    // Prepare and execute a SELECT query on weeks table
    // If week not found, return error response with 404 status
    // TODO: Prepare INSERT query
    // INSERT INTO comments (week_id, author, text) VALUES (?, ?, ?)
    // TODO: Bind parameters
    // TODO: Execute the query
    // TODO: Check if insert was successful
    // If yes, get the last insert ID and return success response with 201 status
    // Include the new comment data in the response
    // If no, return error response with 500 status
        if (!is_array($data)) sendError("Invalid JSON body", 400);

    $week_id = sanitizeInput($data['week_id'] ?? '');
    $author = sanitizeInput($data['author'] ?? '');
    $text = sanitizeInput($data['text'] ?? '');

    if (!$week_id || !$author || !$text) sendError("Missing required fields", 400);

    $stmt = $db->prepare("SELECT 1 FROM weeks WHERE week_id = :week_id LIMIT 1");
    $stmt->bindValue(':week_id', $week_id);
    $stmt->execute();
    if (!$stmt->fetch()) sendError("Referenced week not found", 404);

    $stmt = $db->prepare("INSERT INTO comments (week_id, author, text, created_at) VALUES (:week_id, :author, :text, CURRENT_TIMESTAMP)");
    $stmt->bindValue(':week_id', $week_id);
    $stmt->bindValue(':author', $author);
    $stmt->bindValue(':text', $text);

    if ($stmt->execute()) {
        $newComment = [
            'id' => (int)$db->lastInsertId(),
            'week_id' => $week_id,
            'author' => $author,
            'text' => $text
        ];
        sendResponse(true, "Comment created successfully", $newComment, 201);
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
    // TODO: Check if comment exists
    // Prepare and execute a SELECT query
    // If not found, return error response with 404 status
    // TODO: Prepare DELETE query
    // DELETE FROM comments WHERE id = ?
    // TODO: Bind the id paramete
    // TODO: Execute the query
    // TODO: Check if delete was successful
    // If yes, return success response
    // If no, return error response with 500 status
    if (!$commentId) sendError("Comment id is required", 400);

    $stmt = $db->prepare("SELECT 1 FROM comments WHERE id = :id LIMIT 1");
    $stmt->bindValue(':id', $commentId);
    $stmt->execute();
    if (!$stmt->fetch()) sendError("Comment not found", 404);

    $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");
    $stmt->bindValue(':id', $commentId);
    if ($stmt->execute()) {
        sendResponse(true, "Comment deleted successfully");
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
    //$resource = $_GET['resource'] ?? 'weeks';
    //$id = $_GET['id'] ?? null;
    //$week_id = $_GET['week_id'] ?? null ;
    
    // Route based on resource type and HTTP method
    
    // ========== WEEKS ROUTES ==========
    if ($resource === 'weeks') {
        
        if ($method === 'GET') {
            // TODO: Check if week_id is provided in query parameters
            // If yes, call getWeekById()
            // If no, call getAllWeeks() to get all weeks (with optional search/sort)
            if ($week_id) {
                getWeekById($db, $week_id);
            } 
            else {
                getAllWeeks($db);
            }
        } elseif ($method === 'POST') {
            // TODO: Call createWeek() with the decoded request body
            createWeek($db, $data);
        } elseif ($method === 'PUT') {
            // TODO: Call updateWeek() with the decoded request body
             updateWeek($db, $data);
        } elseif ($method === 'DELETE') {
            // TODO: Get week_id from query parameter or request body
            // Call deleteWeek()
            if (!$week_id && isset($data['week_id'])) {
                $week_id = $data['week_id'];
            }
             if (!$week_id) sendError("Missing week_id", 400);
            deleteWeek($db, $week_id);
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
             if (!$week_id) sendError("Missing week_id", 400);
            getCommentsByWeek($db, $week_id);
        } elseif ($method === 'POST') {
            // TODO: Call createComment() with the decoded request body
            createComment($db, $data);
        } elseif ($method === 'DELETE') {
            // TODO: Get comment id from query parameter or request body
            // Call deleteComment()
            if (!$id && isset($data['id'])) {
                $id = $data['id'];
            }
            if (!$id) sendError("Missing comment id", 400);
            deleteComment($db, $id);
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
//function sendResponse($success , $message , $data = null, $statusCode = 200) {
    // TODO: Set HTTP response code
    // Use http_response_code($statusCode)
    // TODO: Echo JSON encoded data
    // Use json_encode($data)
    //http_response_code($statusCode);
    //echo json_encode([
        //"success" => $success,
        //"message" => $message,
        //"data" => $data
    //], JSON_UNESCAPED_UNICODE);
    // TODO: Exit to prevent further execution
    //exit();
//}


/**
 * Helper function to send error response
 * 
 * @param string $message - Error message
 * @param int $statusCode - HTTP status code
 */
//function sendError($message, $statusCode = 400) {
    // TODO: Create error response array
    // Structure: ['success' => false, 'error' => $message]
    
    // TODO: Call sendResponse() with the error array and status code
    
//}
//function sendError($message, $statusCode = 400) {
    //sendResponse(false, $message, null, $statusCode);
//}


/**
 * Helper function to validate date format (YYYY-MM-DD)
 * 
 * @param string $date - Date string to validate
 * @return bool - True if valid, false otherwise
 */
//function validateDate($date) {
    // TODO: Use DateTime::createFromFormat() to validate
    // Format: 'Y-m-d'
    // Check that the created date matches the input string
    // Return true if valid, false otherwise
    
//}


/**
 * Helper function to sanitize input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
//function sendError($message, $statusCode = 400) {
   
//}

    // TODO: Trim whitespace
    
    // TODO: Strip HTML tags using strip_tags()
   
    // TODO: Convert special characters using htmlspecialchars()
    
    // TODO: Return sanitized data
    


/**
 * Helper function to validate allowed sort fields
 * 
 * @param string $field - Field name to validate
 * @param array $allowedFields - Array of allowed field names
 * @return bool - True if valid, false otherwise
 */

    // TODO: Check if $field exists in $allowedFields array
    // Use in_array()
    // Return true if valid, false otherwise
    

?>
