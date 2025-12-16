<?php
ession_start();

$_SESSION['api_active'] = true;

/**
 * Discussion Board API
 * 
 * This is a RESTful API that handles all CRUD operations for the discussion board.
 * It manages both discussion topics and their replies.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structures (for reference):
 * 
 * Table: topics
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - topic_id (VARCHAR(50), UNIQUE) - The topic's unique identifier (e.g., "topic_1234567890")
 *   - subject (VARCHAR(255)) - The topic subject/title
 *   - message (TEXT) - The main topic message
 *   - author (VARCHAR(100)) - The author's name
 *   - created_at (TIMESTAMP) - When the topic was created
 * 
 * Table: replies
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - reply_id (VARCHAR(50), UNIQUE) - The reply's unique identifier (e.g., "reply_1234567890")
 *   - topic_id (VARCHAR(50)) - Foreign key to topics.topic_id
 *   - text (TEXT) - The reply message
 *   - author (VARCHAR(100)) - The reply author's name
 *   - created_at (TIMESTAMP) - When the reply was created
 * 
 * API Endpoints:
 * 
 * Topics:
 *   GET    /api/discussion.php?resource=topics              - Get all topics (with optional search)
 *   GET    /api/discussion.php?resource=topics&id={id}      - Get single topic
 *   POST   /api/discussion.php?resource=topics              - Create new topic
 *   PUT    /api/discussion.php?resource=topics              - Update a topic
 *   DELETE /api/discussion.php?resource=topics&id={id}      - Delete a topic
 * 
 * Replies:
 *   GET    /api/discussion.php?resource=replies&topic_id={id} - Get all replies for a topic
 *   POST   /api/discussion.php?resource=replies              - Create new reply
 *   DELETE /api/discussion.php?resource=replies&id={id}      - Delete a reply
 * 
 * Response Format: JSON
 */

// TODO: Set headers for JSON response and CORS
// Set Content-Type to application/json
// Allow cross-origin requests (CORS) if needed
// Allow specific HTTP methods (GET, POST, PUT, DELETE, OPTIONS)
// Allow specific headers (Content-Type, Authorization)
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
/**
 * Discussion Board API
 * 
 * This is a RESTful API that handles all CRUD operations for the discussion board.
 * It manages both discussion topics and their replies.
 * It uses PDO to interact with a MySQL database.
 * 
 * Database Table Structures (for reference):
 * 
 * Table: topics
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - topic_id (VARCHAR(50), UNIQUE) - The topic's unique identifier (e.g., "topic_1234567890")
 *   - subject (VARCHAR(255)) - The topic subject/title
 *   - message (TEXT) - The main topic message
 *   - author (VARCHAR(100)) - The author's name
 *   - created_at (TIMESTAMP) - When the topic was created
 * 
 * Table: replies
 * Columns:
 *   - id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   - reply_id (VARCHAR(50), UNIQUE) - The reply's unique identifier (e.g., "reply_1234567890")
 *   - topic_id (VARCHAR(50)) - Foreign key to topics.topic_id
 *   - text (TEXT) - The reply message
 *   - author (VARCHAR(100)) - The reply author's name
 *   - created_at (TIMESTAMP) - When the reply was created
 * 
 * API Endpoints:
 * 
 * Topics:
 *   GET    /api/discussion.php?resource=topics              - Get all topics (with optional search)
 *   GET    /api/discussion.php?resource=topics&id={id}      - Get single topic
 *   POST   /api/discussion.php?resource=topics              - Create new topic
 *   PUT    /api/discussion.php?resource=topics              - Update a topic
 *   DELETE /api/discussion.php?resource=topics&id={id}      - Delete a topic
 * 
 * Replies:
 *   GET    /api/discussion.php?resource=replies&topic_id={id} - Get all replies for a topic
 *   POST   /api/discussion.php?resource=replies              - Create new reply
 *   DELETE /api/discussion.php?resource=replies&id={id}      - Delete a reply
 * 
 * Response Format: JSON
 */

