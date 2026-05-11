<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Analytics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar { min-height: 100vh; background: #2c3e50; color: white; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #34495e; color: white; }
        .card-insight { border-left: 5px solid #3498db; }
        .card-gap-critical { border-left-color: #e74c3c; }
        .card-gap-significant { border-left-color: #f39c12; }
        .card-gap-emerging { border-left-color: #27ae60; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php if($this->session->userdata('is_authenticated')): ?>
        <nav class="col-md-2 d-none d-md-block sidebar">
            <div class="position-sticky pt-3">
                <h5 class="px-3 mb-4">Alumni Influencers</h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="<?= site_url('dashboard') ?>" class="<?= $this->uri->segment(2) == '' ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('dashboard/graphs') ?>" class="<?= $this->uri->segment(2) == 'graphs' ? 'active' : '' ?>">
                            <i class="bi bi-graph-up me-2"></i> Analytics Graphs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('dashboard/alumni') ?>" class="<?= $this->uri->segment(2) == 'alumni' ? 'active' : '' ?>">
                            <i class="bi bi-people me-2"></i> Alumni Explorer
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= site_url('dashboard/security') ?>" class="<?= $this->uri->segment(2) == 'security' ? 'active' : '' ?>">
                            <i class="bi bi-shield-lock me-2"></i> Security & Usage
                        </a>
                    </li>
                    <hr>
                    <li class="nav-item">
                        <a href="<?= site_url('auth/logout') ?>">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
        <?php else: ?>
        <main class="col-12 px-md-4 py-4">
        <?php endif; ?>
