<?php
require_once '../config.php';
require_once '../includes/header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $victim_name = clean_input($_POST['victim_name']);
    $phone = clean_input($_POST['phone']);
    $email = clean_input($_POST['email']);
    $address = clean_input($_POST['address']);
    $family_size = clean_input($_POST['family_size']);
    
    // Check if victim already exists
    $check = mysqli_query($conn, "SELECT victim_id FROM victims WHERE phone='$phone'");
    
    if (mysqli_num_rows($check) > 0) {
        $message = show_alert("A victim with this phone number is already registered.", "error");
    } else {
        $sql = "INSERT INTO victims (victim_name, phone, email, address, family_size) 
                VALUES ('$victim_name', '$phone', '$email', '$address', '$family_size')";
        
        if (mysqli_query($conn, $sql)) {
            $victim_id = mysqli_insert_id($conn);
            $message = show_alert("Victim registered successfully! Victim ID: #$victim_id. You can now submit relief requests.", "success");
        } else {
            $message = show_alert("Error: " . mysqli_error($conn), "error");
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #667eea; margin-bottom: 2rem;">🆘 Register as Flood Victim</h2>
        
        <div class="alert alert-info">
            <strong>📢 Note:</strong> Register here if you've been affected by the flood and need assistance. After registration, you can submit specific relief requests.
        </div>
        
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="victim_name">Full Name *</label>
                <input type="text" id="victim_name" name="victim_name" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" pattern="[0-9]{11}" placeholder="01XXXXXXXXX" required>
                <small style="display: block; margin-top: 0.5rem; color: #666;">
                    This will be used to contact you. Please provide an active number.
                </small>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address (Optional)</label>
                <input type="email" id="email" name="email">
            </div>
            
            <div class="form-group">
                <label for="address">Current Address/Location *</label>
                <textarea id="address" name="address" rows="3" placeholder="Provide detailed address where you are currently staying" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="family_size">Total Family Members *</label>
                <input type="number" id="family_size" name="family_size" min="1" placeholder="Number of people in your family" required>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Register Victim</button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem;">
            Already registered? <a href="submit_request.php" style="color: #667eea; font-weight: 600;">Submit a relief request</a>
        </p>
        
        <div class="alert alert-info" style="margin-top: 2rem;">
            <strong>🆘 Emergency Hotline:</strong> For immediate life-threatening situations, call <strong>999</strong>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>