// TODO: Set headers for JSON response and CORS
// Set Content-Type to application/json
// Allow cross-origin requests (CORS) if needed
// Allow specific HTTP methods (GET, POST, PUT, DELETE, OPTIONS)
// Allow specific headers (Content-Type, Authorization)
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");  
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


// TODO: Handle preflight OPTIONS request
// If the request method is OPTIONS, return 200 status and exit
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


// TODO: Include the database connection class
// Assume the Database class has a method getConnection() that returns a PDO instance
require_once '../config/database.php';



// TODO: Get the PDO database connection
// $db = $database->getConnection();
$database = new Database();
$db = $database->getConnection();


// TODO: Get the HTTP request method
// Use $_SERVER['REQUEST_METHOD']
$method = $_SERVER['REQUEST_METHOD'];


// TODO: Get the request body for POST and PUT requests
// Use file_get_contents('php://input') to get raw POST data
// Decode JSON data using json_decode()
$inputData = null;
if ($method === 'POST' || $method === 'PUT') {
    $rawData = file_get_contents('php://input');
    $inputData = json_decode($rawData, true);
}


// TODO: Parse query parameters for filtering and searching
$resource = isset($_GET['resource']) ? $_GET['resource'] : null;



// ============================================================================
// TOPICS FUNCTIONS
// ============================================================================

/**
 * Function: Get all topics or search for specific topics
 * Method: GET
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by subject, message, or author
 *   - sort: Optional field to sort by (subject, author, created_at)
 *   - order: Optional sort order (asc or desc, default: desc)
 */
function getAllTopics($db) {
    $sql = "SELECT topic_id, subject, message, author, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at FROM topics";
    // TODO: Initialize base SQL query
    

    // Select topic_id, subject, message, author, and created_at (formatted as date)
    
    // TODO: Initialize an array to hold bound parameters
    
    
    // TODO: Check if search parameter exists in $_GET

    // If yes, add WHERE clause using LIKE for subject, message, OR author
    // Add the search term to the params array
    
    // TODO: Add ORDER BY clause
    // Check for sort and order parameters in $_GET
    // Validate the sort field (only allow: subject, author, created_at)
    // Validate order (only allow: asc, desc)
    // Default to ordering by created_at DESC
    
    // TODO: Prepare the SQL statement
    
    // TODO: Bind parameters if search was used
    // Loop through $params array and bind each parameter
    
    // TODO: Execute the query
    
    // TODO: Fetch all results as an associative array
    
    // TODO: Return JSON response with success status and data
    // Call sendResponse() helper function or echo json_encode directly
    $sql = "SELECT topic_id, subject, message, author,
            DATE_FORMAT(created_at, '%Y-%m-%d') AS created_at
            FROM topics";

    $params = [];

    if (!empty($_GET['search'])) {
        $sql .= " WHERE subject LIKE :search OR message LIKE :search OR author LIKE :search";
        $params[':search'] = "%" . $_GET['search'] . "%";
    }

    $allowedSort = ['subject', 'author', 'created_at'];
    $sort = $_GET['sort'] ?? 'created_at';
    $order = strtolower($_GET['order'] ?? 'desc');

    if (!in_array($sort, $allowedSort)) $sort = 'created_at';
    if (!in_array($order, ['asc', 'desc'])) $order = 'desc';

    $sql .= " ORDER BY $sort $order";

    $stmt = $db->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();

    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse([
        "success" => true,
        "data" => $topics
    ]);
}


/**
 * Function: Get a single topic by topic_id
 * Method: GET
 * 
 * Query Parameters:
 *   - id: The topic's unique identifier
 */
