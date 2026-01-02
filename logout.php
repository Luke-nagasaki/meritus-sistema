<?php
session_start();
require_once 'config/auth.php';

logout();
header('Location: index.php');
exit;
?>
