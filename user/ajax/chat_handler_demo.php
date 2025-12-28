<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

function jsonResponse($success, $data = [], $message = '') {
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

$action = $_REQUEST['action'] ?? '';
$projectId = $_REQUEST['project_id'] ?? 0;

if (!$projectId && $action != 'get_conversations') {
    jsonResponse(false, [], 'Missing Project ID');
}

// 0. Ensure Tables Exist
$conn->query("CREATE TABLE IF NOT EXISTS `project_status_demo` (
  `project_id` int(11) NOT NULL,
  `quote_accepted` tinyint(1) DEFAULT 0,
  `user_confirmed` tinyint(1) DEFAULT 0,
  `freelancer_confirmed` tinyint(1) DEFAULT 0,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
$conn->query("INSERT IGNORE INTO project_status_demo (project_id) VALUES ($projectId)");


// 1. Get Messages & Status
if ($action === 'get_messages') {
    $currentUserId = isset($_REQUEST['current_user_id']) ? intval($_REQUEST['current_user_id']) : 0;
    
    $sql = "SELECT m.*, u.full_name 
            FROM messages_demo m 
            LEFT JOIN users u ON m.sender_id = u.user_id 
            WHERE m.project_id = ? 
            ORDER BY m.created_at ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    
    foreach ($messages as &$msg) {
        $msg['is_self'] = (intval($msg['sender_id']) === $currentUserId);
        if ($msg['message_type'] == 'quote') {
            $msg['quote_data'] = json_decode($msg['message_content'], true);
        }
    }
    
    $statusRes = $conn->query("SELECT * FROM project_status_demo WHERE project_id = $projectId");
    $status = $statusRes->fetch_assoc();
    
    jsonResponse(true, ['messages' => $messages, 'status' => $status]);
}

// 2. Send Message
if ($action === 'send_message') {
    $senderId = $_POST['sender_id'];
    $receiverId = $_POST['receiver_id'];
    $content = $_POST['content'];
    $type = $_POST['type'] ?? 'text';
    
    $sql = "INSERT INTO messages_demo (project_id, sender_id, receiver_id, message_type, message_content) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $projectId, $senderId, $receiverId, $type, $content);
    
    if ($stmt->execute()) {
        jsonResponse(true, ['id' => $stmt->insert_id]);
    } else {
        jsonResponse(false, [], $stmt->error);
    }
}

// 3. Upload File (Robust Fallback)
if ($action === 'upload_file') {
    $senderId = $_POST['sender_id'];
    $receiverId = $_POST['receiver_id'];
    
    if (!isset($_FILES['file']) || $_FILES['file']['error'] != UPLOAD_ERR_OK) {
        jsonResponse(false, [], 'No file uploaded or upload error: ' . ($_FILES['file']['error'] ?? 'Unknown'));
    }
    
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmp = $file['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $type = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : 'file';
    $publicUrl = '';
    
    // Attempt File System Save
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $uploadDir = $docRoot . '/WEB_MXH/user/assets/uploads/chat/';
    
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Try to chmod if exists
    if (file_exists($uploadDir)) {
        @chmod($uploadDir, 0777);
    }
    
    if (is_writable($uploadDir)) {
        $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
        $targetPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($fileTmp, $targetPath)) {
            $publicUrl = '/WEB_MXH/user/assets/uploads/chat/' . $newFileName;
        }
    }
    
    // Fallback: Use Base64 if file save failed
    if (empty($publicUrl)) {
        $fileContent = file_get_contents($fileTmp);
        $base64 = base64_encode($fileContent);
        // Add mime type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($fileTmp);
        $publicUrl = 'data:' . $mime . ';base64,' . $base64;
    }
    
    // Insert into DB
    // Note: Base64 strings can be large, so text/mediumtext column is needed. 
    // messages_demo.message_content is 'text' (64KB). 
    // If base64 > 64KB, this will fail or truncate.
    // Ideally we alter table to LONGTEXT for this demo fallback.
    
    // Quick Alter to ensure it fits
    $conn->query("ALTER TABLE messages_demo MODIFY message_content LONGTEXT");
    
    $sql = "INSERT INTO messages_demo (project_id, sender_id, receiver_id, message_type, message_content, file_name, file_size) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissss", $projectId, $senderId, $receiverId, $type, $publicUrl, $fileName, $fileSize);
    
    if ($stmt->execute()) {
        jsonResponse(true, ['url' => $publicUrl, 'type' => $type, 'fallback' => (strpos($publicUrl, 'data:') === 0)]);
    } else {
        jsonResponse(false, [], 'DB Insert Error: ' . $stmt->error);
    }
}

// 4. Update Status
if ($action === 'update_status') {
    $field = $_POST['field']; // quote_accepted, user_confirmed, freelancer_confirmed, freelancer_work_done
    
    if (in_array($field, ['quote_accepted', 'user_confirmed', 'freelancer_confirmed', 'freelancer_work_done'])) {
        $stmt = $conn->prepare("UPDATE project_status_demo SET $field = 1 WHERE project_id = ?");
        $stmt->bind_param("i", $projectId);
        if ($stmt->execute()) {
            jsonResponse(true);
        }
    }
    jsonResponse(false, [], 'Invalid field');
}

// 4.5 Reset Status
if ($action === 'reset_status') {
    $stmt = $conn->prepare("UPDATE project_status_demo SET quote_accepted = 0, freelancer_work_done = 0, user_confirmed = 0, freelancer_confirmed = 0 WHERE project_id = ?");
    $stmt->bind_param("i", $projectId);
    if ($stmt->execute()) {
        jsonResponse(true);
    } else {
        jsonResponse(false, [], $stmt->error);
    }
}

// 5. Get Documents
if ($action === 'get_documents') {
    $sql = "SELECT * FROM messages_demo WHERE project_id = ? AND message_type = 'file' ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    jsonResponse(true, $result->fetch_all(MYSQLI_ASSOC));
}

// 6. Get Conversations
if ($action === 'get_conversations') {
    $userId = $_REQUEST['user_id'] ?? 1;
    $sql = "SELECT m.*, 
            (SELECT full_name FROM users WHERE user_id = IF(m.sender_id = ?, m.receiver_id, m.sender_id) LIMIT 1) as other_name,
            (SELECT COUNT(*) FROM messages_demo WHERE project_id = m.project_id AND is_read = 0 AND receiver_id = ?) as unread
            FROM messages_demo m
            WHERE m.id IN (
                SELECT MAX(id) FROM messages_demo 
                WHERE sender_id = ? OR receiver_id = ?
                GROUP BY project_id
            )
            ORDER BY m.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $userId, $userId, $userId, $userId);
    $stmt->execute();
    jsonResponse(true, $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
}
?>
