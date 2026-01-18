<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/VisaGenerator.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getDatabaseConnection();
initializeDatabase($pdo);

$generator = new VisaGenerator($pdo);
$generator->ensureScheduleIsFresh();
