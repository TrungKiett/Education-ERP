<?php
// Session configuration
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role']);
}

// Check if user has specific role
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

// Check if user is admin
function isAdmin() {
    return hasRole('admin');
}

// Check if user is teacher
function isTeacher() {
    return hasRole('teacher');
}

// Check if user is student
function isStudent() {
    return hasRole('student');
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /?action=login');
        exit();
    }
}

// Require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /');
        exit();
    }
}

// Require admin
function requireAdmin() {
    requireRole('admin');
}

// Require teacher
function requireTeacher() {
    requireRole('teacher');
}

// Require student
function requireStudent() {
    requireRole('student');
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current username
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

// Get current role
function getCurrentRole() {
    return $_SESSION['role'] ?? null;
}

