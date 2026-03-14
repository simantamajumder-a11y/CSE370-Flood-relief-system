<?php
require_once '../config.php';
require_once '../includes/header.php';

// Get volunteer phone for lookup (in real app, use login system)
$search_performed = false;
$certificates = null;
$volunteer = null;
$phone = '';

if (isset($_POST['search_certificates'])) {
    $phone = clean_input($_POST['phone']);
    $search_performed = true;
    
    // Get volunteer details
    $vol_query = mysqli_query($conn, "SELECT * FROM volunteers WHERE phone='$phone'");
    
    if (mysqli_num_rows($vol_query) > 0) {
        $volunteer = mysqli_fetch_assoc($vol_query);
        
        // Get certificates for this volunteer
        $cert_query = "
            SELECT * FROM volunteer_certificates 
            WHERE volunteer_id = '{$volunteer['volunteer_id']}'
            ORDER BY issue_date DESC
        ";
        $certificates = mysqli_query($conn, $cert_query);
        
        // Get task statistics
        $stats = mysqli_fetch_assoc(mysqli_query($conn, "
            SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN completion_status='completed' THEN 1 ELSE 0 END) as completed_tasks
            FROM volunteer_requests 
            WHERE volunteer_id = '{$volunteer['volunteer_id']}'
        "));
    }
}
?>

<div class="container">
    <div class="form-container" style="max-width: 700px;">
        <h2 style="text-align: center; color: #667eea; margin-bottom: 2rem;">📜 My Certificates</h2>
        
        <div class="alert alert-info">
            <strong>ℹ️ Note:</strong> Enter your registered phone number to view your certificates and volunteer record.
        </div>
        
        <!-- Search Form -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="phone">Enter Your Phone Number</label>
                <input type="tel" id="phone" name="phone" pattern="[0-9]{11}" placeholder="01XXXXXXXXX" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            
            <button type="submit" name="search_certificates" class="btn" style="width: 100%;">
                🔍 View My Certificates
            </button>
        </form>
    </div>
    
    <?php if ($search_performed): ?>
        <?php if ($volunteer): ?>
            <!-- Volunteer Profile -->
            <div class="form-container" style="max-width: 900px; margin-top: 2rem;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem; border-radius: 10px; color: white; margin-bottom: 2rem;">
                    <div style="display: flex; align-items: center; gap: 2rem;">
                        <div style="background: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">
                            🤝
                        </div>
                        <div style="flex: 1;">
                            <h2 style="margin: 0; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($volunteer['volunteer_name']); ?></h2>
                            <p style="margin: 0.25rem 0; opacity: 0.9;">📧 <?php echo htmlspecialchars($volunteer['email']); ?></p>
                            <p style="margin: 0.25rem 0; opacity: 0.9;">📞 <?php echo htmlspecialchars($volunteer['phone']); ?></p>
                            <p style="margin: 0.25rem 0; opacity: 0.9;">
                                <strong>Status:</strong> 
                                <span style="padding: 0.3rem 0.8rem; border-radius: 15px; background: <?php echo $volunteer['status'] == 'active' ? '#28a745' : '#dc3545'; ?>;">
                                    <?php echo ucfirst($volunteer['status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div class="stats-grid" style="margin-bottom: 2rem;">
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-info">
                            <h3><?php echo $stats['total_tasks']; ?></h3>
                            <p>Total Tasks Assigned</p>
                        </div>
                    </div>
                    <div class="stat-card" style="background: #d4edda;">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <h3><?php echo $stats['completed_tasks']; ?></h3>
                            <p>Tasks Completed</p>
                        </div>
                    </div>
                    <div class="stat-card" style="background: #d1ecf1;">
                        <div class="stat-icon">📜</div>
                        <div class="stat-info">
                            <h3><?php echo mysqli_num_rows($certificates); ?></h3>
                            <p>Certificates Earned</p>
                        </div>
                    </div>
                </div>
                
                <!-- Certificates -->
                <h2 style="color: #667eea; margin-bottom: 1.5rem;">🏆 My Certificates</h2>
                
                <?php if (mysqli_num_rows($certificates) > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                        <?php while($cert = mysqli_fetch_assoc($certificates)): ?>
                            <div class="card" style="border: 2px solid #667eea;">
                                <div style="text-align: center;">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎓</div>
                                    <h3 style="color: #667eea; margin-bottom: 0.5rem;">Certificate of Appreciation</h3>
                                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">
                                        <strong>Certificate #:</strong><br>
                                        <?php echo htmlspecialchars($cert['certificate_number']); ?>
                                    </p>
                                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                                        <p style="margin: 0.5rem 0;"><strong>Hours Completed:</strong> <?php echo $cert['hours_completed']; ?> hours</p>
                                        <p style="margin: 0.5rem 0;"><strong>Issue Date:</strong> <?php echo date('F d, Y', strtotime($cert['issue_date'])); ?></p>
                                    </div>
                                    <a href="../admin/view_certificate.php?id=<?php echo $cert['certificate_id']; ?>" class="btn" style="width: 100%;" target="_blank">
                                        👁️ View & Print Certificate
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; background: #f8f9fa; border-radius: 10px;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                        <h3 style="color: #667eea; margin-bottom: 0.5rem;">No Certificates Yet</h3>
                        <p style="color: #666;">Complete volunteer tasks to earn certificates!</p>
                        <p style="color: #666; margin-top: 1rem;">
                            You have completed <strong><?php echo $stats['completed_tasks']; ?></strong> tasks. 
                            Keep up the great work! Certificates will be issued by the admin.
                        </p>
                    </div>
                <?php endif; ?>
                
                <!-- Skills Section -->
                <div style="background: #e7f3ff; padding: 1.5rem; border-radius: 10px; margin-top: 2rem; border-left: 4px solid #667eea;">
                    <h3 style="color: #667eea; margin-bottom: 1rem;">💪 Your Skills</h3>
                    <p><?php echo nl2br(htmlspecialchars($volunteer['skills'])); ?></p>
                </div>
                
                <!-- Availability -->
                <div style="background: #fff3cd; padding: 1.5rem; border-radius: 10px; margin-top: 1rem; border-left: 4px solid #ffc107;">
                    <h3 style="color: #856404; margin-bottom: 1rem;">📅 Availability</h3>
                    <p><strong><?php echo ucfirst(str_replace('-', ' ', $volunteer['availability'])); ?></strong></p>
                </div>
            </div>
            
        <?php else: ?>
            <!-- No volunteer found -->
            <div class="form-container" style="max-width: 700px; margin-top: 2rem;">
                <div class="alert alert-error">
                    <strong>❌ Not Found:</strong> No volunteer record found with this phone number. 
                    Please check the number or <a href="register_volunteer.php" style="color: #667eea; font-weight: 600;">register as a volunteer</a>.
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Information Section -->
    <div class="cards" style="margin-top: 3rem;">
        <div class="card">
            <div class="card-icon">ℹ️</div>
            <h3>How to Get Certificates?</h3>
            <p>Complete volunteer tasks assigned to you. Once tasks are completed, our admin team will review your work and issue certificates recognizing your contribution.</p>
        </div>
        <div class="card">
            <div class="card-icon">📧</div>
            <h3>Need Help?</h3>
            <p>If you have questions about your certificates or volunteer status, contact us at relief@floodhelp.bd or call our hotline: 999</p>
        </div>
        <div class="card">
            <div class="card-icon">🤝</div>
            <h3>Not Registered?</h3>
            <p>Join our volunteer team today! Register now and start making a difference in your community.</p>
            <a href="register_volunteer.php" class="btn">Register Now</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>