function getTopicById($db, $topicId) {
    // TODO: Validate that topicId is provided
    // If empty, return error with 400 status
    
    // TODO: Prepare SQL query to select topic by topic_id
    // Select topic_id, subject, message, author, and created_at
    
    // TODO: Prepare and bind the topic_id parameter
    
    // TODO: Execute the query
    
    // TODO: Fetch the result
    
    // TODO: Check if topic exists
    // If topic found, return success response with topic data
    // If not found, return error with 404 status

if (empty($topicId)) {
        sendResponse(["error" => "Topic ID is required"], 400);
    }

    $sql = "SELECT topic_id, subject, message, author,
            DATE_FORMAT(created_at, '%Y-%m-%d') AS created_at
            FROM topics
            WHERE topic_id = :topic_id";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':topic_id', $topicId);
    $stmt->execute();

    $topic = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($topic) {
        sendResponse(["success" => true, "data" => $topic]);
    } else {
        sendResponse(["error" => "Topic not found"], 404);
    }

}


/**
 * Function: Create a new topic
 * Method: POST
 * 
 * Required JSON Body:
 *   - topic_id: Unique identifier (e.g., "topic_1234567890")
 *   - subject: Topic subject/title
 *   - message: Main topic message
 *   - author: Author's name
 */
function createTopic($db, $data) {
    // TODO: Validate required fields
    // Check if topic_id, subject, message, and author are provided
    // If any required field is missing, return error with 400 status
    
    // TODO: Sanitize input data
    // Trim whitespace from all string fields
    // Use the sanitizeInput() helper function
    
    // TODO: Check if topic_id already exists
    // Prepare and execute a SELECT query to check for duplicate
    // If duplicate found, return error with 409 status (Conflict)
    
    // TODO: Prepare INSERT query
    // Insert topic_id, subject, message, and author
    // The created_at field should auto-populate with CURRENT_TIMESTAMP
    
    // TODO: Prepare the statement and bind parameters
    // Bind all the sanitized values
    
    // TODO: Execute the query
    
    // TODO: Check if insert was successful
    // If yes, return success response with 201 status (Created)
    // Include the topic_id in the response
    // If no, return error with 500 status

}


/**
 * Function: Update an existing topic
 * Method: PUT
 * 
 * Required JSON Body:
 *   - topic_id: The topic's unique identifier
 *   - subject: Updated subject (optional)
 *   - message: Updated message (optional)
 */
function updateTopic($db, $data) {
    // TODO: Validate that topic_id is provided
    // If not provided, return error with 400 status
    if (empty($data['topic_id'])) {
        sendResponse(['error' => 'topic_id is required'], 400);
    }
    
    // TODO: Check if topic exists
    $topicId = $data['topic_id'];
    // Prepare and execute a SELECT query
    // If not found, return error with 404 status
    $stmt = $db->prepare("SELECT * FROM topics WHERE topic_id = :topic_id");
    $stmt->bindParam(':topic_id', $topicId);
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        sendResponse(['error' => 'Topic not found'], 404);
    }
    
    // TODO: Build UPDATE query dynamically based on provided fields
    // Only update fields that are provided in the request
    $updates = [];
    $params = [':topic_id' => $topicId];
    if (isset($data['subject'])) {
        $updates[] = "subject = :subject";
        $params[':subject'] = $data['subject'];
    }
    if (isset($data['message'])) {
        $updates[] = "message = :message";
        $params[':message'] = $data['message'];
    }
    
    // TODO: Check if there are any fields to update
    // If $updates array is empty, return error
    if (empty($updates)) {
        sendResponse(['error' => 'No fields to update'], 400);
    }
    
    // TODO: Complete the UPDATE query
    $sql = "UPDATE topics SET " . implode(", ", $updates) . " WHERE topic_id = :topic_id";

    
    // TODO: Prepare statement and bind parameters
    $stmt = $db->prepare($sql);
    // Bind all parameters from the $params array

    // TODO: Execute the query
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    // TODO: Check if update was successful
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            sendResponse(['message' => 'Topic updated successfully']);
        } else {
            sendResponse(['message' => 'No changes made to the topic']);
        }
    } else {
        sendResponse(['error' => 'Failed to update topic'], 500);
    }
    // If yes, return success response
    // If no rows affected, return appropriate message
    // If error, return error with 500 status
}


/**
 * Function: Delete a topic
 * Method: DELETE
 * 
 * Query Parameters:
 *   - id: The topic's unique identifier
 */
