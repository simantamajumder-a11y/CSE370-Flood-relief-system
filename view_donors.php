<?php
require_once '../config.php';
require_once '../includes/header.php';

// Get all donors with their donation count
$donors_query = "
    SELECT d.*, 
           COUNT(DISTINCT don.donation_id) as donation_count,
           SUM(don.total_amount) as total_donated
    FROM donors d
    LEFT JOIN donations don ON d.donor_id = don.donor_id
    GROUP BY d.donor_id
    ORDER BY d.created_at DESC
";
$donors = mysqli_query($conn, $donors_query);
?>

<div class="container">
    <div class="table-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>👥 All Registered Donors</h2>
            <a href="add_donor.php" class="btn">+ Add New Donor</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Total Donations</th>
                    <th>Amount Donated</th>
                    <th>Registered On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($donors) > 0): ?>
                    <?php while($donor = mysqli_fetch_assoc($donors)): ?>
                    <tr>
                        <td>#<?php echo $donor['donor_id']; ?></td>
                        <td><?php echo htmlspecialchars($donor['donor_name']); ?></td>
                        <td><?php echo htmlspecialchars($donor['email']); ?></td>
                        <td><?php echo htmlspecialchars($donor['phone']); ?></td>
                        <td><?php echo htmlspecialchars(substr($donor['address'], 0, 30)) . '...'; ?></td>
                        <td><?php echo $donor['donation_count']; ?> times</td>
                        <td>৳<?php echo number_format($donor['total_donated'] ?? 0, 2); ?></td>
                        <td><?php echo date('d M Y', strtotime($donor['created_at'])); ?></td>
                        <td>
                            <a href="donor_details.php?id=<?php echo $donor['donor_id']; ?>" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View Details</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2rem;">
                            No donors registered yet. Be the first to register!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid" style="margin-top: 2rem;">
        <?php
        $total_donors = mysqli_num_rows($donors);
        mysqli_data_seek($donors, 0); // Reset pointer
        $active_donors = 0;
        $total_amount = 0;
        while($d = mysqli_fetch_assoc($donors)) {
            if($d['donation_count'] > 0) $active_donors++;
            $total_amount += $d['total_donated'] ?? 0;
        }
        ?>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3><?php echo $total_donors; ?></h3>
                <p>Total Donors</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo $active_donors; ?></h3>
                <p>Active Donors</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>৳<?php echo number_format($total_amount, 2); ?></h3>
                <p>Total Amount Donated</p>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>