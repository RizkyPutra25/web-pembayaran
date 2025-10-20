<?php
// auth_check.php
require_once __DIR__.'/init.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