function deleteTopic($db, $topicId) {
    if (empty($topicId)) {
        sendResponse(['error' => 'topic_id is required'], 400);
    }
    // TODO: Validate that topicId is provided
    $checkTopicStmt = $db->prepare("SELECT * FROM topics WHERE topic_id = :topic_id");
    $checkTopicStmt->bindParam(':topic_id', $topicId);
    $checkTopicStmt->execute();
    // If not, return error with 400 status
    
    // TODO: Check if topic exists
    if ($checkTopicStmt->rowCount() === 0) {
        sendResponse(['error' => 'Topic not found'], 404);
    }
    // Prepare and execute a SELECT query
    // If not found, return error with 404 status
    
    // TODO: Delete associated replies first (foreign key constraint)
    $deleteRepliesStmt = $db->prepare("DELETE FROM replies WHERE topic_id = :topic_id");
    $deleteRepliesStmt->bindParam(':topic_id', $topicId);
    $deleteRepliesStmt->execute();

    // Prepare DELETE query for replies table
    
    // TODO: Prepare DELETE query for the topic
    $deleteTopicStmt = $db->prepare("DELETE FROM topics WHERE topic_id = :topic_id");

    
    // TODO: Prepare, bind, and execute
    $deleteTopicStmt->bindParam(':topic_id', $topicId);
    $deleteTopicStmt->execute();

    
    // TODO: Check if delete was successful
    if ($deleteTopicStmt->rowCount() > 0) {
        sendResponse(['message' => 'Topic and associated replies deleted successfully']);
    } else {
        sendResponse(['error' => 'Failed to delete topic'], 500);
    }
    // If yes, return success response
    // If no, return error with 500 status
}


// ============================================================================
// REPLIES FUNCTIONS
// ============================================================================

/**
 * Function: Get all replies for a specific topic
 * Method: GET
 * 
 * Query Parameters:
 *   - topic_id: The topic's unique identifier
 */
function getRepliesByTopicId($db, $topicId) {
    // TODO: Validate that topicId is provided
    // If not provided, return error with 400 status
    if (empty($topicId)) {
        sendResponse(['error' => 'topic_id is required'], 400);
    }
    $checkTopicStmt = $db->prepare("SELECT * FROM topics WHERE topic_id = :topic_id");
    $checkTopicStmt->bindParam(':topic_id', $topicId);
    $checkTopicStmt->execute();
    // TODO: Prepare SQL query to select all replies for the topic
    $stmt = $db->prepare("SELECT reply_id, topic_id, text, author, DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') AS created_at FROM replies WHERE topic_id = :topic_id ORDER BY created_at ASC");

    // Select reply_id, topic_id, text, author, and created_at (formatted as date)
    // Order by created_at ASC (oldest first)
    
    // TODO: Prepare and bind the topic_id parameter
    $stmt->bindParam(':topic_id', $topicId);

    // TODO: Execute the query
    $stmt->execute();
    // TODO: Fetch all results as an associative array
    $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // TODO: Return JSON response
    sendResponse(['success' => true, 'data' => $replies]);
    // Even if no replies found, return empty array (not an error)
}


/**
 * Function: Create a new reply
 * Method: POST
 * 
 * Required JSON Body:
 *   - reply_id: Unique identifier (e.g., "reply_1234567890")
 *   - topic_id: The parent topic's identifier
 *   - text: Reply message text
 *   - author: Author's name
 */
