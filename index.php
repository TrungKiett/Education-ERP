<?php
// MVC Router - Entry point
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'models/Database.php';

// Autoload controllers
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/controllers/' . $class . '.php',
        __DIR__ . '/models/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Get action from URL
// Mặc định truy cập sẽ vào trang admin dashboard, không yêu cầu đăng nhập
$action = $_GET['action'] ?? 'admin.dashboard';

// Route to appropriate controller
$parts = explode('.', $action);
$controllerName = ucfirst($parts[0]) . 'Controller';
$method = $parts[1] ?? 'index';

// Default routing: nếu action là 'dashboard' hoặc rỗng thì luôn chuyển về admin dashboard
if ($action === 'dashboard' || $action === '') {
    $action = 'admin.dashboard';
}

// Handle special actions
if ($action === 'login') {
    $controller = new AuthController();
    $controller->login();
} elseif ($action === 'register') {
    $controller = new AuthController();
    $controller->register();
} elseif ($action === 'logout') {
    $controller = new AuthController();
    $controller->logout();
} elseif ($action === 'enrollment' || $action === 'enrollment.form') {
    // Public enrollment form
    $controller = new EnrollmentController();
    $controller->enrollmentForm();
} elseif ($action === 'student.invoices') {
    // Student invoices
    $controller = new StudentController();
    $controller->invoices();
} else {
    // Parse controller.action format
    $parts = explode('.', $action);
    $controllerName = ucfirst($parts[0]) . 'Controller';
    $method = $parts[1] ?? 'index';
    
    if (class_exists($controllerName)) {
        $controller = new $controllerName();
        if (method_exists($controller, $method)) {
            $controller->$method();
        } else {
            die("Method $method not found in $controllerName");
        }
    } else {
        die("Controller $controllerName not found");
    }
}
