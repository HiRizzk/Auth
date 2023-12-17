<?php
function clean($input_data, $input_type = 'text') {
    switch ($input_type) {
        case 'text':
            $sanitized_data = htmlspecialchars(strip_tags($input_data), ENT_QUOTES, 'UTF-8');
            break;
        case 'email':
            $sanitized_data = filter_var($input_data, FILTER_SANITIZE_EMAIL);
            if (!filter_var($sanitized_data, FILTER_VALIDATE_EMAIL)) {
                return false; // Invalid email
            }
            break;
        case 'numeric':
            $sanitized_data = preg_replace("/[^0-9]/", "", $input_data);
            break;
        case 'url':
            $sanitized_data = filter_var($input_data, FILTER_SANITIZE_URL);
            if (!filter_var($sanitized_data, FILTER_VALIDATE_URL)) {
                return false; // Invalid URL
            }
            break;
        default:
            $sanitized_data = $input_data;
            break;
    }

    return $sanitized_data;
}

function getUserData($uid, $conn) {
    $query = "SELECT * FROM users WHERE id = ?";
    $statement = $conn->prepare($query);

    if ($statement) {
        $statement->bind_param("i", $uid);
        $statement->execute();
        $result = $statement->get_result();
        $statement->close();

        return ($result->num_rows == 1) ? $result->fetch_assoc() : null;
    }

    return null;
}
function hostaddr() {
    $hostaddr = $_SERVER['REMOTE_ADDR'];

    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $hostaddr = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }

    $_SERVER['REMOTE_ADDR'] = $hostaddr;

    return $hostaddr;
}
function error_push($severity, $message, $file, $line) {
    $errorLevels = [
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
    ];

    $errorMessage = '[' . date('Y-m-d H:i:s') . '] ' . $errorLevels[$severity] . ': ' . $message . ' in ' . $file . ' on line ' . $line;

    error_log($errorMessage, 3, 'error_log.txt');

    echo 'An error occurred. Please try again later.';
    return true;
}

set_error_handler('error_push');
?>