function createReply($db, $data) {
    // TODO: Validate required fields
    // Check if reply_id, topic_id, text, and author are provided
    // If any field is missing, return error with 400 status
    if (empty($data['reply_id']) || empty($data['topic_id']) || empty($data['text']) || empty($data['author'])) {
        sendResponse(['error' => 'reply_id, topic_id, text, and author are required'], 400);
    }
    
    // TODO: Sanitize input data
    // Trim whitespace from all fields
    $replyId = trim($data['reply_id']);
    $topicId = trim($data['topic_id']);
    $text = trim($data['text']);
    $author = trim($data['author']);

    
    // TODO: Verify that the parent topic exists
    $checkTopicStmt = $db->prepare("SELECT * FROM topics WHERE topic_id = :topic_id");  
    // Prepare and execute SELECT query on topics table
    $checkTopicStmt->bindParam(':topic_id', $topicId);
    $checkTopicStmt->execute();
    if ($checkTopicStmt->rowCount() === 0) {
        sendResponse(['error' => 'Parent topic not found'], 404);
    }

    // If topic doesn't exist, return error with 404 status (can't reply to non-existent topic)

    
    // TODO: Check if reply_id already exists
    // Prepare and execute SELECT query to check for duplicate
    // If duplicate found, return error with 409 status
    $checkReplyStmt = $db->prepare("SELECT * FROM replies WHERE reply_id = :reply_id");
    $checkReplyStmt->bindParam(':reply_id', $replyId);
    $checkReplyStmt->execute();
    if ($checkReplyStmt->rowCount() > 0) {
        sendResponse(['error' => 'reply_id already exists'], 409);
    }

    
    // TODO: Prepare INSERT query
    // Insert reply_id, topic_id, text, and author
    $insertSql = "INSERT INTO replies (reply_id, topic_id, text, author) VALUES (:reply_id, :topic_id
, :text, :author)";

    
    // TODO: Prepare statement and bind parameters
    $stmt = $db->prepare($insertSql);
    $stmt->bindParam(':reply_id', $replyId);
    $stmt->bindParam(':topic_id', $topicId);
    $stmt->bindParam(':text', $text);
    $stmt->bindParam(':author', $author);

    
    // TODO: Execute the query
    if ($stmt->execute()) {
        sendResponse(['message' => 'Reply created successfully', 'reply_id' => $replyId], 201);
    } else {
        sendResponse(['error' => 'Failed to create reply'], 500);
    }
    
    // TODO: Check if insert was successful
    // If yes, return success response with 201 status
    // Include the reply_id in the response
    // If no, return error with 500 status


}


/**
 * Function: Delete a reply
 * Method: DELETE
 * 
 * Query Parameters:
 *   - id: The reply's unique identifier
 */
function deleteReply($db, $replyId) {
    // TODO: Validate that replyId is provided
    // If not, return error with 400 status
    if (empty($replyId)) {
        sendResponse(['error' => 'reply_id is required'], 400);
    }
    
    // TODO: Check if reply exists
    // Prepare and execute SELECT query
    // If not found, return error with 404 status
    $checkReplyStmt = $db->prepare("SELECT * FROM replies WHERE reply_id = :reply_id");
    $checkReplyStmt->bindParam(':reply_id', $replyId);
    $checkReplyStmt->execute();
    if ($checkReplyStmt->rowCount() === 0) {
        sendResponse(['error' => 'Reply not found'], 404);
    }

    
    // TODO: Prepare DELETE query
    $deleteReplyStmt = $db->prepare("DELETE FROM replies WHERE reply_id = :reply_id");
    $deleteReplyStmt->bindParam(':reply_id', $replyId);
    
    // TODO: Prepare, bind, and execute
    $deleteReplyStmt->execute();
    
    // TODO: Check if delete was successful
    if ($deleteReplyStmt->rowCount() > 0) {
        sendResponse(['message' => 'Reply deleted successfully']);
    } else {
        sendResponse(['error' => 'Failed to delete reply'], 500);
    }

    // If yes, return success response
    // If no, return error with 500 status
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Route the request based on resource and HTTP method
    if ($resource === 'topics') {
        switch ($method) {
            case 'GET':

    
    // TODO: For GET requests, check for 'id' parameter in $_GET
                if (isset($_GET['id'])) {
                    getTopicById($db, $_GET['id']);
                } else {
                    getAllTopics($db);
                }
                break;
            case 'POST':
                createTopic($db, $inputData);
                break;
            case 'PUT':
                updateTopic($db, $inputData);
                break;
            case 'DELETE':
                deleteTopic($db, isset($_GET['id']) ? $_GET['id'] : null);
                break;
            default:
    
    // TODO: For DELETE requests, get id from query parameter or request body
                sendResponse(['error' => 'Method not allowed'], 405);
                break;
        }
    } elseif ($resource === 'replies') {
        switch ($method) {
            case 'GET':
                if (isset($_GET['topic_id'])) {
                    getRepliesByTopicId($db, $_GET['topic_id']);
                } else {
                    sendResponse(['error' => 'topic_id parameter is required'], 400);
                }
                break;
            case 'POST':
                createReply($db, $inputData);
                break;
            case 'DELETE':
                deleteReply($db, isset($_GET['id']) ? $_GET['id'] : null);
                break;
            default:
                sendResponse(['error' => 'Method not allowed'], 405);
                break;
        }
    } else {
        sendResponse(['error' => 'Invalid resource'], 400);
    }
    // TODO: For unsupported methods, return 405 Method Not Allowed
    // TODO: For invalid resources, return 400 Bad Request
    
} catch (PDOException $e) {
    // TODO: Handle database errors
    // DO NOT expose the actual error message to the client (security risk)
    // Log the error for debugging (optional)
    // Return generic error response with 500 status
    sendResponse(['error' => 'Database error occurred'], 500);

    
} catch (Exception $e) {
    // TODO: Handle general errors
    // Log the error for debugging
    // Return error response with 500 status
    sendResponse(['error' => 'An unexpected error occurred'], 500);

}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response and exit
 * 
 * @param mixed $data - Data to send (will be JSON encoded)
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
    http_response_code($statusCode);
    
    // TODO: Echo JSON encoded data
    // Make sure to handle JSON encoding errors
    echo json_encode($data);
    
    // TODO: Exit to prevent further execution
    exit();
}


