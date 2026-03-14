<?php
require_once '../config.php';
require_once '../includes/header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // First, register the victim if new
    $victim_name = clean_input($_POST['victim_name']);
    $phone = clean_input($_POST['phone']);
    $email = clean_input($_POST['email']);
    $address = clean_input($_POST['address']);
    $family_size = clean_input($_POST['family_size']);
    
    // Check if victim already exists
    $check = mysqli_query($conn, "SELECT victim_id FROM victims WHERE phone='$phone' OR email='$email'");
    
    if (mysqli_num_rows($check) > 0) {
        $victim = mysqli_fetch_assoc($check);
        $victim_id = $victim['victim_id'];
    } else {
        // Insert new victim
        $sql = "INSERT INTO victims (victim_name, phone, email, address, family_size) 
                VALUES ('$victim_name', '$phone', '$email', '$address', '$family_size')";
        mysqli_query($conn, $sql);
        $victim_id = mysqli_insert_id($conn);
    }
    
    // Insert request
    $request_type = clean_input($_POST['request_type']);
    $description = clean_input($_POST['description']);
    $priority = clean_input($_POST['priority']);
    
    $req_sql = "INSERT INTO victim_requests (victim_id, request_type, description, priority) 
                VALUES ('$victim_id', '$request_type', '$description', '$priority')";
    
    if (mysqli_query($conn, $req_sql)) {
        $request_id = mysqli_insert_id($conn);
        $message = show_alert("Your request has been submitted successfully! Request ID: #$request_id. Our team will contact you soon.", "success");
    } else {
        $message = show_alert("Error submitting request: " . mysqli_error($conn), "error");
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #667eea; margin-bottom: 2rem;">🆘 Request Emergency Assistance</h2>
        
        <div class="alert alert-info">
            <strong>📢 Important:</strong> Please provide accurate information so our relief team can reach you quickly. All requests are processed on priority basis.
        </div>
        
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <h3 style="color: #667eea; margin-bottom: 1rem;">Your Information</h3>
            
            <div class="form-group">
                <label for="victim_name">Full Name *</label>
                <input type="text" id="victim_name" name="victim_name" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" pattern="[0-9]{11}" placeholder="01XXXXXXXXX" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address (Optional)</label>
                <input type="email" id="email" name="email">
            </div>
            
            <div class="form-group">
                <label for="address">Current Location/Address *</label>
                <textarea id="address" name="address" placeholder="Please provide detailed address so relief team can find you" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="family_size">Family Size *</label>
                <input type="number" id="family_size" name="family_size" min="1" placeholder="Number of family members" required>
            </div>
            
            <hr style="margin: 2rem 0;">
            
            <h3 style="color: #667eea; margin-bottom: 1rem;">Assistance Required</h3>
            
            <div class="form-group">
                <label for="request_type">Type of Assistance Needed *</label>
                <select id="request_type" name="request_type" required>
                    <option value="">-- Select Type --</option>
                    <option value="Food">Food & Water</option>
                    <option value="Medical">Medical Assistance</option>
                    <option value="Shelter">Shelter/Temporary Housing</option>
                    <option value="Clothing">Clothing & Essentials</option>
                    <option value="Rescue">Emergency Rescue</option>
                    <option value="Multiple">Multiple Types</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="priority">Priority Level *</label>
                <select id="priority" name="priority" required>
                    <option value="low">Low - Can wait a few days</option>
                    <option value="medium" selected>Medium - Need within 1-2 days</option>
                    <option value="high">High - Urgent, need immediately</option>
                    <option value="critical">Critical - Life-threatening situation</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description">Detailed Description *</label>
                <textarea id="description" name="description" rows="5" placeholder="Please describe your situation in detail: What happened? What do you need? Any special circumstances (elderly, children, medical conditions)?" required></textarea>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Submit Request</button>
        </form>
        
        <div class="alert alert-info" style="margin-top: 2rem;">
            <strong>🆘 Emergency Hotline:</strong> For life-threatening emergencies, call <strong>999</strong> immediately.
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>