<?php
require_once '../config.php';
require_once '../includes/header.php';

$donor_id = isset($_GET['id']) ? clean_input($_GET['id']) : null;

if (!$donor_id) {
    header("Location: view_donors.php");
    exit();
}

// Get donor details
$donor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM donors WHERE donor_id = '$donor_id'"));

if (!$donor) {
    header("Location: view_donors.php");
    exit();
}

// Get donor's donations
$donations_query = "
    SELECT * FROM donations 
    WHERE donor_id = '$donor_id' 
    ORDER BY donation_date DESC
";
$donations = mysqli_query($conn, $donations_query);

// Get statistics
$stats = [];
$stats['total_donations'] = mysqli_num_rows($donations);
$stats['total_amount'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM donations WHERE donor_id='$donor_id'"))['total'] ?? 0;
$stats['total_items'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT dt.track_id) as count FROM donation_track dt JOIN donations d ON dt.donation_id = d.donation_id WHERE d.donor_id='$donor_id'"))['count'];
?>

<div class="container">
    <div style="margin-bottom: 2rem;">
        <a href="view_donors.php" style="color: #667eea; text-decoration: none; font-size: 1rem;">← Back to All Donors</a>
    </div>
    
    <div class="form-container" style="max-width: 900px;">
        <h2 style="text-align: center; color: #667eea; margin-bottom: 2rem;">
            👤 Donor Profile
        </h2>
        
        <!-- Donor Information Card -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem; border-radius: 10px; color: white; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 2rem;">
                <div style="background: white; width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                    👤
                </div>
                <div style="flex: 1;">
                    <h2 style="margin: 0; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($donor['donor_name']); ?></h2>
                    <p style="margin: 0.25rem 0;">📧 <?php echo htmlspecialchars($donor['email']); ?></p>
                    <p style="margin: 0.25rem 0;">📞 <?php echo htmlspecialchars($donor['phone']); ?></p>
                    <p style="margin: 0.25rem 0;">📍 <?php echo htmlspecialchars($donor['address']); ?></p>
                    <p style="margin: 0.25rem 0; margin-top: 0.5rem; opacity: 0.9;">
                        Member since: <?php echo date('F d, Y', strtotime($donor['created_at'])); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>৳<?php echo number_format($stats['total_amount'], 2); ?></h3>
                    <p>Total Cash Donated</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_donations']; ?></h3>
                    <p>Total Donations</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎁</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_items']; ?></h3>
                    <p>Items Donated</p>
                </div>
            </div>
        </div>
        
        <!-- Donation History -->
        <div class="table-container">
            <h2 style="margin-bottom: 1.5rem;">💝 Donation History</h2>
            
            <?php if (mysqli_num_rows($donations) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Donated To</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($donations, 0); // Reset pointer
                        while($donation = mysqli_fetch_assoc($donations)): 
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($donation['receipt_number']); ?></strong></td>
                            <td><?php echo date('d M Y', strtotime($donation['donation_date'])); ?></td>
                            <td>
                                <span style="padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem; background: #667eea; color: white;">
                                    <?php echo htmlspecialchars($donation['donation_type']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($donation['total_amount']): ?>
                                    <strong>৳<?php echo number_format($donation['total_amount'], 2); ?></strong>
                                <?php else: ?>
                                    <em>Items Only</em>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($donation['donated_to']); ?></td>
                            <td>
                                <button onclick="showDonationDetails(<?php echo $donation['donation_id']; ?>)" class="btn btn-success" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    View Items
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Hidden details row -->
                        <tr id="details-<?php echo $donation['donation_id']; ?>" style="display: none;">
                            <td colspan="6" style="background: #f8f9fa; padding: 1.5rem;">
                                <h4 style="color: #667eea; margin-bottom: 1rem;">Items Donated:</h4>
                                <?php
                                $items = mysqli_query($conn, "SELECT * FROM donation_track WHERE donation_id='{$donation['donation_id']}'");
                                if (mysqli_num_rows($items) > 0):
                                ?>
                                    <table style="width: 100%; margin-top: 1rem;">
                                        <thead>
                                            <tr style="background: #667eea;">
                                                <th style="color: white;">Item Name</th>
                                                <th style="color: white;">Quantity</th>
                                                <th style="color: white;">Unit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($item = mysqli_fetch_assoc($items)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                                <td><strong><?php echo $item['quantity']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p>No items recorded for this donation.</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem; background: #f8f9fa; border-radius: 10px;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                    <h3 style="color: #667eea; margin-bottom: 0.5rem;">No Donations Yet</h3>
                    <p style="color: #666;">This donor hasn't made any donations yet.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Action Buttons -->
        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <a href="donate.php" class="btn" style="flex: 1; text-align: center;">
                + Add New Donation
            </a>
            <a href="view_donors.php" class="btn btn-secondary" style="flex: 1; text-align: center;">
                Back to All Donors
            </a>
        </div>
    </div>
</div>

<script>
function showDonationDetails(donationId) {
    const detailsRow = document.getElementById('details-' + donationId);
    if (detailsRow.style.display === 'none') {
        detailsRow.style.display = 'table-row';
    } else {
        detailsRow.style.display = 'none';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>