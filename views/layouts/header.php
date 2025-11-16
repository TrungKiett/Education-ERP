<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Hệ thống quản lý giáo dục'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
        }
        .nav-link {
            color: #495057;
        }
        .nav-link:hover {
            background-color: #e9ecef;
            color: #0d6efd;
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-mortarboard"></i> Hệ thống quản lý giáo dục
            </a>
            <?php if (isLoggedIn()): ?>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text text-white me-3">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(getCurrentUsername()); ?> 
                    (<?php echo ucfirst(getCurrentRole()); ?>)
                </span>
                <a class="btn btn-outline-light btn-sm" href="index.php?action=logout">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
            </div>
            <?php endif; ?>
        </div>
    </nav>

