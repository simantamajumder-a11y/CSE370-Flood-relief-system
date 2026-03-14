<?php
require_once '../config.php';
require_once '../includes/header.php';

$message = '';

// Get all donors for dropdown
$donors_query = mysqli_query($conn, "SELECT donor_id, donor_name, email FROM donors ORDER BY donor_name");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $donor_id = clean_input($_POST['donor_id']);
    $donation_date = clean_input($_POST['donation_date']);
    $donation_type = clean_input($_POST['donation_type']);
    $total_amount = clean_input($_POST['total_amount']);
    $donated_to = clean_input($_POST['donated_to']);
    $receipt_number = 'RCP-' . time() . rand(100, 999);
    
    // Insert donation
    $sql = "INSERT INTO donations (donor_id, donation_date, total_amount, donation_type, receipt_number, donated_to) 
            VALUES ('$donor_id', '$donation_date', " . ($total_amount ? "'$total_amount'" : "NULL") . ", '$donation_type', '$receipt_number', '$donated_to')";
    
    if (mysqli_query($conn, $sql)) {
        $donation_id = mysqli_insert_id($conn);
        
        // If items donation, insert track details
        if ($donation_type == 'Items' && !empty($_POST['items'])) {
            foreach ($_POST['items'] as $index => $item_name) {
                $quantity = clean_input($_POST['quantities'][$index]);
                $unit = clean_input($_POST['units'][$index]);
                
                if (!empty($item_name) && !empty($quantity)) {
                    $track_sql = "INSERT INTO donation_track (donation_id, item_name, quantity, unit) 
                                  VALUES ('$donation_id', '$item_name', '$quantity', '$unit')";
                    mysqli_query($conn, $track_sql);
                }
            }
        }
        
        $message = show_alert("Donation recorded successfully! Receipt Number: $receipt_number", "success");
    } else {
        $message = show_alert("Error: " . mysqli_error($conn), "error");
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #667eea; margin-bottom: 2rem;">💝 Make a Donation</h2>
        
        <?php echo $message; ?>
        
        <form method="POST" action="" id="donationForm">
            <div class="form-group">
                <label for="donor_id">Select Donor *</label>
                <select id="donor_id" name="donor_id" required>
                    <option value="">-- Choose Donor --</option>
                    <?php while ($donor = mysqli_fetch_assoc($donors_query)): ?>
                        <option value="<?php echo $donor['donor_id']; ?>">
                            <?php echo htmlspecialchars($donor['donor_name']) . ' (' . htmlspecialchars($donor['email']) . ')'; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <small style="display: block; margin-top: 0.5rem;">
                    Not registered? <a href="add_donor.php" style="color: #667eea;">Register here</a>
                </small>
            </div>
            
            <div class="form-group">
                <label for="donation_date">Donation Date *</label>
                <input type="date" id="donation_date" name="donation_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="donation_type">Donation Type *</label>
                <select id="donation_type" name="donation_type" required onchange="toggleDonationType()">
                    <option value="">-- Select Type --</option>
                    <option value="Money">Money</option>
                    <option value="Items">Items (Food, Clothes, Medicine, etc.)</option>
                    <option value="Both">Both Money & Items</option>
                </select>
            </div>
            
            <div class="form-group" id="moneySection" style="display: none;">
                <label for="total_amount">Amount (৳) *</label>
                <input type="number" id="total_amount" name="total_amount" min="0" step="0.01" placeholder="0.00">
            </div>
            
            <div id="itemsSection" style="display: none;">
                <label style="display: block; margin-bottom: 1rem; font-weight: 600;">Donation Items:</label>
                <div id="itemsList">
                    <div class="item-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr 50px; gap: 10px; margin-bottom: 10px;">
                        <input type="text" name="items[]" placeholder="Item name (e.g., Rice)" class="form-control">
                        <input type="number" name="quantities[]" placeholder="Quantity" min="1" class="form-control">
                        <select name="units[]" class="form-control">
                            <option value="kg">kg</option>
                            <option value="pcs">pcs</option>
                            <option value="boxes">boxes</option>
                            <option value="liters">liters</option>
                        </select>
                        <button type="button" onclick="removeItem(this)" class="btn btn-secondary" style="padding: 0.5rem;">✕</button>
                    </div>
                </div>
                <button type="button" onclick="addItem()" class="btn btn-success" style="margin-top: 10px;">+ Add Item</button>
            </div>
            
            <div class="form-group">
                <label for="donated_to">Donated To *</label>
                <input type="text" id="donated_to" name="donated_to" placeholder="e.g., Flood Relief Fund, Dhaka Center" required>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Submit Donation</button>
        </form>
    </div>
</div>

<script>
function toggleDonationType() {
    const type = document.getElementById('donation_type').value;
    const moneySection = document.getElementById('moneySection');
    const itemsSection = document.getElementById('itemsSection');
    
    if (type === 'Money' || type === 'Both') {
        moneySection.style.display = 'block';
        document.getElementById('total_amount').required = true;
    } else {
        moneySection.style.display = 'none';
        document.getElementById('total_amount').required = false;
    }
    
    if (type === 'Items' || type === 'Both') {
        itemsSection.style.display = 'block';
    } else {
        itemsSection.style.display = 'none';
    }
}

function addItem() {
    const itemsList = document.getElementById('itemsList');
    const newItem = document.createElement('div');
    newItem.className = 'item-row';
    newItem.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr 50px; gap: 10px; margin-bottom: 10px;';
    newItem.innerHTML = `
        <input type="text" name="items[]" placeholder="Item name" class="form-control">
        <input type="number" name="quantities[]" placeholder="Quantity" min="1" class="form-control">
        <select name="units[]" class="form-control">
            <option value="kg">kg</option>
            <option value="pcs">pcs</option>
            <option value="boxes">boxes</option>
            <option value="liters">liters</option>
        </select>
        <button type="button" onclick="removeItem(this)" class="btn btn-secondary" style="padding: 0.5rem;">✕</button>
    `;
    itemsList.appendChild(newItem);
}

function removeItem(button) {
    button.parentElement.remove();
}
</script>

<style>
.form-control {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 1rem;
}
</style>

<?php require_once '../includes/footer.php'; ?>