/**
 * Helper function to sanitize string input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Check if data is a string
    // If not, return as is or convert to string
    if (!is_string($data)) {
        return $data;
    }
    // TODO: Trim whitespace from both ends
    $data = trim($data);
    // TODO: Remove HTML and PHP tags
    $data = strip_tags($data);
    // TODO: Convert special characters to HTML entities (prevents XSS)
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    // TODO: Return sanitized data
    return $data;
}


/**
 * Helper function to validate resource name
 * 
 * @param string $resource - Resource name to validate
 * @return bool - True if valid, false otherwise
 */
function isValidResource($resource) {
    // TODO: Define allowed resources
    $allowedResources = ['topics', 'replies'];

    
    // TODO: Check if resource is in the allowed list
    return in_array($resource, $allowedResources);

}

?>

// TODO: Handle preflight OPTIONS request
// If the request method is OPTIONS, return 200 status and exit


// TODO: Include the database connection class
// Assume the Database class has a method getConnection() that returns a PDO instance


// TODO: Get the PDO database connection
// $db = $database->getConnection();


// TODO: Get the HTTP request method
// Use $_SERVER['REQUEST_METHOD']


// TODO: Get the request body for POST and PUT requests
// Use file_get_contents('php://input') to get raw POST data
// Decode JSON data using json_decode()


// TODO: Parse query parameters for filtering and searching


// ============================================================================
// TOPICS FUNCTIONS
// ============================================================================

/**
 * Function: Get all topics or search for specific topics
 * Method: GET
 * 
 * Query Parameters:
 *   - search: Optional search term to filter by subject, message, or author
 *   - sort: Optional field to sort by (subject, author, created_at)
 *   - order: Optional sort order (asc or desc, default: desc)
 */
function getAllTopics($db) {
    // TODO: Initialize base SQL query
    // Select topic_id, subject, message, author, and created_at (formatted as date)
    
    // TODO: Initialize an array to hold bound parameters
    
    // TODO: Check if search parameter exists in $_GET
    // If yes, add WHERE clause using LIKE for subject, message, OR author
    // Add the search term to the params array
    
    // TODO: Add ORDER BY clause
    // Check for sort and order parameters in $_GET
    // Validate the sort field (only allow: subject, author, created_at)
    // Validate order (only allow: asc, desc)
    // Default to ordering by created_at DESC
    
    // TODO: Prepare the SQL statement
    
    // TODO: Bind parameters if search was used
    // Loop through $params array and bind each parameter
    
    // TODO: Execute the query
    
    // TODO: Fetch all results as an associative array
    
    // TODO: Return JSON response with success status and data
    // Call sendResponse() helper function or echo json_encode directly
}


