<?php
// Start session for user authentication and data persistence
session_start();

// Define the base URL for the application
define('BASE_URL', '/');

// Include utility functions
require_once 'includes/functions.php';

// Simple routing system
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Header
include 'includes/header.php';

// Main content
switch ($page) {
    case 'home':
        include 'pages/home.php';
        break;
    case 'menus':
        include 'pages/menus.php';
        break;
    case 'staff':
        include 'pages/staff.php';
        break;
    case 'orders':
        include 'pages/orders.php';
        break;
    case 'new-order':
        include 'pages/new-order.php';
        break;
    case 'sales':
        include 'pages/sales.php';
        break;
    default:
        include 'pages/home.php';
        break;
}

// Footer
include 'includes/footer.php';
?>

