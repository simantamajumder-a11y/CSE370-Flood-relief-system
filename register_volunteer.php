<?php
require_once '../config.php';
require_once '../includes/header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $volunteer_name = clean_input($_POST['volunteer_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $availability = clean_input($_POST['availability']);
    $skills = clean_input($_POST['skills']);
    
    $sql = "INSERT INTO volunteers (volunteer_name, email, phone, availability, skills) 
            VALUES ('$volunteer_name', '$email', '$phone', '$availability', '$skills')";
    
    if (mysqli_query($conn, $sql)) {
        $volunteer_id = mysqli_insert_id($conn);
        $message = show_alert("Thank you for registering as a volunteer! Your Volunteer ID is: #$volunteer_id. We'll contact you soon with assignment details.", "success");
    } else {
        $message = show_alert("Error: " . mysqli_error($conn), "error");
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #667eea; margin-bottom: 2rem;">🤝 Join as Volunteer</h2>
        
        <div class="alert alert-info">
            <strong>💪 Make a Difference!</strong> Your time and skills can save lives. Join our volunteer team and help those affected by the flood.
        </div>
        
        <?php echo $message; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="volunteer_name">Full Name *</label>
                <input type="text" id="volunteer_name" name="volunteer_name" required>
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
                <label for="availability">Availability *</label>
                <select id="availability" name="availability" required>
                    <option value="">-- Select Availability --</option>
                    <option value="full-time">Full-time (Available all days)</option>
                    <option value="weekdays">Weekdays only</option>
                    <option value="weekends">Weekends only</option>
                    <option value="evenings">Evenings (after 5 PM)</option>
                    <option value="flexible">Flexible (As needed)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="skills">Skills & Expertise *</label>
                <textarea id="skills" name="skills" rows="4" placeholder="e.g., Medical training, Driving, Cooking, Translation, First Aid, Logistics, etc." required></textarea>
                <small style="display: block; margin-top: 0.5rem; color: #666;">
                    Please list any relevant skills that could help in relief operations
                </small>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Register as Volunteer</button>
        </form>
        
        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 5px; margin-top: 2rem;">
            <h4 style="color: #667eea; margin-bottom: 1rem;">What Volunteers Do:</h4>
            <ul style="margin-left: 1.5rem; line-height: 2;">
                <li>Distribute food, water, and essential supplies</li>
                <li>Assist in rescue and evacuation operations</li>
                <li>Provide first aid and medical support</li>
                <li>Help organize relief centers</li>
                <li>Transport supplies and people</li>
                <li>Document and track relief activities</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>