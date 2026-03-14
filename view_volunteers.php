<?php
require_once '../config.php';
require_once '../includes/header.php';

// Get all volunteers with their task count
$volunteers_query = "
    SELECT v.*, 
           COUNT(DISTINCT vr.volunteer_request_id) as tasks_completed
    FROM volunteers v
    LEFT JOIN volunteer_requests vr ON v.volunteer_id = vr.volunteer_id AND vr.completion_status = 'completed'
    GROUP BY v.volunteer_id
    ORDER BY v.created_at DESC
";
$volunteers = mysqli_query($conn, $volunteers_query);

// Get statistics
$stats = [];
$stats['total'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteers"))['count'];
$stats['active'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteers WHERE status='active'"))['count'];
$stats['inactive'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteers WHERE status='inactive'"))['count'];

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $volunteer_id = clean_input($_POST['volunteer_id']);
    $new_status = clean_input($_POST['new_status']);
    mysqli_query($conn, "UPDATE volunteers SET status='$new_status' WHERE volunteer_id='$volunteer_id'");
    header("Location: view_volunteers.php");
    exit();
}
?>

<div class="container">
    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon">🤝</div>
            <div class="stat-info">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Volunteers</p>
            </div>
        </div>
        <div class="stat-card" style="background: #d4edda;">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo $stats['active']; ?></h3>
                <p>Active Volunteers</p>
            </div>
        </div>
        <div class="stat-card" style="background: #f8d7da;">
            <div class="stat-icon">⏸️</div>
            <div class="stat-info">
                <h3><?php echo $stats['inactive']; ?></h3>
                <p>Inactive Volunteers</p>
            </div>
        </div>
    </div>
    
    <div class="table-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>🤝 All Volunteers</h2>
            <a href="register_volunteer.php" class="btn">+ Register New Volunteer</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Availability</th>
                    <th>Skills</th>
                    <th>Tasks Completed</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($volunteers) > 0): ?>
                    <?php while($vol = mysqli_fetch_assoc($volunteers)): ?>
                    <tr>
                        <td><strong>#<?php echo $vol['volunteer_id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($vol['volunteer_name']); ?></td>
                        <td>
                            📞 <?php echo htmlspecialchars($vol['phone']); ?><br>
                            📧 <?php echo htmlspecialchars($vol['email']); ?>
                        </td>
                        <td>
                            <span style="padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem; background: #667eea; color: white;">
                                <?php echo ucfirst(str_replace('-', ' ', $vol['availability'])); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars(substr($vol['skills'], 0, 50)) . (strlen($vol['skills']) > 50 ? '...' : ''); ?></td>
                        <td><?php echo $vol['tasks_completed']; ?> tasks</td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="volunteer_id" value="<?php echo $vol['volunteer_id']; ?>">
                                <input type="hidden" name="new_status" value="<?php echo $vol['status'] == 'active' ? 'inactive' : 'active'; ?>">
                                <button type="submit" name="toggle_status" style="padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem; border: none; cursor: pointer; background: <?php echo $vol['status'] == 'active' ? '#28a745' : '#dc3545'; ?>; color: white;">
                                    <?php echo ucfirst($vol['status']); ?>
                                </button>
                            </form>
                        </td>
                        <td><?php echo date('d M Y', strtotime($vol['created_at'])); ?></td>
                        <td>
                            <a href="assign_task.php?id=<?php echo $vol['volunteer_id']; ?>" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Assign Task</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2rem;">
                            No volunteers registered yet. Encourage people to volunteer!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Volunteer Skills Overview -->
    <div class="cards" style="margin-top: 2rem;">
        <div class="card">
            <div class="card-icon">🏥</div>
            <h3>Medical Skills</h3>
            <?php
            $medical = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteers WHERE skills LIKE '%medical%' OR skills LIKE '%first aid%'"))['count'];
            ?>
            <p><?php echo $medical; ?> volunteers with medical expertise</p>
        </div>
        <div class="card">
            <div class="card-icon">🚚</div>
            <h3>Logistics & Transport</h3>
            <?php
            $logistics = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteers WHERE skills LIKE '%driving%' OR skills LIKE '%transport%' OR skills LIKE '%logistics%'"))['count'];
            ?>
            <p><?php echo $logistics; ?> volunteers with logistics skills</p>
        </div>
        <div class="card">
            <div class="card-icon">🍲</div>
            <h3>Food Distribution</h3>
            <?php
            $food = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteers WHERE skills LIKE '%food%' OR skills LIKE '%cooking%'"))['count'];
            ?>
            <p><?php echo $food; ?> volunteers for food services</p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>