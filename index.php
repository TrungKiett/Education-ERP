<?php
require_once 'config/session.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$role = getCurrentRole();

if ($role === 'admin') {
    header('Location: admin/index.php');
} elseif ($role === 'teacher') {
    header('Location: teacher/index.php');
} elseif ($role === 'student') {
    header('Location: student/index.php');
} else {
    header('Location: login.php');
}
exit();
?>

