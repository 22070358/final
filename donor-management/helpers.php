<?php
/**
 * helpers.php - Helper functions
 * Các hàm hỗ trợ sử dụng chung trong ứng dụng
 */

/**
 * Redirect to a page
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Redirect with message
 */
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    redirect($url);
}

/**
 * Escape string for database
 */
function escape_string($string, $link) {
    return mysqli_real_escape_string($link, $string);
}

/**
 * Sanitize user input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Vietnam format)
 */
function is_valid_phone($phone) {
    return preg_match('/^(\+84|0)[0-9]{9,10}$/', $phone);
}

/**
 * Check if user is logged in
 */
function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Get current user ID
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 */
function get_current_username() {
    return $_SESSION['username'] ?? 'Guest';
}

/**
 * Format date to Vietnamese format
 */
function format_date($date, $format = 'd/m/Y') {
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

/**
 * Get time ago format
 */
function get_time_ago($date) {
    $datetime = new DateTime($date);
    $now = new DateTime();
    $interval = $now->diff($datetime);
    
    if ($interval->d > 30) {
        return $interval->m . ' tháng trước';
    } elseif ($interval->d > 0) {
        return $interval->d . ' ngày trước';
    } elseif ($interval->h > 0) {
        return $interval->h . ' giờ trước';
    } elseif ($interval->i > 0) {
        return $interval->i . ' phút trước';
    } else {
        return 'Vừa xong';
    }
}

/**
 * Generate random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    for ($i = 0; $i < $length; $i++) {
        $string .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $string;
}

/**
 * Hash password using bcrypt
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Log error to file
 */
function log_error($message) {
    $log_file = __DIR__ . '/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

/**
 * Get page title
 */
function get_page_title($page) {
    $titles = [
        'home' => 'Dashboard',
        'login' => 'Login & Registration',
        'edit' => 'Edit User',
        'profile' => 'User Profile',
    ];
    return $titles[$page] ?? 'Donor Management System';
}

/**
 * Paginate results
 */
function paginate($total_items, $items_per_page, $current_page = 1) {
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'offset' => $offset,
        'limit' => $items_per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'total_items' => $total_items,
    ];
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get badge color based on role
 */
function get_role_badge_color($role) {
    $colors = [
        'admin' => 'bg-red-100 text-red-800',
        'moderator' => 'bg-yellow-100 text-yellow-800',
        'user' => 'bg-blue-100 text-blue-800',
    ];
    return $colors[$role] ?? 'bg-gray-100 text-gray-800';
}

/**
 * Get badge color based on status
 */
function get_status_badge_color($status) {
    $colors = [
        'active' => 'badge-success',
        'inactive' => 'badge-danger',
        'pending' => 'badge-warning',
    ];
    return $colors[$status] ?? 'badge-gray';
}

/**
 * Format currency (Vietnamese Dong)
 */
function format_currency($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

/**
 * Send JSON response
 */
function send_json_response($data, $status_code = 200) {
    header('Content-Type: application/json');
    http_response_code($status_code);
    echo json_encode($data);
    exit();
}

/**
 * Generate secure token for remember me functionality
 */
function generate_secure_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash token for database storage
 */
function hash_token($token) {
    return hash('sha256', $token);
}

/**
 * Set remember me cookie
 */
function set_remember_me_cookie($token, $expire_time = null) {
    if ($expire_time === null) {
        $expire_time = time() + COOKIE_REMEMBER_EXPIRE;
    }

    setcookie(
        COOKIE_REMEMBER_ME,
        $token,
        [
            'expires' => $expire_time,
            'path' => SESSION_PATH,
            'domain' => SESSION_DOMAIN,
            'secure' => SESSION_SECURE,
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    );
}

/**
 * Clear remember me cookie
 */
function clear_remember_me_cookie() {
    setcookie(
        COOKIE_REMEMBER_ME,
        '',
        [
            'expires' => time() - 3600,
            'path' => SESSION_PATH,
            'domain' => SESSION_DOMAIN,
            'secure' => SESSION_SECURE,
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    );
}

/**
 * Set cookie consent cookie
 */
function set_cookie_consent($consent = true) {
    setcookie(
        COOKIE_CONSENT,
        $consent ? 'accepted' : 'declined',
        [
            'expires' => time() + COOKIE_CONSENT_EXPIRE,
            'path' => SESSION_PATH,
            'domain' => SESSION_DOMAIN,
            'secure' => SESSION_SECURE,
            'httponly' => false, // Allow JavaScript access for consent banner
            'samesite' => 'Strict'
        ]
    );
}

/**
 * Check if user has accepted cookies
 */
function has_cookie_consent() {
    return isset($_COOKIE[COOKIE_CONSENT]) && $_COOKIE[COOKIE_CONSENT] === 'accepted';
}

/**
 * Get remember me token from cookie
 */
function get_remember_me_token() {
    return $_COOKIE[COOKIE_REMEMBER_ME] ?? null;
}

/**
 * Validate and authenticate remember me token
 */
function authenticate_remember_me($link) {
    $token = get_remember_me_token();
    if (!$token) {
        return false;
    }

    $hashed_token = hash_token($token);

    // Check if token exists and is not expired
    $query = "SELECT us.*, u.username, u.email, up.role
              FROM user_sessions us
              JOIN users u ON us.user_id = u.id
              LEFT JOIN user_profiles up ON u.id = up.user_id
              WHERE us.remember_token = ? AND us.expires_at > NOW()";

    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 's', $hashed_token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $session_data = mysqli_fetch_assoc($result);

        // Set session data
        $_SESSION['user_id'] = $session_data['user_id'];
        $_SESSION['username'] = $session_data['username'];
        $_SESSION['email'] = $session_data['email'];
        $_SESSION['role'] = $session_data['role'] ?? 'donor';

        // Generate new session token for security
        $new_session_token = generate_secure_token();
        $new_hashed_session_token = hash_token($new_session_token);

        // Update session token in database
        $update_query = "UPDATE user_sessions SET session_token = ?, created_at = NOW() WHERE id = ?";
        $update_stmt = mysqli_prepare($link, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'si', $new_hashed_session_token, $session_data['id']);
        mysqli_stmt_execute($update_stmt);

        return true;
    }

    return false;
}

/**
 * Create remember me session in database
 */
function create_remember_me_session($link, $user_id, $remember_token, $ip_address, $user_agent) {
    $hashed_remember_token = hash_token($remember_token);
    $session_token = generate_secure_token();
    $hashed_session_token = hash_token($session_token);
    $expires_at = date('Y-m-d H:i:s', time() + COOKIE_REMEMBER_EXPIRE);

    $query = "INSERT INTO user_sessions (user_id, session_token, remember_token, ip_address, user_agent, expires_at)
              VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'isssss', $user_id, $hashed_session_token, $hashed_remember_token, $ip_address, $user_agent, $expires_at);
    $result = mysqli_stmt_execute($stmt);

    return $result ? $session_token : false;
}

/**
 * Clear expired sessions
 */
function clear_expired_sessions($link) {
    $query = "DELETE FROM user_sessions WHERE expires_at < NOW()";
    mysqli_query($link, $query);
}

/**
 * Logout user and clear all sessions
 */
function logout_user($link) {
    if (isset($_SESSION['user_id'])) {
        // Clear remember me token from database
        $query = "DELETE FROM user_sessions WHERE user_id = ?";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
    }

    // Clear remember me cookie
    clear_remember_me_cookie();

    // Destroy session
    session_destroy();

    // Start new session to prevent session fixation
    session_start();
    session_regenerate_id(true);
}

/**
 * Get HTTP status text
 */
function get_http_status_text($code) {
    $statuses = [
        200 => 'OK',
        201 => 'Created',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
    ];
    return $statuses[$code] ?? 'Unknown Status';
}

?>
