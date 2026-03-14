<?php
require_once '../config.php';
require_once '../includes/header.php';

$message = '';
$request_id = isset($_GET['id']) ? clean_input($_GET['id']) : null;

if (!$request_id) {
    header("Location: view_requests.php");
    exit();
}

// Get request details
$request_query = "
    SELECT vr.*, v.*
    FROM victim_requests vr
    JOIN victims v ON vr.victim_id = v.victim_id
    WHERE vr.request_id = '$request_id'
";
$request = mysqli_fetch_assoc(mysqli_query($conn, $request_query));

if (!$request) {
    header("Location: view_requests.php");
    exit();
}

// Get assigned volunteers
$assigned_volunteers = mysqli_query($conn, "
    SELECT vol.*, vr.accepted_date, vr.completion_status, vr.notes
    FROM volunteer_requests vr
    JOIN volunteers vol ON vr.volunteer_id = vol.volunteer_id
    WHERE vr.request_id = '$request_id'
    ORDER BY vr.accepted_date DESC
");

// Handle status update
if (isset($_POST['update_status'])) {
    $new_status = clean_input($_POST['new_status']);
    mysqli_query($conn, "UPDATE victim_requests SET status='$new_status' WHERE request_id='$request_id'");
    $message = show_alert("Request status updated successfully!", "success");
    $request['status'] = $new_status;
}

// Handle priority update
if (isset($_POST['update_priority'])) {
    $new_priority = clean_input($_POST['new_priority']);
    mysqli_query($conn, "UPDATE victim_requests SET priority='$new_priority' WHERE request_id='$request_id'");
    $message = show_alert("Request priority updated successfully!", "success");
    $request['priority'] = $new_priority;
}
?>

<div class="container">
    <?php echo $message; ?>
    
    <div style="margin-bottom: 2rem;">
        <a href="view_requests.php" style="color: #667eea; text-decoration: none; font-size: 1rem;">← Back to All Requests</a>
    </div>
    
    <div class="form-container" style="max-width: 900px;">
        <h2 style="text-align: center; color: #667eea; margin-bottom: 2rem;">
            🆘 Request Details #<?php echo $request['request_id']; ?>
        </h2>
        
        <!-- Status Badges -->
        <div style="display: flex; gap: 1rem; justify-content: center; margin-bottom: 2rem;">
            <span style="padding: 0.5rem 1.5rem; border-radius: 20px; font-weight: 600; background: <?php 
                echo $request['status'] == 'completed' ? '#28a745' : 
                     ($request['status'] == 'in_progress' ? '#007bff' : 
                     ($request['status'] == 'rejected' ? '#dc3545' : '#ffc107')); 
            ?>; color: white;">
                Status: <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
            </span>
            <span style="padding: 0.5rem 1.5rem; border-radius: 20px; font-weight: 600; background: <?php 
                echo $request['priority'] == 'critical' ? '#dc3545' : 
                     ($request['priority'] == 'high' ? '#fd7e14' : 
                     ($request['priority'] == 'medium' ? '#ffc107' : '#6c757d')); 
            ?>; color: white;">
                Priority: <?php echo strtoupper($request['priority']); ?>
            </span>
        </div>
        
        <!-- Victim Information -->
        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
            <h3 style="color: #667eea; margin-bottom: 1rem;">👤 Victim Information</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($request['victim_name']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($request['phone']); ?></p>
                    <p><strong>Email:</strong> <?php echo $request['email'] ? htmlspecialchars($request['email']) : 'Not provided'; ?></p>
                </div>
                <div>
                    <p><strong>Family Size:</strong> <?php echo $request['family_size']; ?> members</p>
                    <p><strong>Victim ID:</strong> #<?php echo $request['victim_id']; ?></p>
                    <p><strong>Registered:</strong> <?php echo date('d M Y', strtotime($request['created_at'])); ?></p>
                </div>
            </div>
            <div style="margin-top: 1rem;">
                <p><strong>📍 Address:</strong> <?php echo htmlspecialchars($request['address']); ?></p>
            </div>
        </div>
        
        <!-- Request Details -->
        <div style="background: #fff3cd; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; border-left: 4px solid #ffc107;">
            <h3 style="color: #856404; margin-bottom: 1rem;">📋 Request Details</h3>
            <p><strong>Request Type:</strong> <?php echo htmlspecialchars($request['request_type']); ?></p>
            <p><strong>Request Date:</strong> <?php echo date('d M Y, h:i A', strtotime($request['request_date'])); ?></p>
            <p><strong>Description:</strong></p>
            <div style="background: white; padding: 1rem; border-radius: 5px; margin-top: 0.5rem;">
                <?php echo nl2br(htmlspecialchars($request['description'])); ?>
            </div>
        </div>
        
        <!-- Assigned Volunteers -->
        <div style="background: #e7f3ff; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; border-left: 4px solid #667eea;">
            <h3 style="color: #667eea; margin-bottom: 1rem;">🤝 Assigned Volunteers</h3>
            
            <?php if (mysqli_num_rows($assigned_volunteers) > 0): ?>
                <?php while($vol = mysqli_fetch_assoc($assigned_volunteers)): ?>
                    <div style="background: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($vol['volunteer_name']); ?></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($vol['phone']); ?></p>
                            </div>
                            <div>
                                <p><strong>Assigned:</strong> <?php echo date('d M Y', strtotime($vol['accepted_date'])); ?></p>
                                <p><strong>Status:</strong> 
                                    <span style="padding: 0.2rem 0.6rem; border-radius: 10px; font-size: 0.85rem; background: <?php 
                                        echo $vol['completion_status'] == 'completed' ? '#28a745' : '#007bff'; 
                                    ?>; color: white;">
                                        <?php echo ucfirst(str_replace('_', ' ', $vol['completion_status'])); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <?php if ($vol['notes']): ?>
                            <p style="margin-top: 0.5rem;"><strong>Notes:</strong> <?php echo htmlspecialchars($vol['notes']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="background: white; padding: 1rem; border-radius: 5px;">No volunteers assigned yet.</p>
                <a href="../volunteers/assign_task.php?id=<?php echo $request_id; ?>" class="btn" style="margin-top: 1rem;">Assign Volunteer</a>
            <?php endif; ?>
        </div>
        
        <!-- Action Forms -->
        <div class="cards" style="margin-top: 2rem;">
            <!-- Update Status -->
            <div class="card">
                <h3>Update Status</h3>
                <form method="POST" style="margin-top: 1rem;">
                    <select name="new_status" required style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 2px solid #e0e0e0; margin-bottom: 1rem;">
                        <option value="pending" <?php echo $request['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $request['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $request['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="rejected" <?php echo $request['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <button type="submit" name="update_status" class="btn" style="width: 100%;">Update Status</button>
                </form>
            </div>
            
            <!-- Update Priority -->
            <div class="card">
                <h3>Update Priority</h3>
                <form method="POST" style="margin-top: 1rem;">
                    <select name="new_priority" required style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 2px solid #e0e0e0; margin-bottom: 1rem;">
                        <option value="low" <?php echo $request['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo $request['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo $request['priority'] == 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="critical" <?php echo $request['priority'] == 'critical' ? 'selected' : ''; ?>>Critical</option>
                    </select>
                    <button type="submit" name="update_priority" class="btn btn-secondary" style="width: 100%;">Update Priority</button>
                </form>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <a href="../volunteers/assign_task.php?request=<?php echo $request_id; ?>" class="btn btn-success" style="flex: 1; text-align: center;">
                Assign to Volunteer
            </a>
            <a href="view_requests.php" class="btn" style="flex: 1; text-align: center;">
                Back to All Requests
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>