<?php
require_once '../config.php';
require_once 'check_auth.php'; // Require admin login
require_once '../includes/header.php';

// Donation Statistics
$donation_stats = [];
$donation_stats['total_cash'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM donations WHERE total_amount IS NOT NULL"))['total'] ?? 0;
$donation_stats['total_donations'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donations"))['count'];
$donation_stats['avg_donation'] = $donation_stats['total_donations'] > 0 ? $donation_stats['total_cash'] / $donation_stats['total_donations'] : 0;

// Request Statistics by Type
$request_by_type = mysqli_query($conn, "
    SELECT request_type, COUNT(*) as count 
    FROM victim_requests 
    GROUP BY request_type 
    ORDER BY count DESC
");

// Request Statistics by Priority
$request_by_priority = mysqli_query($conn, "
    SELECT priority, COUNT(*) as count 
    FROM victim_requests 
    GROUP BY priority 
    ORDER BY FIELD(priority, 'critical', 'high', 'medium', 'low')
");

// Request Statistics by Status
$request_by_status = mysqli_query($conn, "
    SELECT status, COUNT(*) as count 
    FROM victim_requests 
    GROUP BY status
");

// Top Donors
$top_donors = mysqli_query($conn, "
    SELECT d.donor_name, d.email, 
           COUNT(don.donation_id) as donation_count,
           SUM(don.total_amount) as total_donated
    FROM donors d
    JOIN donations don ON d.donor_id = don.donor_id
    GROUP BY d.donor_id
    ORDER BY total_donated DESC
    LIMIT 10
");

// Volunteer Performance
$volunteer_performance = mysqli_query($conn, "
    SELECT v.volunteer_name, v.email,
           COUNT(vr.volunteer_request_id) as tasks_assigned,
           SUM(CASE WHEN vr.completion_status = 'completed' THEN 1 ELSE 0 END) as tasks_completed
    FROM volunteers v
    LEFT JOIN volunteer_requests vr ON v.volunteer_id = vr.volunteer_id
    WHERE v.status = 'active'
    GROUP BY v.volunteer_id
    ORDER BY tasks_completed DESC
    LIMIT 10
");

// Monthly Donation Trends
$monthly_donations = mysqli_query($conn, "
    SELECT DATE_FORMAT(donation_date, '%Y-%m') as month,
           COUNT(*) as donation_count,
           SUM(total_amount) as total_amount
    FROM donations
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
");

// Item Donations Summary
$item_donations = mysqli_query($conn, "
    SELECT dt.item_name, 
           SUM(dt.quantity) as total_quantity,
           dt.unit,
           COUNT(DISTINCT dt.donation_id) as donation_count
    FROM donation_track dt
    GROUP BY dt.item_name, dt.unit
    ORDER BY total_quantity DESC
    LIMIT 15
");
?>

<!-- Admin Header Bar -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 0; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="margin: 0; color: white;">📊 Reports & Analytics</h3>
            <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Comprehensive system reports and statistics | Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="dashboard.php" class="btn" style="background: rgba(255,255,255,0.2); border: 2px solid white; padding: 0.7rem 1.5rem;">
                📊 Dashboard
            </a>
            <a href="certificates.php" class="btn" style="background: rgba(255,255,255,0.2); border: 2px solid white; padding: 0.7rem 1.5rem;">
                📜 Certificates
            </a>
            <a href="logout.php" class="btn" style="background: #dc3545; border: 2px solid #dc3545; padding: 0.7rem 1.5rem;">
                🚪 Logout
            </a>
        </div>
    </div>
</div>

<div class="container">
    <h1 style="text-align: center; color: #667eea; margin-bottom: 2rem;">📊 Reports & Analytics</h1>
    
    <!-- Overall Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>৳<?php echo number_format($donation_stats['total_cash'], 2); ?></h3>
                <p>Total Cash Donations</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-info">
                <h3><?php echo $donation_stats['total_donations']; ?></h3>
                <p>Total Donations Received</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-info">
                <h3>৳<?php echo number_format($donation_stats['avg_donation'], 2); ?></h3>
                <p>Average Donation Amount</p>
            </div>
        </div>
    </div>
    
    <!-- Request Statistics by Type -->
    <div class="table-container" style="margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">📋 Requests by Type</h2>
        <table>
            <thead>
                <tr>
                    <th>Request Type</th>
                    <th>Total Requests</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victim_requests"))['count'];
                while($type = mysqli_fetch_assoc($request_by_type)):
                    $percentage = $total_requests > 0 ? ($type['count'] / $total_requests) * 100 : 0;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($type['request_type']); ?></td>
                    <td><?php echo $type['count']; ?></td>
                    <td>
                        <div style="background: #e0e0e0; border-radius: 10px; overflow: hidden;">
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: <?php echo $percentage; ?>%; padding: 0.5rem; color: white; text-align: center; min-width: 50px;">
                                <?php echo number_format($percentage, 1); ?>%
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Request Statistics by Priority -->
    <div class="cards" style="margin-bottom: 2rem;">
        <div class="card">
            <h3>Requests by Priority</h3>
            <table style="width: 100%; margin-top: 1rem;">
                <?php while($priority = mysqli_fetch_assoc($request_by_priority)): ?>
                <tr>
                    <td style="padding: 0.5rem 0; border: none;">
                        <span style="padding: 0.3rem 0.8rem; border-radius: 15px; background: <?php 
                            echo $priority['priority'] == 'critical' ? '#dc3545' : 
                                 ($priority['priority'] == 'high' ? '#fd7e14' : 
                                 ($priority['priority'] == 'medium' ? '#ffc107' : '#6c757d')); 
                        ?>; color: white; font-size: 0.85rem;">
                            <?php echo ucfirst($priority['priority']); ?>
                        </span>
                    </td>
                    <td style="padding: 0.5rem 0; text-align: right; border: none; font-weight: 600;">
                        <?php echo $priority['count']; ?> requests
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        
        <div class="card">
            <h3>Requests by Status</h3>
            <table style="width: 100%; margin-top: 1rem;">
                <?php while($status = mysqli_fetch_assoc($request_by_status)): ?>
                <tr>
                    <td style="padding: 0.5rem 0; border: none;">
                        <span style="padding: 0.3rem 0.8rem; border-radius: 15px; background: <?php 
                            echo $status['status'] == 'completed' ? '#28a745' : 
                                 ($status['status'] == 'in_progress' ? '#007bff' : 
                                 ($status['status'] == 'rejected' ? '#dc3545' : '#ffc107')); 
                        ?>; color: white; font-size: 0.85rem;">
                            <?php echo ucfirst(str_replace('_', ' ', $status['status'])); ?>
                        </span>
                    </td>
                    <td style="padding: 0.5rem 0; text-align: right; border: none; font-weight: 600;">
                        <?php echo $status['count']; ?> requests
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
    
    <!-- Top Donors -->
    <div class="table-container" style="margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">🏆 Top Donors</h2>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Donor Name</th>
                    <th>Email</th>
                    <th>Number of Donations</th>
                    <th>Total Amount Donated</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                while($donor = mysqli_fetch_assoc($top_donors)): 
                ?>
                <tr>
                    <td>
                        <span style="font-size: 1.5rem; font-weight: 600; color: <?php 
                            echo $rank == 1 ? '#FFD700' : ($rank == 2 ? '#C0C0C0' : ($rank == 3 ? '#CD7F32' : '#667eea')); 
                        ?>">
                            #<?php echo $rank; ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($donor['donor_name']); ?></td>
                    <td><?php echo htmlspecialchars($donor['email']); ?></td>
                    <td><?php echo $donor['donation_count']; ?> times</td>
                    <td><strong>৳<?php echo number_format($donor['total_donated'], 2); ?></strong></td>
                </tr>
                <?php 
                $rank++;
                endwhile; 
                ?>
            </tbody>
        </table>
    </div>
    
    <!-- Monthly Donation Trends -->
    <div class="table-container" style="margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">📅 Monthly Donation Trends</h2>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Number of Donations</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while($month = mysqli_fetch_assoc($monthly_donations)): ?>
                <tr>
                    <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                    <td><?php echo $month['donation_count']; ?></td>
                    <td>৳<?php echo number_format($month['total_amount'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Item Donations Summary -->
    <div class="table-container" style="margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">📦 Item Donations Summary</h2>
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Total Quantity</th>
                    <th>Unit</th>
                    <th>Number of Donations</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($item_donations) > 0): ?>
                    <?php while($item = mysqli_fetch_assoc($item_donations)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><strong><?php echo number_format($item['total_quantity']); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td><?php echo $item['donation_count']; ?> donations</td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">No item donations recorded yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Volunteer Performance -->
    <div class="table-container">
        <h2 style="margin-bottom: 1.5rem;">⭐ Top Performing Volunteers</h2>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Volunteer Name</th>
                    <th>Email</th>
                    <th>Tasks Assigned</th>
                    <th>Tasks Completed</th>
                    <th>Completion Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                while($vol = mysqli_fetch_assoc($volunteer_performance)): 
                    $completion_rate = $vol['tasks_assigned'] > 0 ? ($vol['tasks_completed'] / $vol['tasks_assigned']) * 100 : 0;
                ?>
                <tr>
                    <td>#<?php echo $rank; ?></td>
                    <td><?php echo htmlspecialchars($vol['volunteer_name']); ?></td>
                    <td><?php echo htmlspecialchars($vol['email']); ?></td>
                    <td><?php echo $vol['tasks_assigned']; ?></td>
                    <td><strong><?php echo $vol['tasks_completed']; ?></strong></td>
                    <td>
                        <div style="background: #e0e0e0; border-radius: 10px; overflow: hidden; min-width: 100px;">
                            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); width: <?php echo $completion_rate; ?>%; padding: 0.5rem; color: white; text-align: center; min-width: 40px;">
                                <?php echo number_format($completion_rate, 0); ?>%
                            </div>
                        </div>
                    </td>
                </tr>
                <?php 
                $rank++;
                endwhile; 
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>