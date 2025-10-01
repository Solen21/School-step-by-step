<?php
include "../../includes/header.php";
?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Management Dashboard</h2> 
        <a href="../../../index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Admin Dashboard</a>

    </div>

    <div class="alert alert-info">
        <h4 class="alert-heading">Welcome To Management System</h4>
        <p>You have full Management in the system.</p>
    </div>

    <div class="row">
        
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-layer-group fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Manage Grades</h5>
                    <p class="card-text">Create and manage grade levels and their streams.</p>
                    <a href="grades.php" class="btn btn-success">Go to Grades</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-door-open fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Manage Sections</h5>
                    <p class="card-text">Organize students by creating and editing sections.</p>
                    <a href="sections.php" class="btn btn-primary">Go to Sections</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-user-graduate fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Manage Students</h5>
                    <p class="card-text">View, add, and edit student records.</p>
                    <a href="students.php" class="btn btn-warning">Go to Students</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-book fa-3x text-secondary mb-3"></i>
                    <h5 class="card-title">Manage Subjects</h5>
                    <p class="card-text">Define the subjects taught for each grade level.</p>
                    <a href="subjects.php" class="btn btn-secondary">Go to Subjects</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-chalkboard-teacher fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Manage Teachers</h5>
                    <p class="card-text">View, add, and edit teacher records.</p>
                    <a href="../manage_teachers.php" class="btn btn-info">Go to Teachers</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-user-shield fa-3x text-dark mb-3"></i>
                    <h5 class="card-title">Manage Guardians</h5>
                    <p class="card-text">Add, edit, and link guardians to students.</p>
                    <a href="../manage_guardians.php" class="btn btn-dark">Go to Guardians</a>
                </div>
            </div>
        </div>
