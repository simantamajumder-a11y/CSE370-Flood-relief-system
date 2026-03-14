<?php
require_once '../config.php';
require_once '../includes/header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $donor_name = clean_input($_POST['donor_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $address = clean_input($_POST['address']);
    
    // Insert donor
    $sql = "INSERT INTO donors (donor_name, email, phone, address) 
            VALUES ('$donor_name', '$email', '$phone', '$address')";
    
    if (mysqli_query($conn, $sql)) {
        $message = show_alert("Donor registered successfully! You can now make donations.", "success");
    } else {
        $message = show_alert("Error: " . mysqli_error($conn), "error");
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #667eea; margin-bottom: 2rem;">👤 Register as Donor</h2>
        
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="donor_name">Full Name *</label>
                <input type="text" id="donor_name" name="donor_name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" pattern="[0-9]{11}" placeholder="01XXXXXXXXX" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address *</label>
                <textarea id="address" name="address" required></textarea>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Register Donor</button>
        </form>
        
        <p style="text-align: center; margin-top: 1rem;">
            Already registered? <a href="donate.php" style="color: #667eea;">Make a donation</a>
        </p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>