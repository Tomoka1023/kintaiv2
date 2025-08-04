<?php
session_start();
date_default_timezone_set('Asia/Tokyo');
session_destroy();
header("Location: login.php");
exit;
?>