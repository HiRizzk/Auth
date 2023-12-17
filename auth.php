<?php
session_start();
$time = time();

require_once("functions.php");

$email = clean($_POST['email']);
$username = clean($_POST['username']);
$password = md5($_POST['password']);
$verifiedEmail = clean($_GET['code']);

$hcaptcha_secret_key = "YOUR_HCAPTCHA_SECRET_KEY";
$hcaptcha_response = $_POST['h-captcha-response'];

if(isset($logout) === true)
{
    $_SESSION = array();
    setcookie("token", "", 0);
    session_destroy();
    redirect("./login");
}

if (isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['h-captcha-response'])) {
    $hcaptcha_verify_url = "https://hcaptcha.com/siteverify";
    $hcaptcha_data = array(
        'secret' => $hcaptcha_secret_key,
        'response' => $hcaptcha_response
    );

    $hcaptcha_options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($hcaptcha_data)
        )
    );

    $hcaptcha_context = stream_context_create($hcaptcha_options);
    $hcaptcha_result = file_get_contents($hcaptcha_verify_url, false, $hcaptcha_context);
    $hcaptcha_response_data = json_decode($hcaptcha_result);

    if ($hcaptcha_response_data->success) {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

        if ($action === 'login') {
            // Retrieve user's password hash from the database
            $sql = "SELECT * FROM users WHERE email = '{$email}'";
            $result = query($sql);

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $uid = $row['id'];

                if (password_verify($password, $row['password'])) {
                    $uuid = uuid();
                    $lastlogin = base64_encode($uid . "|" . $realIP . "|" . $uuid);
                    $token = md5($lastlogin)."|".$uuid;
                    
                    
                    $_SESSION['token'] = $token;
                    setcookie("token", $token, time() + 84600);
                    
                    query("UPDATE users SET lastlogin = '{$lastlogin}', csession = '{$token}' WHERE email = '{$email}'");
                    redirect("./dashboard&login=success");
                } else {
                    $error = "Bad Password!";
                    redirect("./login?err={$error}");
                }
            } else {
                $error = "Invalid Username!";
                redirect("./login?err={$error}");
            }
        } elseif ($action === 'register') {
            $checkSql = "SELECT * FROM users WHERE email = '{$email}'";
            $result = query($checkSql);

            if (mysqli_num_rows($result) > 0) {
                $error = "Email is already taken.";
                redirect("./register?err={$error}");
            } else {
                // Email is available, so insert the user into the database
                $insertSql = "INSERT INTO users (email, password, username, joindate, ip_address, ref_id) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertSql);
                $stmt->bind_param("ssssss", $email, $password, $username, $joindate, $ip_address, $ref_id);

                if ($stmt->execute()) {
                    // Registration successful
                    // Redirect to the login page or send a confirmation email
                    $error = "0";
                    redirect("./login?success=1");
                } else {
                    // Registration failed
                    // Display an error message
                    $error = "Form Submission Failed!";
                    redirect("./register?err={$error}");
                }
            }
        } elseif ($action === 'reset-password' && $_SERVER['REQUEST_METHOD'] == 'POST') {
            // Handle forgot password form submission
            $email = $_POST['email'];

            // Check if the email exists in the database
            $sql = "SELECT * FROM users WHERE email = '$email'";
            $result = query($sql);

            if ($result->num_rows == 1) {
                // Generate a password reset token, send an email, and update the database
                // Redirect to a confirmation page

                $passtoken = md5(base64_encode($email . time()));

                $subject = "Your Token Link";
                $message = "Click the following link to use your token: " . $domain . "/reset?token=" . $passtoken;
                $headers = "From: help@" . $domain;

                // Send the email
                $mailSent = mail($email, $subject, $message, $headers);

                // Check if the email was sent successfully
                if ($mailSent) {
                    $success = "Email sent successfully. Check your inbox for the link.";
                    // redirect("./recover?success=1);
                } else {
                    $error = "Error sending email. Please try again.";
                    redirect("./recover?err=1");
                }

                query("UPDATE users SET passtoken = '{$passtoken}', pt_exp = 'time()+900' WHERE email = '{$email}'");
                redirect("./recover?step=2");
            } else {
                // Email not found
                // Display an error message
                $error = "Invalid Email Address!";
                redirect("./recover?step=1&error=1&err=" . base64_encode($error));
            }
        } elseif ($action === 'verify-email' && isset($_GET['code'])) {
            $verificationCode = clean($_GET['code']);
            $sql = "SELECT * FROM users WHERE email = '$email' AND verification_code = '$verificationCode'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $verifiedEmail = $row['email'];

                // Update the email_verified status
                $updateSql = "UPDATE users SET email_verified = 1 WHERE email = '$verifiedEmail'";

                if ($conn->query($updateSql) === TRUE) {
                    $success = "Email verified successfully.";
                } else {
                    $error = "Error updating record: " . $conn->error;
                    redirect("./verify&err=" . $error);
                }
            } else {
                $error = "Error updating record: " . $conn->error;
                redirect("./verify&err=" . $error);
            }
        } else {
            $error = "Invalid action specified.";
            redirect("./auth?err=" . $error);
        }
    } else {
        $error = "Invalid captcha response.";
        redirect("./auth?err=" . $error);
    }
} else {
    $error = "Required parameters missing.";
    redirect("./auth?err=" . $error);
}
?>