/**
 * Function: Get a single topic by topic_id
 * Method: GET
 * 
 * Query Parameters:
 *   - id: The topic's unique identifier
 */
function getTopicById($db, $topicId) {
    // TODO: Validate that topicId is provided
    // If empty, return error with 400 status
    
    // TODO: Prepare SQL query to select topic by topic_id
    // Select topic_id, subject, message, author, and created_at
    
    // TODO: Prepare and bind the topic_id parameter
    
    // TODO: Execute the query
    
    // TODO: Fetch the result
    
    // TODO: Check if topic exists
    // If topic found, return success response with topic data
    // If not found, return error with 404 status
}


/**
 * Function: Create a new topic
 * Method: POST
 * 
 * Required JSON Body:
 *   - topic_id: Unique identifier (e.g., "topic_1234567890")
 *   - subject: Topic subject/title
 *   - message: Main topic message
 *   - author: Author's name
 */
function createTopic($db, $data) {
    // TODO: Validate required fields
    // Check if topic_id, subject, message, and author are provided
    // If any required field is missing, return error with 400 status
    
    // TODO: Sanitize input data
    // Trim whitespace from all string fields
    // Use the sanitizeInput() helper function
    
    // TODO: Check if topic_id already exists
    // Prepare and execute a SELECT query to check for duplicate
    // If duplicate found, return error with 409 status (Conflict)
    
    // TODO: Prepare INSERT query
    // Insert topic_id, subject, message, and author
    // The created_at field should auto-populate with CURRENT_TIMESTAMP
    
    // TODO: Prepare the statement and bind parameters
    // Bind all the sanitized values
    
    // TODO: Execute the query
    
    // TODO: Check if insert was successful
    // If yes, return success response with 201 status (Created)
    // Include the topic_id in the response
    // If no, return error with 500 status
}


/**
 * Function: Update an existing topic
 * Method: PUT
 * 
 * Required JSON Body:
 *   - topic_id: The topic's unique identifier
 *   - subject: Updated subject (optional)
 *   - message: Updated message (optional)
 */
function updateTopic($db, $data) {
    // TODO: Validate that topic_id is provided
    // If not provided, return error with 400 status
    
    // TODO: Check if topic exists
    // Prepare and execute a SELECT query
    // If not found, return error with 404 status
    
    // TODO: Build UPDATE query dynamically based on provided fields
    // Only update fields that are provided in the request
    
    // TODO: Check if there are any fields to update
    // If $updates array is empty, return error
    
    // TODO: Complete the UPDATE query
    
    // TODO: Prepare statement and bind parameters
    // Bind all parameters from the $params array
    
    // TODO: Execute the query
    
    // TODO: Check if update was successful
    // If yes, return success response
    // If no rows affected, return appropriate message
    // If error, return error with 500 status
}


/**
 * Function: Delete a topic
 * Method: DELETE
 * 
 * Query Parameters:
 *   - id: The topic's unique identifier
 */
function deleteTopic($db, $topicId) {
    // TODO: Validate that topicId is provided
    // If not, return error with 400 status
    
    // TODO: Check if topic exists
    // Prepare and execute a SELECT query
    // If not found, return error with 404 status
    
    // TODO: Delete associated replies first (foreign key constraint)
    // Prepare DELETE query for replies table
    
    // TODO: Prepare DELETE query for the topic
    
    // TODO: Prepare, bind, and execute
    
    // TODO: Check if delete was successful
    // If yes, return success response
    // If no, return error with 500 status
}


// ============================================================================
// REPLIES FUNCTIONS
// ============================================================================

/**
 * Function: Get all replies for a specific topic
 * Method: GET
 * 
 * Query Parameters:
 *   - topic_id: The topic's unique identifier
 */
