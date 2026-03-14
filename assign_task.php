<?php
require_once '../config.php';
require_once '../includes/header.php';

$message = '';

// Get volunteer ID from URL
$volunteer_id = isset($_GET['id']) ? clean_input($_GET['id']) : null;

// Get volunteer details
$volunteer = null;
if ($volunteer_id) {
    $vol_query = mysqli_query($conn, "SELECT * FROM volunteers WHERE volunteer_id='$volunteer_id'");
    $volunteer = mysqli_fetch_assoc($vol_query);
}

// Get all pending requests
$pending_requests = mysqli_query($conn, 
    "SELECT vr.*, v.victim_name, v.phone, v.address 
     FROM victim_requests vr 
     JOIN victims v ON vr.victim_id = v.victim_id 
     WHERE vr.status = 'pending' 
     ORDER BY vr.priority DESC, vr.request_date ASC"
);

// Get all volunteers if no ID provided
if (!$volunteer_id) {
    $volunteers = mysqli_query($conn, "SELECT * FROM volunteers WHERE status='active' ORDER BY volunteer_name");
}

// Handle task assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vol_id = clean_input($_POST['volunteer_id']);
    $request_id = clean_input($_POST['request_id']);
    $notes = clean_input($_POST['notes']);
    
    // Insert volunteer request
    $sql = "INSERT INTO volunteer_requests (volunteer_id, request_id, notes) 
            VALUES ('$vol_id', '$request_id', '$notes')";
    
    if (mysqli_query($conn, $sql)) {
        // Update victim request status
        mysqli_query($conn, "UPDATE victim_requests SET status='in_progress' WHERE request_id='$request_id'");
        $message = show_alert("Task assigned successfully! The volunteer will be notified.", "success");
    } else {
        $message = show_alert("Error: " . mysqli_error($conn), "error");
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #667eea; margin-bottom: 2rem;">📋 Assign Task to Volunteer</h2>
        
        <?php echo $message; ?>
        
        <?php if ($volunteer): ?>
            <!-- Show volunteer info if ID provided -->
            <div style="background: #e7f3ff; padding: 1.5rem; border-radius: 5px; border-left: 4px solid #667eea; margin-bottom: 2rem;">
                <h3 style="color: #667eea; margin-bottom: 1rem;">Assigning task to:</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($volunteer['volunteer_name']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($volunteer['phone']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($volunteer['email']); ?></p>
                <p><strong>Skills:</strong> <?php echo htmlspecialchars($volunteer['skills']); ?></p>
                <p><strong>Availability:</strong> <?php echo htmlspecialchars($volunteer['availability']); ?></p>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php if (!$volunteer_id): ?>
                <div class="form-group">
                    <label for="volunteer_id">Select Volunteer *</label>
                    <select id="volunteer_id" name="volunteer_id" required>
                        <option value="">-- Choose Volunteer --</option>
                        <?php while($vol = mysqli_fetch_assoc($volunteers)): ?>
                            <option value="<?php echo $vol['volunteer_id']; ?>">
                                <?php echo htmlspecialchars($vol['volunteer_name']) . ' - ' . htmlspecialchars($vol['availability']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="volunteer_id" value="<?php echo $volunteer_id; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="request_id">Select Relief Request *</label>
                <select id="request_id" name="request_id" required onchange="showRequestDetails()">
                    <option value="">-- Choose Request --</option>
                    <?php while($req = mysqli_fetch_assoc($pending_requests)): ?>
                        <option value="<?php echo $req['request_id']; ?>" 
                                data-victim="<?php echo htmlspecialchars($req['victim_name']); ?>"
                                data-phone="<?php echo htmlspecialchars($req['phone']); ?>"
                                data-address="<?php echo htmlspecialchars($req['address']); ?>"
                                data-type="<?php echo htmlspecialchars($req['request_type']); ?>"
                                data-priority="<?php echo htmlspecialchars($req['priority']); ?>"
                                data-description="<?php echo htmlspecialchars($req['description']); ?>">
                            #<?php echo $req['request_id']; ?> - <?php echo htmlspecialchars($req['victim_name']); ?> 
                            (<?php echo htmlspecialchars($req['request_type']); ?>) - 
                            <?php echo strtoupper($req['priority']); ?> Priority
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <!-- Request Details Display -->
            <div id="requestDetails" style="display: none; background: #f8f9fa; padding: 1.5rem; border-radius: 5px; margin-bottom: 1.5rem;">
                <h3 style="color: #667eea; margin-bottom: 1rem;">Request Details:</h3>
                <p><strong>Victim:</strong> <span id="victimName"></span></p>
                <p><strong>Phone:</strong> <span id="victimPhone"></span></p>
                <p><strong>Address:</strong> <span id="victimAddress"></span></p>
                <p><strong>Request Type:</strong> <span id="requestType"></span></p>
                <p><strong>Priority:</strong> <span id="requestPriority"></span></p>
                <p><strong>Description:</strong> <span id="requestDescription"></span></p>
            </div>
            
            <div class="form-group">
                <label for="notes">Assignment Notes (Instructions for Volunteer)</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Provide any specific instructions, meeting points, items to bring, etc."></textarea>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Assign Task</button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem;">
            <a href="view_volunteers.php" style="color: #667eea;">← Back to Volunteers List</a>
        </p>
    </div>
</div>

<script>
function showRequestDetails() {
    const select = document.getElementById('request_id');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        document.getElementById('victimName').textContent = selectedOption.dataset.victim;
        document.getElementById('victimPhone').textContent = selectedOption.dataset.phone;
        document.getElementById('victimAddress').textContent = selectedOption.dataset.address;
        document.getElementById('requestType').textContent = selectedOption.dataset.type;
        document.getElementById('requestPriority').textContent = selectedOption.dataset.priority.toUpperCase();
        document.getElementById('requestDescription').textContent = selectedOption.dataset.description;
        document.getElementById('requestDetails').style.display = 'block';
    } else {
        document.getElementById('requestDetails').style.display = 'none';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>