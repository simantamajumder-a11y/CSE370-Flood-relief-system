<?php
require_once 'config.php';
require_once 'includes/header.php';

// Get statistics
$total_donors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donors"))['count'];
$total_donations = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donations"))['count'];
$total_victims = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victims"))['count'];
$total_volunteers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteers"))['count'];
$pending_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victim_requests WHERE status='pending'"))['count'];
?>

<div class="container">
    <!-- Hero Section -->
    <section class="hero">
        <h1>🌊 Flood Relief Management System</h1>
        <p>Together we can make a difference. Connect, Donate, Volunteer, and Help Those in Need.</p>
        <a href="donors/donate.php" class="btn">Donate Now</a>
        <a href="volunteers/register_volunteer.php" class="btn btn-secondary">Become a Volunteer</a>
    </section>

    <!-- Statistics Dashboard -->
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3><?php echo $total_donations; ?></h3>
                <p>Total Donations</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3><?php echo $total_donors; ?></h3>
                <p>Registered Donors</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🆘</div>
            <div class="stat-info">
                <h3><?php echo $total_victims; ?></h3>
                <p>Victims Registered</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🤝</div>
            <div class="stat-info">
                <h3><?php echo $total_volunteers; ?></h3>
                <p>Active Volunteers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-info">
                <h3><?php echo $pending_requests; ?></h3>
                <p>Pending Requests</p>
            </div>
        </div>
    </section>

    <!-- Features Cards -->
    <section class="cards">
        <div class="card">
            <div class="card-icon">💝</div>
            <h3>Make a Donation</h3>
            <p>Your contribution can save lives. Donate money or essential items to help flood victims rebuild their lives.</p>
            <a href="donors/donate.php" class="btn">Donate Now</a>
        </div>

        <div class="card">
            <div class="card-icon">🆘</div>
            <h3>Request Assistance</h3>
            <p>If you're affected by the flood, register and submit your request for food, shelter, medical aid, or other necessities.</p>
            <a href="victims/submit_request.php" class="btn">Request Help</a>
        </div>

        <div class="card">
            <div class="card-icon">🤝</div>
            <h3>Volunteer</h3>
            <p>Join our team of volunteers. Help distribute aid, provide medical assistance, or offer your skills to those in need.</p>
            <a href="volunteers/register_volunteer.php" class="btn">Join Us</a>
        </div>
    </section>

    <!-- Recent Activities -->
    <section class="table-container" style="margin-top: 3rem;">
        <h2 style="margin-bottom: 1.5rem;">📋 Recent Donation Activities</h2>
        <table>
            <thead>
                <tr>
                    <th>Donor Name</th>
                    <th>Donation Date</th>
                    <th>Amount</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recent_donations = mysqli_query($conn, 
                    "SELECT donors.donor_name, donations.donation_date, donations.total_amount, donations.donation_type 
                     FROM donations 
                     JOIN donors ON donations.donor_id = donors.donor_id 
                     ORDER BY donations.donation_date DESC 
                     LIMIT 5"
                );
                
                if (mysqli_num_rows($recent_donations) > 0) {
                    while ($donation = mysqli_fetch_assoc($recent_donations)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($donation['donor_name']) . "</td>";
                        echo "<td>" . date('d M Y', strtotime($donation['donation_date'])) . "</td>";
                        echo "<td>" . ($donation['total_amount'] ? '৳' . number_format($donation['total_amount'], 2) : 'Items') . "</td>";
                        echo "<td>" . htmlspecialchars($donation['donation_type']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center;'>No donations yet. Be the first to donate!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>