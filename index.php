<?php
require_once("global.php");

$fuseaction = filter_input(INPUT_REQUEST, 'fuseaction', FILTER_SANITIZE_STRING);
$protected = array('dashboard', 'profile');
$nobody = array('404-error','maintenance', '500-error');

if(in_array($protected, $fuseaction))
{
require_once("session.php");
}

//} elseif (in_array($fuseaction, $protected)) {
    // Redirect to the login page if authentication is required
  //  header("Location: login.php");
   // exit(); // Ensure that no further code is executed after the redirection
// }

$template = "./blank.php"; // Default template
switch ($fuseaction) {
    case "dashboard":
        $template = "./dashboard.php";
        break;
    case "profile":
        $template = "./profile.php";
        break;
    case "users":
        $template = "./users.php";
        break;
    case "settings":
        $template = "./settings.php";
        break;
}




require_once("./template/header.php");
require_once($template);
require_once("./template/footer.php");
?>