function getRepliesByTopicId($db, $topicId) {
    // TODO: Validate that topicId is provided
    // If not provided, return error with 400 status
    
    // TODO: Prepare SQL query to select all replies for the topic
    // Select reply_id, topic_id, text, author, and created_at (formatted as date)
    // Order by created_at ASC (oldest first)
    
    // TODO: Prepare and bind the topic_id parameter
    
    // TODO: Execute the query
    
    // TODO: Fetch all results as an associative array
    
    // TODO: Return JSON response
    // Even if no replies found, return empty array (not an error)
}


/**
 * Function: Create a new reply
 * Method: POST
 * 
 * Required JSON Body:
 *   - reply_id: Unique identifier (e.g., "reply_1234567890")
 *   - topic_id: The parent topic's identifier
 *   - text: Reply message text
 *   - author: Author's name
 */
function createReply($db, $data) {
    // TODO: Validate required fields
    // Check if reply_id, topic_id, text, and author are provided
    // If any field is missing, return error with 400 status
    
    // TODO: Sanitize input data
    // Trim whitespace from all fields
    
    // TODO: Verify that the parent topic exists
    // Prepare and execute SELECT query on topics table
    // If topic doesn't exist, return error with 404 status (can't reply to non-existent topic)
    
    // TODO: Check if reply_id already exists
    // Prepare and execute SELECT query to check for duplicate
    // If duplicate found, return error with 409 status
    
    // TODO: Prepare INSERT query
    // Insert reply_id, topic_id, text, and author
    
    // TODO: Prepare statement and bind parameters
    
    // TODO: Execute the query
    
    // TODO: Check if insert was successful
    // If yes, return success response with 201 status
    // Include the reply_id in the response
    // If no, return error with 500 status
}


/**
 * Function: Delete a reply
 * Method: DELETE
 * 
 * Query Parameters:
 *   - id: The reply's unique identifier
 */
function deleteReply($db, $replyId) {
    // TODO: Validate that replyId is provided
    // If not, return error with 400 status
    
    // TODO: Check if reply exists
    // Prepare and execute SELECT query
    // If not found, return error with 404 status
    
    // TODO: Prepare DELETE query
    
    // TODO: Prepare, bind, and execute
    
    // TODO: Check if delete was successful
    // If yes, return success response
    // If no, return error with 500 status
}


// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================

try {
    // TODO: Route the request based on resource and HTTP method
    
    // TODO: For GET requests, check for 'id' parameter in $_GET
    
    // TODO: For DELETE requests, get id from query parameter or request body
    
    // TODO: For unsupported methods, return 405 Method Not Allowed
    
    // TODO: For invalid resources, return 400 Bad Request
    
} catch (PDOException $e) {
    // TODO: Handle database errors
    // DO NOT expose the actual error message to the client (security risk)
    // Log the error for debugging (optional)
    // Return generic error response with 500 status
    
} catch (Exception $e) {
    // TODO: Handle general errors
    // Log the error for debugging
    // Return error response with 500 status
}


// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Helper function to send JSON response and exit
 * 
 * @param mixed $data - Data to send (will be JSON encoded)
 * @param int $statusCode - HTTP status code (default: 200)
 */
function sendResponse($data, $statusCode = 200) {
    // TODO: Set HTTP response code
    
    // TODO: Echo JSON encoded data
    // Make sure to handle JSON encoding errors
    
    // TODO: Exit to prevent further execution
}


/**
 * Helper function to sanitize string input
 * 
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitizeInput($data) {
    // TODO: Check if data is a string
    // If not, return as is or convert to string
    
    // TODO: Trim whitespace from both ends
    
    // TODO: Remove HTML and PHP tags
    
    // TODO: Convert special characters to HTML entities (prevents XSS)
    
    // TODO: Return sanitized data
}


/**
 * Helper function to validate resource name
 * 
 * @param string $resource - Resource name to validate
 * @return bool - True if valid, false otherwise
 */
function isValidResource($resource) {
    // TODO: Define allowed resources
    
    // TODO: Check if resource is in the allowed list
}

?>
