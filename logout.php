<?php
require_once 'auth/user_authentication.php';

$auth = new UserAuthentication($mysqli);
$auth->logoutUser();

header("Location: login.php");
exit;
?>
