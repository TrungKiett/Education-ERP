<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Hệ thống quản lý giáo dục'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --app-bg: #f6f8fb;
            --app-gradient: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            --card-gradient: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(124, 58, 237, 0.08));
            --card-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
            --border-color: rgba(15, 23, 42, 0.08);
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--app-bg);
            min-height: 100vh;
            color: #0f172a;
        }
        .app-nav {
            background: var(--app-gradient);
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.25);
        }
        .navbar-brand {
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .user-pill {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.95rem;
        }
        .btn-signout {
            border-color: rgba(255, 255, 255, 0.65);
            color: white;
            transition: all 0.2s ease;
        }
        .btn-signout:hover {
            background: white;
            color: #2563eb;
        }
        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-radius: 1rem;
            background: white;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }
        .page-heading h2 {
            margin: 0;
            font-weight: 600;
        }
        .stat-card {
            border: none;
            border-radius: 1.5rem;
            background: white;
            box-shadow: var(--card-shadow);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--card-gradient);
            opacity: 0.6;
            z-index: 0;
        }
        .stat-card .card-body {
            position: relative;
            z-index: 1;
        }
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
        }
        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.25);
        }
        .bg-gradient-primary {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
        }
        .bg-gradient-success {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }
        .bg-gradient-info {
            background: linear-gradient(135deg, #0891b2, #0ea5e9);
        }
        .bg-gradient-warning {
            background: linear-gradient(135deg, #f97316, #fb923c);
        }
        .bg-gradient-danger {
            background: linear-gradient(135deg, #dc2626, #ef4444);
        }
        .glass-card {
            border-radius: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.88);
            box-shadow: var(--card-shadow);
        }
        .quick-actions .btn {
            border-radius: 0.85rem;
            padding: 0.85rem 1rem;
            font-weight: 500;
        }
        .nav-tabs.custom-tabs {
            border-bottom: none;
            gap: 0.5rem;
        }
        .nav-tabs.custom-tabs .nav-link {
            border: none;
            border-radius: 999px;
            background: white;
            box-shadow: var(--card-shadow);
            color: #475569;
            font-weight: 500;
            padding: 0.65rem 1.5rem;
            transition: all 0.2s ease;
        }
        .nav-tabs.custom-tabs .nav-link.active {
            background: var(--app-gradient);
            color: white;
        }
        .table-modern {
            border-radius: 1rem;
            overflow: hidden;
        }
        .table-modern thead {
            background: rgba(37, 99, 235, 0.08);
        }
        .table-modern tbody tr {
            transition: background 0.2s ease;
        }
        .table-modern tbody tr:hover {
            background: rgba(124, 58, 237, 0.05);
        }
        @media (max-width: 768px) {
            .page-heading {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark app-nav">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="bi bi-mortarboard"></i> Hệ thống quản lý giáo dục
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#appNavbar" aria-controls="appNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="appNavbar">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="?action=enrollment">
                            <i class="bi bi-file-earmark-text"></i> Đăng ký tuyển sinh
                        </a>
                    </li>
                </ul>
                <?php if (isLoggedIn()): ?>
                <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
                    <span class="user-pill text-white">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars(getCurrentUsername()); ?>
                        • <?php echo ucfirst(getCurrentRole()); ?>
                    </span>
                    <a class="btn btn-outline-light btn-sm btn-signout" href="?action=logout">
                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

