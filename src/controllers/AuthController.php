<?php
require_once __DIR__ . '/../models/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

// Function to generate a random token
function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

// Function to set a session and token
function setSessionToken($id_user, $username, $role)
{
    global $pdo;

    // Delete expired tokens for the user
    $sql_delete_expired_tokens = "DELETE FROM tokens WHERE id_user = ? AND expires_at < NOW()";
    $stmt_delete_expired_tokens = $pdo->prepare($sql_delete_expired_tokens);
    $stmt_delete_expired_tokens->execute([$id_user]);

    // Query to check if user has an active token
    $sql_check_token = "SELECT id_user, token, expires_at FROM tokens WHERE id_user = ?";
    $stmt_check_token = $pdo->prepare($sql_check_token);
    $stmt_check_token->execute([$id_user]);
    $existing_token = $stmt_check_token->fetch(PDO::FETCH_ASSOC);

    if ($existing_token) {
        // Check if token has expired according to Jakarta time
        $expires_at_utc = new DateTime($existing_token['expires_at'], new DateTimeZone('UTC'));
        $expires_at_utc->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $expires_at_jakarta = $expires_at_utc->format('Y-m-d H:i:s');

        if ($expires_at_jakarta < date('Y-m-d H:i:s')) {
            // If token is expired in Jakarta time, delete it
            $sql_delete_token = "DELETE FROM tokens WHERE id_user = ? AND token = ?";
            $stmt_delete_token = $pdo->prepare($sql_delete_token);
            $stmt_delete_token->execute([$id_user, $existing_token['token']]);

            // Generate new token
            $token = generateToken();
            $expires_at = date('Y-m-d H:i:s', strtotime('+12 hours'));

            // Insert new token into database
            $sql_insert_token = "INSERT INTO tokens (id_user, token, expires_at) VALUES (?, ?, ?)";
            $stmt_insert_token = $pdo->prepare($sql_insert_token);
            $stmt_insert_token->execute([$id_user, $token, $expires_at]);
        } else {
            // Use existing token
            $token = $existing_token['token'];
        }
    } else {
        // Generate new token
        $token = generateToken();
        $expires_at = date('Y-m-d H:i:s', strtotime('+12 hours'));

        // Insert new token into database
        $sql_insert_token = "INSERT INTO tokens (id_user, token, expires_at) VALUES (?, ?, ?)";
        $stmt_insert_token = $pdo->prepare($sql_insert_token);
        $stmt_insert_token->execute([$id_user, $token, $expires_at]);
    }

    // Set session variables
    $_SESSION['id_user'] = $id_user;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
    $_SESSION['token'] = $token;

    return $token;
}

// Function to verify password
function verifyPassword($inputPassword, $hashedPassword)
{
    return password_verify($inputPassword, $hashedPassword);
}

// Function to log the login details
function logLogin($id_user)
{
    global $pdo;

    // Get user IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Insert login details into logs table
    $sql_insert_log = "INSERT INTO logs (id_user, ip_user_login) VALUES (?, ?)";
    $stmt_insert_log = $pdo->prepare($sql_insert_log);
    $stmt_insert_log->execute([$id_user, $ip_address]);
}

// Check if form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // If not accessed via POST, redirect to login page
    header('Location: /');
    exit();
}

// Retrieve and sanitize username and password from form
$username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
$password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');

try {
    $pdo = getPDOInstance();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Query database to fetch user details including hashed password
$sql = "SELECT Id_user, Username, Password, Role FROM Users WHERE Username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    if (verifyPassword($password, $user['Password'])) {
        logLogin($user['Id_user']);
        $token = setSessionToken($user['Id_user'], $user['Username'], $user['Role']);
        header('Location: dashboard?key=' . $token);
        exit();
    } else {
        header('Location: /aduan2/?error=invalid_password');
        exit();
    }
} else {
    header('Location: /aduan2/?error=user_not_found');
    exit();
}
