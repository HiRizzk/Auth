<?php
$token = $_SESSION['token']

if(isset($token) && strlen($token) == 32)
{
    $query = "SELECT * FROM users JOIN csession ON users.csession  = csession.session_id";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
    $udata = $result->fetch_array(); 
    $loggedin = true;
    }
} 
else 
{
$loggedin = false;
} 
?>