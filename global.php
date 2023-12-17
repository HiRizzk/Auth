<?php
session_start();

$timenow = time();
$hostaddr = $_SERVER['REMOTE_ADDR'];

require_once("config.php");
require_once("functions.php");

// Get the real IP address when behind Cloudflare
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $hostaddr = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

// Update $_SERVER['REMOTE_ADDR'] to the real IP address
$_SERVER['REMOTE_ADDR'] = $hostaddr;

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>