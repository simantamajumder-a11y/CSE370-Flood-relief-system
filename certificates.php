<?php
require_once '../config.php';
require_once 'check_auth.php';
require_once '../includes/header.php';

$message = '';
$volunteer_id = isset($_GET['volunteer_id']) ? clean_input($_GET['volunteer_id']) : null;

// Handle certificate generation
if (isset($_POST['generate_certificate'])) {
    $vol_id = clean_input($_POST['volunteer_id']);
    $hours_completed = clean_input($_POST['hours_completed']);
    $issue_date = clean_input($_POST['issue_date']);
    
    // Generate unique certificate number
    $certificate_number = 'FRC-' . date('Y') . '-' . str_pad($vol_id, 5, '0', STR_PAD_LEFT) . '-' . rand(100, 999);
    
    // Insert certificate
    $sql = "INSERT INTO volunteer_certificates (volunteer_id, issue_date, certificate_number, hours_completed) 
            VALUES ('$vol_id', '$issue_date', '$certificate_number', '$hours_completed')";
    
    if (mysqli_query($conn, $sql)) {
        $message = show_alert("Certificate generated successfully! Certificate Number: $certificate_number", "success");
    } else {
        $message = show_alert("Error: " . mysqli_error($conn), "error");
    }
}

// Handle certificate deletion
if (isset($_POST['delete_certificate'])) {
    $cert_id = clean_input($_POST['certificate_id']);
    mysqli_query($conn, "DELETE FROM volunteer_certificates WHERE certificate_id='$cert_id'");
    $message = show_alert("Certificate deleted successfully!", "success");
}

// Get all volunteers with their task completion info
$volunteers_query = "
    SELECT v.*, 
           COUNT(DISTINCT vr.volunteer_request_id) as total_tasks,
           (SELECT COUNT(*) FROM volunteer_certificates vc WHERE vc.volunteer_id = v.volunteer_id) as certificate_count
    FROM volunteers v
    LEFT JOIN volunteer_requests vr ON v.volunteer_id = vr.volunteer_id AND vr.completion_status = 'completed'
    WHERE v.status = 'active'
    GROUP BY v.volunteer_id
    ORDER BY total_tasks DESC
";
$volunteers = mysqli_query($conn, $volunteers_query);

// Get all issued certificates
$certificates_query = "
    SELECT vc.*, v.volunteer_name, v.email, v.phone
    FROM volunteer_certificates vc
    JOIN volunteers v ON vc.volunteer_id = v.volunteer_id
    ORDER BY vc.issue_date DESC
";
$certificates = mysqli_query($conn, $certificates_query);

