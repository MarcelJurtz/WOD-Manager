<?php
session_start();

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once('db.php');

    // Check database connectivity first
    if (!checkDatabaseConnection()) {
        header('Location: ./../index.php?error=db_connection');
        exit;
    }

    $con = getConnectionSafe();
    if (!$con) {
        header('Location: ./../index.php?error=db_connection');
        exit;
    }

    if (!isset($_POST['username'], $_POST['password'])) {
        logLogin($con, 0);
        header('Location: ./../index.php?error=missing_fields');
        exit;
    }

    // Validate input
    if (empty(trim($_POST['username'])) || empty(trim($_POST['password']))) {
        logLogin($con, 0);
        header('Location: ./../index.php?error=missing_fields');
        exit;
    }

    $stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ?');
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $con->error);
    }

    $stmt->bind_param('s', $_POST['username']);
    
    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('Database execute failed: ' . $stmt->error);
    }
    
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password);
        $stmt->fetch();
        
        if (password_verify($_POST['password'], $password)) {
            logLogin($con, 1);
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $_POST['username'];
            $_SESSION['id'] = $id;
            $stmt->close();
            $con->close();
            header('Location: ./../index.php');
            exit;
        } else {
            logLogin($con, 0);
            $stmt->close();
            $con->close();
            header('Location: ./../index.php?error=invalid_credentials');
            exit;
        }
    } else {
        logLogin($con, 0);
        $stmt->close();
        $con->close();
        header('Location: ./../index.php?error=invalid_credentials');
        exit;
    }

} catch (Exception $e) {
    // Log the error for debugging
    error_log('Authentication error: ' . $e->getMessage());
    
    // Clean up resources if they exist
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    if (isset($con) && $con) {
        $con->close();
    }
    
    // Redirect with generic error
    header('Location: ./../index.php?error=server_error');
    exit;
}

function logLogin($con, $success) {
    try {
        if ($con && isset($_POST["username"])) {
            $stmt = $con->prepare('INSERT INTO login_log (username, success, ip) VALUES (?,?,?)');
            if ($stmt) {
                $remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
                $stmt->bind_param('sis', $_POST["username"], $success, $remote_addr);
                $stmt->execute();
                $stmt->close();
            }
        }
    } catch (Exception $e) {
        // Log error but don't stop execution
        error_log('Login logging error: ' . $e->getMessage());
    }
}
?>