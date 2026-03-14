<?php
require_once '../config.php';
require_once 'check_auth.php'; // Check if admin is logged in

// Get comprehensive statistics
$stats = [];

// Donors & Donations
$stats['total_donors'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donors"))['count'];
$stats['total_donations'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM donations"))['count'];
$stats['total_amount'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM donations"))['total'] ?? 0;

// Victims & Requests
$stats['total_victims'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victims"))['count'];
$stats['total_requests'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victim_requests"))['count'];
$stats['pending_requests'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victim_requests WHERE status='pending'"))['count'];
$stats['critical_requests'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victim_requests WHERE priority='critical' AND status='pending'"))['count'];

// Volunteers
$stats['total_volunteers'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteers"))['count'];
$stats['active_volunteers'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteers WHERE status='active'"))['count'];

// Certificates
$stats['total_certificates'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteer_certificates"))['count'];

// Resource Centers
$stats['total_centers'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM resource_centers"))['count'];

// Get pending requests
$pending_requests = mysqli_query($conn, 
    "SELECT vr.*, v.victim_name, v.phone, v.address 
     FROM victim_requests vr 
     JOIN victims v ON vr.victim_id = v.victim_id 
     WHERE vr.status = 'pending' 
     ORDER BY 
        CASE vr.priority 
            WHEN 'critical' THEN 1 
            WHEN 'high' THEN 2 
            WHEN 'medium' THEN 3 
            ELSE 4 
        END,
        vr.request_date DESC 
     LIMIT 10"
);

// Get recent donations
$recent_donations = mysqli_query($conn,
    "SELECT d.*, don.donor_name 
     FROM donations d 
     JOIN donors don ON d.donor_id = don.donor_id 
     ORDER BY d.donation_date DESC 
     LIMIT 5"
);

// Get volunteers eligible for certificates (completed at least 1 task)
$eligible_volunteers = mysqli_query($conn,
    "SELECT v.*, 
            COUNT(vr.volunteer_request_id) as tasks_completed,
            (SELECT COUNT(*) FROM volunteer_certificates vc WHERE vc.volunteer_id = v.volunteer_id) as certificate_count
     FROM volunteers v
     LEFT JOIN volunteer_requests vr ON v.volunteer_id = vr.volunteer_id AND vr.completion_status = 'completed'
     WHERE v.status = 'active'
     GROUP BY v.volunteer_id
     HAVING tasks_completed > 0
     ORDER BY tasks_completed DESC
     LIMIT 10"
);

// Handle request status update
if (isset($_POST['update_status'])) {
    $request_id = clean_input($_POST['request_id']);
    $new_status = clean_input($_POST['new_status']);
    
    mysqli_query($conn, "UPDATE victim_requests SET status='$new_status' WHERE request_id='$request_id'");
    header("Location: dashboard.php");
    exit();
}

require_once '../includes/header.php';
?>

<!-- Admin Header Bar -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 0; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="margin: 0; color: white;">👋 Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h3>
            <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Admin Dashboard - Last login: <?php echo date('F d, Y'); ?></p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <span style="background: rgba(255,255,255,0.2); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem;">
                🔐 Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
            </span>
            <a href="certificates.php" class="btn btn-success" style="background: rgba(255,255,255,0.2); border: 2px solid white; padding: 0.7rem 1.5rem;">
                📜 Certificates
            </a>
            <a href="reports.php" class="btn btn-success" style="background: rgba(255,255,255,0.2); border: 2px solid white; padding: 0.7rem 1.5rem;">
                📊 Reports
            </a>
            <a href="logout.php" class="btn" style="background: #dc3545; border: 2px solid #dc3545; padding: 0.7rem 1.5rem;">
                🚪 Logout
            </a>
        </div>
    </div>
</div>

<div class="container">
    <h1 style="text-align: center; color: #667eea; margin-bottom: 2rem;">📊 Admin Dashboard</h1>
    
    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>৳<?php echo number_format($stats['total_amount'], 2); ?></h3>
                <p>Total Donations</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3><?php echo $stats['total_donors']; ?></h3>
                <p>Registered Donors</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🆘</div>
            <div class="stat-info">
                <h3><?php echo $stats['total_victims']; ?></h3>
                <p>Victims Registered</p>
            </div>
        </div>
        
        <div class="stat-card" style="background: #fff3cd;">
            <div class="stat-icon">⏳</div>
            <div class="stat-info">
                <h3><?php echo $stats['pending_requests']; ?></h3>
                <p>Pending Requests</p>
            </div>
        </div>
        
        <div class="stat-card" style="background: #f8d7da;">
            <div class="stat-icon">🚨</div>
            <div class="stat-info">
                <h3><?php echo $stats['critical_requests']; ?></h3>
                <p>Critical Requests</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">🤝</div>
            <div class="stat-info">
                <h3><?php echo $stats['active_volunteers']; ?></h3>
                <p>Active Volunteers</p>
            </div>
        </div>
        
        <div class="stat-card" style="background: #d1ecf1;">
            <div class="stat-icon">📜</div>
            <div class="stat-info">
                <h3><?php echo $stats['total_certificates']; ?></h3>
                <p>Certificates Issued</p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="cards" style="margin-top: 2rem;">
        <div class="card">
            <h3>📜 Issue Certificates</h3>
            <p>Issue certificates to volunteers</p>
            <a href="certificates.php" class="btn">Manage Certificates</a>
        </div>
        <div class="card">
            <h3>📋 View All Requests</h3>
            <p>Manage all victim requests</p>
            <a href="../victims/view_requests.php" class="btn">View Requests</a>
        </div>
        <div class="card">
            <h3>📊 View Reports</h3>
            <p>Detailed analytics and reports</p>
            <a href="reports.php" class="btn">View Reports</a>
        </div>
    </div>
    
    <!-- Volunteers Eligible for Certificates -->
    <div class="table-container" style="margin-top: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">🏆 Volunteers Eligible for Certificates</h2>
        <table>
            <thead>
                <tr>
                    <th>Volunteer ID</th>
                    <th>Name</th>
                    <th>Tasks Completed</th>
                    <th>Certificates Issued</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($eligible_volunteers) > 0): ?>
                    <?php while($vol = mysqli_fetch_assoc($eligible_volunteers)): ?>
                    <tr>
                        <td>#<?php echo $vol['volunteer_id']; ?></td>
                        <td><?php echo htmlspecialchars($vol['volunteer_name']); ?></td>
                        <td><strong><?php echo $vol['tasks_completed']; ?></strong> tasks</td>
                        <td><?php echo $vol['certificate_count']; ?> certificates</td>
                        <td>
                            <a href="certificates.php?volunteer_id=<?php echo $vol['volunteer_id']; ?>" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                                Issue Certificate
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center;">No volunteers have completed tasks yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pending Requests Table -->
    <div class="table-container" style="margin-top: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">🆘 Pending Relief Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Victim Name</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($pending_requests) > 0): ?>
                    <?php while($req = mysqli_fetch_assoc($pending_requests)): ?>
                        <tr style="background: <?php 
                            echo $req['priority'] == 'critical' ? '#ffebee' : 
                                 ($req['priority'] == 'high' ? '#fff3e0' : 'white'); 
                        ?>">
                            <td>#<?php echo $req['request_id']; ?></td>
                            <td><?php echo htmlspecialchars($req['victim_name']); ?></td>
                            <td><?php echo htmlspecialchars($req['phone']); ?></td>
                            <td><?php echo htmlspecialchars($req['request_type']); ?></td>
                            <td>
                                <span style="padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem; font-weight: 600; background: <?php 
                                    echo $req['priority'] == 'critical' ? '#dc3545' : 
                                         ($req['priority'] == 'high' ? '#fd7e14' : 
                                         ($req['priority'] == 'medium' ? '#ffc107' : '#6c757d')); 
                                ?>; color: white;">
                                    <?php echo strtoupper($req['priority']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($req['request_date'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>">
                                    <select name="new_status" onchange="this.form.submit()" style="padding: 0.5rem; border-radius: 5px;">
                                        <option value="pending" selected>Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align: center;">No pending requests</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Recent Donations Table -->
    <div class="table-container" style="margin-top: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">💝 Recent Donations</h2>
        <table>
            <thead>
                <tr>
                    <th>Receipt #</th>
                    <th>Donor Name</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($recent_donations) > 0): ?>
                    <?php while($don = mysqli_fetch_assoc($recent_donations)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($don['receipt_number']); ?></td>
                            <td><?php echo htmlspecialchars($don['donor_name']); ?></td>
                            <td><?php echo htmlspecialchars($don['donation_type']); ?></td>
                            <td><?php echo $don['total_amount'] ? '৳' . number_format($don['total_amount'], 2) : 'Items'; ?></td>
                            <td><?php echo date('d M Y', strtotime($don['donation_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center;">No donations yet</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>