// Get selected volunteer details if volunteer_id is provided
$selected_volunteer = null;
if ($volunteer_id) {
    $vol_query = mysqli_query($conn, "
        SELECT v.*, 
               COUNT(DISTINCT vr.volunteer_request_id) as total_tasks
        FROM volunteers v
        LEFT JOIN volunteer_requests vr ON v.volunteer_id = vr.volunteer_id AND vr.completion_status = 'completed'
        WHERE v.volunteer_id = '$volunteer_id'
        GROUP BY v.volunteer_id
    ");
    $selected_volunteer = mysqli_fetch_assoc($vol_query);
}
?>

<!-- Admin Header Bar -->
<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 0; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="margin: 0; color: white;">📜 Certificate Management System</h3>
            <p style="margin: 0; opacity: 0.9; font-size: 0.9rem;">Issue and manage volunteer certificates | Logged in as: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="dashboard.php" class="btn" style="background: rgba(255,255,255,0.2); border: 2px solid white; padding: 0.7rem 1.5rem;">
                📊 Dashboard
            </a>
            <a href="reports.php" class="btn" style="background: rgba(255,255,255,0.2); border: 2px solid white; padding: 0.7rem 1.5rem;">
                📈 Reports
            </a>
            <a href="logout.php" class="btn" style="background: #dc3545; border: 2px solid #dc3545; padding: 0.7rem 1.5rem;">
                🚪 Logout
            </a>
        </div>
    </div>
</div>

<div class="container">
    <?php echo $message; ?>
    
    <!-- Statistics -->
    <?php
    $total_certificates = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteer_certificates"))['count'];
    $this_month = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteer_certificates WHERE MONTH(issue_date) = MONTH(CURRENT_DATE()) AND YEAR(issue_date) = YEAR(CURRENT_DATE())"))['count'];
    ?>
    
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon">📜</div>
            <div class="stat-info">
                <h3><?php echo $total_certificates; ?></h3>
                <p>Total Certificates Issued</p>
            </div>
        </div>
        <div class="stat-card" style="background: #d1ecf1;">
            <div class="stat-icon">📅</div>
            <div class="stat-info">
                <h3><?php echo $this_month; ?></h3>
                <p>Issued This Month</p>
            </div>
        </div>
        <div class="stat-card" style="background: #d4edda;">
            <div class="stat-icon">🤝</div>
            <div class="stat-info">
                <h3><?php echo mysqli_num_rows($volunteers); ?></h3>
                <p>Active Volunteers</p>
            </div>
        </div>
    </div>
    
    <!-- Generate Certificate Form -->
    <div class="form-container" style="margin-bottom: 3rem;">
        <h2 style="text-align: center; color: #667eea; margin-bottom: 2rem;">
            🎓 Generate New Certificate
        </h2>
        
        <?php if ($selected_volunteer): ?>
            <!-- Show selected volunteer info -->
            <div style="background: #e7f3ff; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; border-left: 4px solid #667eea;">
                <h3 style="color: #667eea; margin-bottom: 1rem;">Selected Volunteer:</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($selected_volunteer['volunteer_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($selected_volunteer['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($selected_volunteer['phone']); ?></p>
                <p><strong>Completed Tasks:</strong> <?php echo $selected_volunteer['total_tasks']; ?> tasks</p>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="volunteer_id">Select Volunteer *</label>
                <select id="volunteer_id" name="volunteer_id" required onchange="window.location.href='certificates.php?volunteer_id=' + this.value">
                    <option value="">-- Choose Volunteer --</option>
                    <?php
                    mysqli_data_seek($volunteers, 0);
                    while($vol = mysqli_fetch_assoc($volunteers)):
                    ?>
                        <option value="<?php echo $vol['volunteer_id']; ?>" <?php echo ($volunteer_id == $vol['volunteer_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($vol['volunteer_name']); ?> 
                            (<?php echo $vol['total_tasks']; ?> tasks completed, 
                            <?php echo $vol['certificate_count']; ?> certificates issued)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="issue_date">Issue Date *</label>
                <input type="date" id="issue_date" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="hours_completed">Total Hours Completed *</label>
                <input type="number" id="hours_completed" name="hours_completed" min="1" placeholder="e.g., 40" required>
                <small style="display: block; margin-top: 0.5rem; color: #666;">
                    Estimated volunteer hours for completed tasks
                </small>
            </div>
            
            <button type="submit" name="generate_certificate" class="btn" style="width: 100%;">
                🎓 Generate Certificate
            </button>
        </form>
    </div>
    
    <!-- All Issued Certificates -->
    <div class="table-container">
        <h2 style="margin-bottom: 1.5rem;">📋 All Issued Certificates</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Certificate #</th>
                    <th>Volunteer Name</th>
                    <th>Email</th>
                    <th>Hours Completed</th>
                    <th>Issue Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($certificates) > 0): ?>
                    <?php while($cert = mysqli_fetch_assoc($certificates)): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($cert['certificate_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($cert['volunteer_name']); ?></td>
                        <td><?php echo htmlspecialchars($cert['email']); ?></td>
                        <td><?php echo $cert['hours_completed']; ?> hours</td>
                        <td><?php echo date('d M Y', strtotime($cert['issue_date'])); ?></td>
                        <td>
                            <a href="view_certificate.php?id=<?php echo $cert['certificate_id']; ?>" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.85rem; margin-right: 0.5rem;" target="_blank">
                                👁️ View
                            </a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this certificate?');">
                                <input type="hidden" name="certificate_id" value="<?php echo $cert['certificate_id']; ?>">
                                <button type="submit" name="delete_certificate" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem; background: #dc3545;">
                                    🗑️ Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">
                            No certificates issued yet. Generate your first certificate above!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Volunteers Without Certificates -->
    <div class="table-container" style="margin-top: 2rem;">
        <h2 style="margin-bottom: 1.5rem;">⏳ Volunteers Awaiting Certificates</h2>
        <table>
            <thead>
                <tr>
                    <th>Volunteer Name</th>
                    <th>Email</th>
                    <th>Tasks Completed</th>
                    <th>Certificates Issued</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                mysqli_data_seek($volunteers, 0);
                $found = false;
                while($vol = mysqli_fetch_assoc($volunteers)):
                    if ($vol['total_tasks'] > 0 && $vol['certificate_count'] == 0):
                        $found = true;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vol['volunteer_name']); ?></td>
                        <td><?php echo htmlspecialchars($vol['email']); ?></td>
                        <td><strong><?php echo $vol['total_tasks']; ?></strong> tasks</td>
                        <td><?php echo $vol['certificate_count']; ?></td>
                        <td>
                            <a href="certificates.php?volunteer_id=<?php echo $vol['volunteer_id']; ?>" class="btn" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                Generate Certificate
                            </a>
                        </td>
                    </tr>
                <?php 
                    endif;
                endwhile; 
                
                if (!$found):
                ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem;">
                            All eligible volunteers have been issued certificates! 🎉
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>