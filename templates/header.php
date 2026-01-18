<?php
/** @var PDO $pdo */
/** @var array|null $user */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - ' : '' ?>VisaNusa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --canvas: #fdf7f1;
            --surface: #fffaf5;
            --surface-2: #fff1e2;
            --ink: #1d1a17;
            --muted: #6e6258;
            --accent: #e76f51;
            --accent-strong: #d65b3f;
            --teal: #2a9d8f;
            --teal-strong: #208073;
            --gold: #f4a261;
            --stroke: #ead9c8;
            --shadow: 0 18px 40px rgba(39, 24, 8, 0.12);
            --radius-lg: 20px;
            --radius-md: 14px;
        }

        body.theme-body {
            font-family: "Space Grotesk", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 10% 15%, rgba(244, 162, 97, 0.18), transparent 45%),
                radial-gradient(circle at 85% 10%, rgba(42, 157, 143, 0.18), transparent 40%),
                linear-gradient(180deg, #fff7ef 0%, #fdf7f1 55%, #faf3eb 100%);
            color: var(--ink);
        }

        h1, h2, h3, h4, .display {
            font-family: "Fraunces", "Georgia", serif;
            letter-spacing: -0.01em;
        }

        nav, main {
            position: relative;
            z-index: 1;
        }

        .page-bg {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(231, 111, 81, 0.15), transparent 50%),
                radial-gradient(circle at 70% 75%, rgba(244, 162, 97, 0.18), transparent 45%);
        }

        .site-nav {
            background: rgba(255, 250, 245, 0.82);
            border-bottom: 1px solid var(--stroke);
            backdrop-filter: blur(14px);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--ink);
        }

        .brand-dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: conic-gradient(from 180deg, var(--accent), var(--gold), var(--teal));
            box-shadow: 0 0 0 3px rgba(231, 111, 81, 0.15);
        }

        .nav-link {
            color: var(--muted);
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .nav-link:hover {
            color: var(--ink);
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(42, 157, 143, 0.12);
            color: var(--teal-strong);
            font-weight: 600;
            border: 1px solid rgba(42, 157, 143, 0.18);
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(231, 111, 81, 0.12);
            color: var(--accent-strong);
            font-weight: 600;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--stroke);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .card-hero {
            background: linear-gradient(140deg, #fff6ea 0%, #fffaf5 60%, #f9efe3 100%);
        }

        .btn-primary,
        .btn-secondary,
        .btn-ghost {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            border-radius: 999px;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .btn-primary {
            background: var(--teal);
            color: #fff;
            box-shadow: 0 10px 18px rgba(42, 157, 143, 0.28);
        }

        .btn-primary:hover {
            background: var(--teal-strong);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 10px 18px rgba(231, 111, 81, 0.25);
        }

        .btn-secondary:hover {
            background: var(--accent-strong);
            transform: translateY(-1px);
        }

        .btn-ghost {
            background: transparent;
            color: var(--ink);
            border: 1px dashed var(--stroke);
        }

        .eyebrow {
            text-transform: uppercase;
            letter-spacing: 0.2em;
            font-size: 0.7rem;
            color: var(--muted);
            font-weight: 600;
        }

        .muted {
            color: var(--muted);
        }

        .stat {
            padding: 14px;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(234, 217, 200, 0.8);
        }

        .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
            font-weight: 600;
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            margin-top: 6px;
        }

        .stat-note {
            font-size: 0.75rem;
            color: var(--muted);
            margin-top: 2px;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(42, 157, 143, 0.12);
            color: var(--teal-strong);
            border: 1px solid rgba(42, 157, 143, 0.25);
        }

        .alert-error {
            background: rgba(231, 111, 81, 0.12);
            color: var(--accent-strong);
            border: 1px solid rgba(231, 111, 81, 0.25);
        }

        .visa-item {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 18px;
            border-radius: var(--radius-md);
            background: #fff;
            border: 1px solid rgba(234, 217, 200, 0.8);
            box-shadow: 0 8px 20px rgba(39, 24, 8, 0.08);
        }

        @media (min-width: 768px) {
            .visa-item {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .route {
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 6px;
        }

        .route-arrow {
            color: var(--muted);
            margin: 0 6px;
        }

        .meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 6px;
        }

        @media (min-width: 640px) {
            .meta {
                flex-direction: row;
                gap: 14px;
            }
        }

        .price {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--accent-strong);
            margin-top: 8px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .badge-warm {
            background: rgba(244, 162, 97, 0.18);
            color: #b96d2c;
            border-color: rgba(244, 162, 97, 0.3);
        }

        .badge-success {
            background: rgba(42, 157, 143, 0.18);
            color: var(--teal-strong);
            border-color: rgba(42, 157, 143, 0.3);
        }

        .badge-danger {
            background: rgba(231, 111, 81, 0.2);
            color: var(--accent-strong);
            border-color: rgba(231, 111, 81, 0.32);
        }

        .badge-neutral {
            background: rgba(110, 98, 88, 0.1);
            color: var(--muted);
            border-color: rgba(110, 98, 88, 0.2);
        }

        .empty-state {
            display: grid;
            gap: 12px;
            padding: 20px;
            border-radius: var(--radius-md);
            background: var(--surface-2);
            border: 1px dashed rgba(231, 111, 81, 0.3);
        }

        .empty-state i {
            font-size: 1.6rem;
            color: var(--accent-strong);
        }

        .reveal {
            opacity: 0;
            transform: translateY(12px);
            animation: rise 0.6s ease forwards;
            animation-delay: var(--delay, 0s);
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal {
                animation: none;
                opacity: 1;
                transform: none;
            }
        }

        .text-indigo-600,
        .text-indigo-700 {
            color: var(--teal-strong) !important;
        }

        .text-indigo-800,
        .hover\:text-indigo-600:hover,
        .hover\:text-indigo-800:hover {
            color: var(--teal-strong) !important;
        }

        .bg-indigo-600 {
            background-color: var(--teal) !important;
        }

        .bg-indigo-700,
        .hover\:bg-indigo-700:hover {
            background-color: var(--teal-strong) !important;
        }

        .bg-indigo-100 {
            background-color: rgba(42, 157, 143, 0.16) !important;
        }

        .bg-indigo-50 {
            background-color: rgba(42, 157, 143, 0.08) !important;
        }

        .border-indigo-100,
        .border-indigo-200 {
            border-color: rgba(42, 157, 143, 0.3) !important;
        }

        .focus\:ring-indigo-500:focus {
            --tw-ring-color: rgba(42, 157, 143, 0.4) !important;
        }

        .focus\:border-indigo-500:focus {
            border-color: rgba(42, 157, 143, 0.5) !important;
        }
    </style>
</head>
<body class="min-h-screen theme-body">
    <div class="page-bg" aria-hidden="true"></div>
    <nav class="site-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0">
                    <a href="index.php" class="brand">
                        <span class="brand-dot"></span>
                        VisaNusa
                    </a>
                </div>

                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <span class="pill hidden md:inline-flex"><i class="fas fa-coins"></i><?= formatRupiah((int) $user['credit']) ?></span>
                        <a href="dashboard.php" class="nav-link">Dashboard</a>
                        <a href="deposit.php" class="nav-link">Deposit</a>
                        <a href="profile.php" class="nav-link">Profil</a>
                        <a href="logout.php" class="nav-link">Keluar</a>
                        <span class="chip">
                            <i class="fas fa-user"></i><?= sanitize($user['username']) ?>
                        </span>
                    <?php else: ?>
                        <a href="login.php" class="btn-primary">
                            <i class="fas fa-user-circle"></i>
                            <span class="hidden sm:inline">Masuk / Daftar</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
