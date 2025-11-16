<?php
require_once __DIR__ . '/../config/session.php';

abstract class BaseController {
    protected function render($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . "/../views/{$view}.php";
        if (!file_exists($viewPath)) {
            die("View not found: $viewPath");
        }
        require_once $viewPath;
    }
    
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    protected function requireLogin() {
        if (!isLoggedIn()) {
            $this->redirect('?action=login');
        }
    }
    
    protected function requireRole($role) {
        $this->requireLogin();
        if (!hasRole($role)) {
            $this->redirect('/');
        }
    }
    
    protected function requireAdmin() {
        $this->requireRole('admin');
    }
    
    protected function requireTeacher() {
        $this->requireRole('teacher');
    }
    
    protected function requireStudent() {
        $this->requireRole('student');
    }
}

