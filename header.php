<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flood Relief Management System</title>
    <link rel="stylesheet" href="/flood_relief/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <h2>🌊 Flood Relief System</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="/flood_relief/index.php">Home</a></li>
                <li class="dropdown">
                    <a href="#">Donors</a>
                    <div class="dropdown-content">
                        <a href="/flood_relief/donors/add_donor.php">Register Donor</a>
                        <a href="/flood_relief/donors/donate.php">Make Donation</a>
                        <a href="/flood_relief/donors/view_donors.php">View Donors</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Victims</a>
                    <div class="dropdown-content">
                        <a href="/flood_relief/victims/register_victim.php">Register Victim</a>
                        <a href="/flood_relief/victims/submit_request.php">Submit Request</a>
                        <a href="/flood_relief/victims/view_requests.php">View Requests</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Volunteers</a>
                    <div class="dropdown-content">
                        <a href="/flood_relief/volunteers/register_volunteer.php">Register Volunteer</a>
                        <a href="/flood_relief/volunteers/view_volunteers.php">View Volunteers</a>
                        <a href="/flood_relief/volunteers/my_certificates.php">My Certificates</a>
                    </div>
                </li>
                <li class="dropdown">
                    <a href="#">Admin</a>
                    <div class="dropdown-content">
                        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                            <a href="/flood_relief/admin/dashboard.php">📊 Dashboard</a>
                            <a href="/flood_relief/admin/certificates.php">📜 Certificates</a>
                            <a href="/flood_relief/admin/reports.php">📈 Reports</a>
                            <a href="/flood_relief/admin/logout.php" style="color: #dc3545; font-weight: 600;">🚪 Logout (<?php echo htmlspecialchars($_SESSION['admin_name']); ?>)</a>
                        <?php else: ?>
                            <a href="/flood_relief/admin/login.php">🔐 Admin Login</a>
                        <?php endif; ?>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    <div class="main-container">