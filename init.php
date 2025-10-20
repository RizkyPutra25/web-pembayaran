<?php
// init.php
$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'domain' => $_SERVER['HTTP_HOST'],
  'secure' => $secure,
  'httponly' => true,
  'samesite' => 'Lax'
]);
session_start();

// Security headers
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Permissions-Policy: geolocation=()");
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com;");
