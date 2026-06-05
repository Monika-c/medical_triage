<?php
// includes/header.php
// BCS403 - DBMS Project
require_once 'auth.php'; // Ensure user is authenticated
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEC Hassan Medical Triage System</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .priority-Critical { background-color: #dc3545; color: white; }
        .priority-High { background-color: #fd7e14; color: black; }
        .priority-Medium { background-color: #0dcaf0; color: black; }
        .priority-Low { background-color: #198754; color: white; }
        .status-Waiting { color: #ffc107; font-weight: bold; }
        .status-Treatment { color: #0d6efd; font-weight: bold; }
        .status-Discharged { color: #198754; font-weight: bold; }
        .status-Transferred { color: #6c757d; font-weight: bold; }
        .card-icon { font-size: 3rem; opacity: 0.3; position: absolute; right: 15px; bottom: 10px; }
        .card-stat { position: relative; overflow: hidden; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?php echo $base_url; ?>/index.php">
        <i class="bi bi-hospital text-danger fs-4 me-2"></i> GEC Hassan - Medical Triage System
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $base_url; ?>/index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-people"></i> Patients
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/patients/add.php">Add New Patient</a></li>
            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/patients/list.php">Patient List</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-card-checklist"></i> Visits & Triage
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/visits/admit.php">Admit Patient</a></li>
            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/visits/list.php">All Visits</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item fw-bold text-danger" href="<?php echo $base_url; ?>/triage/dashboard.php">Live Triage Board</a></li>
            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/triage/logs.php">System Triage Logs</a></li>
            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/transfers/transfer.php">Transfer Patient</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-bar-chart"></i> Reports
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/reports/department.php">Department Summary</a></li>
            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/reports/doctor.php">Doctor Workload</a></li>
            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/reports/daily.php">Daily Report</a></li>
            <li><a class="dropdown-item" href="<?php echo $base_url; ?>/reports/patient_history.php">Patient History Search</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-primary" href="<?php echo $base_url; ?>/reports/queries_demo.php"><i class="bi bi-code-slash"></i> SQL Queries Demo</a></li>
          </ul>
        </li>
        <li class="nav-item ms-2">
            <a href="<?php echo $base_url; ?>/logout.php" class="btn btn-outline-light btn-sm mt-1"><i class="bi bi-box-arrow-right"></i> Logout (Admin)</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container pb-5">
    <!-- Main content starts here -->
