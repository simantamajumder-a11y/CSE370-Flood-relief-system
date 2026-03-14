<?php
require_once '../config.php';
require_once '../includes/header.php';

// Handle status filter
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';
$priority_filter = isset($_GET['priority']) ? clean_input($_GET['priority']) : 'all';

// Build query with filters
$where_clauses = [];
if ($status_filter != 'all') {
    $where_clauses[] = "vr.status = '$status_filter'";
}
if ($priority_filter != 'all') {
    $where_clauses[] = "vr.priority = '$priority_filter'";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

$requests_query = "
    SELECT vr.*, v.victim_name, v.phone, v.email, v.address, v.family_size
    FROM victim_requests vr
    JOIN victims v ON vr.victim_id = v.victim_id
    $where_sql
    ORDER BY 
        CASE vr.priority 
            WHEN 'critical' THEN 1 
            WHEN 'high' THEN 2 
            WHEN 'medium' THEN 3 
            ELSE 4 
        END,
        vr.request_date DESC
";
$requests = mysqli_query($conn, $requests_query);

// Get statistics
$stats = [];
$stats['total'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victim_requests"))['count'];
$stats['pending'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victim_requests WHERE status='pending'"))['count'];
$stats['in_progress'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victim_requests WHERE status='in_progress'"))['count'];
$stats['completed'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victim_requests WHERE status='completed'"))['count'];
$stats['critical'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM victim_requests WHERE priority='critical' AND status!='completed'"))['count'];
?>

<div class="container">
    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 2rem;">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Requests</p>
            </div>
        </div>
        <div class="stat-card" style="background: #fff3cd;">
            <div class="stat-icon">⏳</div>
            <div class="stat-info">
                <h3><?php echo $stats['pending']; ?></h3>
                <p>Pending</p>
            </div>
        </div>
        <div class="stat-card" style="background: #cce5ff;">
            <div class="stat-icon">🔄</div>
            <div class="stat-info">
                <h3><?php echo $stats['in_progress']; ?></h3>
                <p>In Progress</p>
            </div>
        </div>
        <div class="stat-card" style="background: #d4edda;">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <h3><?php echo $stats['completed']; ?></h3>
                <p>Completed</p>
            </div>
        </div>
        <div class="stat-card" style="background: #f8d7da;">
            <div class="stat-icon">🚨</div>
            <div class="stat-info">
                <h3><?php echo $stats['critical']; ?></h3>
                <p>Critical Active</p>
            </div>
        </div>
    </div>
    
    <div class="table-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>🆘 All Relief Requests</h2>
            <a href="submit_request.php" class="btn">+ Submit New Request</a>
        </div>
        
        <!-- Filters -->
        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; background: #f8f9fa; padding: 1rem; border-radius: 5px;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Filter by Status:</label>
                <select onchange="window.location.href='?status=' + this.value + '&priority=<?php echo $priority_filter; ?>'" style="width: 100%; padding: 0.5rem; border-radius: 5px; border: 2px solid #e0e0e0;">
                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Filter by Priority:</label>
                <select onchange="window.location.href='?status=<?php echo $status_filter; ?>&priority=' + this.value" style="width: 100%; padding: 0.5rem; border-radius: 5px; border: 2px solid #e0e0e0;">
                    <option value="all" <?php echo $priority_filter == 'all' ? 'selected' : ''; ?>>All Priorities</option>
                    <option value="critical" <?php echo $priority_filter == 'critical' ? 'selected' : ''; ?>>Critical</option>
                    <option value="high" <?php echo $priority_filter == 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="medium" <?php echo $priority_filter == 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="low" <?php echo $priority_filter == 'low' ? 'selected' : ''; ?>>Low</option>
                </select>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Victim Name</th>
                    <th>Contact</th>
                    <th>Request Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Family Size</th>
                    <th>Request Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($requests) > 0): ?>
                    <?php while($req = mysqli_fetch_assoc($requests)): ?>
                    <tr style="background: <?php 
                        echo $req['priority'] == 'critical' ? '#ffebee' : 
                             ($req['priority'] == 'high' ? '#fff3e0' : 'white'); 
                    ?>">
                        <td><strong>#<?php echo $req['request_id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($req['victim_name']); ?></td>
                        <td>
                            📞 <?php echo htmlspecialchars($req['phone']); ?><br>
                            <?php if($req['email']): ?>
                                📧 <?php echo htmlspecialchars($req['email']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($req['request_type']); ?></td>
                        <td>
                            <span style="padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem; font-weight: 600; background: <?php 
                                echo $req['priority'] == 'critical' ? '#dc3545' : 
                                     ($req['priority'] == 'high' ? '#fd7e14' : 
                                     ($req['priority'] == 'medium' ? '#ffc107' : '#6c757d')); 
                            ?>; color: white;">
                                <?php echo strtoupper($req['priority']); ?>
                            </span>
                        </td>
                        <td>
                            <span style="padding: 0.3rem 0.8rem; border-radius: 15px; font-size: 0.85rem; background: <?php 
                                echo $req['status'] == 'completed' ? '#28a745' : 
                                     ($req['status'] == 'in_progress' ? '#007bff' : 
                                     ($req['status'] == 'rejected' ? '#dc3545' : '#ffc107')); 
                            ?>; color: white;">
                                <?php echo ucfirst(str_replace('_', ' ', $req['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo $req['family_size']; ?> members</td>
                        <td><?php echo date('d M Y, h:i A', strtotime($req['request_date'])); ?></td>
                        <td>
                            <a href="request_details.php?id=<?php echo $req['request_id']; ?>" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View Details</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2rem;">
                            No requests found matching the selected filters.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>