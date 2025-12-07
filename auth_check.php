<?php
/**
 * Centralized Authentication Check Module
 * 
 * This file should be included at the top of all protected pages
 * to ensure consistent authentication validation and redirect behavior.
 * 
 * Usage: require_once 'auth_check.php';
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Store the requested page for potential redirect after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to login page
    header('Location: login.html');
    exit;
}

// Optional: Check for session timeout (e.g., 30 minutes of inactivity)
$timeout_duration = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // Session has expired
    session_unset();
    session_destroy();
    header('Location: login.html?timeout=1');
    exit;
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
