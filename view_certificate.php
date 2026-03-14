<?php
require_once '../config.php';

$certificate_id = isset($_GET['id']) ? clean_input($_GET['id']) : null;

if (!$certificate_id) {
    header("Location: certificates.php");
    exit();
}

// Get certificate details
$query = "
    SELECT vc.*, v.volunteer_name, v.email, v.phone, v.skills
    FROM volunteer_certificates vc
    JOIN volunteers v ON vc.volunteer_id = v.volunteer_id
    WHERE vc.certificate_id = '$certificate_id'
";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Certificate not found!'); window.location.href='certificates.php';</script>";
    exit();
}

$certificate = mysqli_fetch_assoc($result);

// Check if admin is logged in for back button
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Certificate - <?php echo htmlspecialchars($certificate['volunteer_name']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Georgia', serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .certificate-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 30px rgba(0,0,0,0.2);
            position: relative;
        }
        
        .certificate {
            padding: 60px 80px;
            border: 20px solid #667eea;
            position: relative;
            background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);
        }
        
        .certificate::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 2px solid #764ba2;
            pointer-events: none;
        }
        
        .certificate-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo {
            font-size: 60px;
            margin-bottom: 10px;
        }
        
        .org-name {
            font-size: 32px;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }
        
        .org-subtitle {
            font-size: 18px;
            color: #666;
            font-style: italic;
        }
        
        .certificate-title {
            text-align: center;
            margin: 30px 0;
        }
        
        .certificate-title h1 {
            font-size: 48px;
            color: #764ba2;
            letter-spacing: 4px;
            text-transform: uppercase;
            border-bottom: 3px solid #667eea;
            display: inline-block;
            padding-bottom: 10px;
        }
        
        .certificate-body {
            text-align: center;
            line-height: 2;
            font-size: 18px;
            color: #333;
            margin: 40px 0;
        }
        
        .recipient-name {
            font-size: 36px;
            color: #667eea;
            font-weight: bold;
            margin: 20px 0;
            font-family: 'Brush Script MT', cursive;
            text-decoration: underline;
            text-decoration-style: double;
        }
        
        .certificate-text {
            margin: 20px 0;
            padding: 0 50px;
        }
        
        .hours {
            font-weight: bold;
            color: #764ba2;
            font-size: 22px;
        }
        
        .certificate-footer {
            display: flex;
            justify-content: space-around;
            margin-top: 60px;
            padding-top: 20px;
        }
        
        .signature-block {
            text-align: center;
            flex: 1;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            width: 200px;
            margin: 40px auto 10px;
        }
        
        .signature-title {
            font-size: 14px;
            color: #666;
            font-weight: bold;
        }
        
        .signature-name {
            font-size: 16px;
            color: #333;
            font-style: italic;
        }
        
        .certificate-details {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #ccc;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #666;
        }
        
        .seal {
            position: absolute;
            bottom: 80px;
            left: 80px;
            width: 100px;
            height: 100px;
            border: 5px solid #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            font-size: 40px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin: 0 10px;
            display: inline-block;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .print-button {
                display: none;
            }
            
            .certificate-container {
                box-shadow: none;
                max-width: 100%;
            }
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(102, 126, 234, 0.05);
            font-weight: bold;
            pointer-events: none;
            z-index: 0;
        }
        
        .content {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()" class="btn">🖨️ Print Certificate</button>
        <?php if ($is_admin): ?>
            <a href="certificates.php" class="btn" style="background: #6c757d;">← Back to Certificates</a>
        <?php else: ?>
            <a href="../volunteers/my_certificates.php" class="btn" style="background: #6c757d;">← Back to My Certificates</a>
        <?php endif; ?>
        <a href="../index.php" class="btn" style="background: #28a745;">🏠 Homepage</a>
    </div>
    
    <div class="certificate-container">
        <div class="certificate">
            <div class="watermark">VERIFIED</div>
            
            <div class="content">
                <div class="certificate-header">
                    <div class="logo">🌊</div>
                    <div class="org-name">FLOOD RELIEF MANAGEMENT SYSTEM</div>
                    <div class="org-subtitle">Humanitarian Service Recognition</div>
                </div>
                
                <div class="certificate-title">
                    <h1>Certificate of Appreciation</h1>
                </div>
                
                <div class="certificate-body">
                    <p>This is to certify that</p>
                    
                    <div class="recipient-name">
                        <?php echo htmlspecialchars($certificate['volunteer_name']); ?>
                    </div>
                    
                    <div class="certificate-text">
                        <p>has successfully volunteered and dedicated their time and efforts towards 
                        providing relief and assistance to flood-affected communities. Through their 
                        exceptional commitment and service, they have completed</p>
                        
                        <p class="hours"><?php echo $certificate['hours_completed']; ?> HOURS</p>
                        
                        <p>of voluntary service, demonstrating outstanding dedication to humanitarian work 
                        and making a significant positive impact on the lives of those affected by natural disasters.</p>
                    </div>
                </div>
                
                <div class="certificate-footer">
                    <div class="signature-block">
                        <div class="signature-line"></div>
                        <div class="signature-title">PROGRAM COORDINATOR</div>
                        <div class="signature-name">Relief Operations</div>
                    </div>
                    
                    <div class="signature-block">
                        <div class="signature-line"></div>
                        <div class="signature-title">DIRECTOR</div>
                        <div class="signature-name">Flood Relief Management</div>
                    </div>
                </div>
                
                <div class="certificate-details">
                    <div>
                        <strong>Certificate No:</strong> <?php echo htmlspecialchars($certificate['certificate_number']); ?>
                    </div>
                    <div>
                        <strong>Issue Date:</strong> <?php echo date('F d, Y', strtotime($certificate['issue_date'])); ?>
                    </div>
                    <div>
                        <strong>Volunteer ID:</strong> #<?php echo $certificate['volunteer_id']; ?>
                    </div>
                </div>
                
                <div class="seal">
                    ✓
                </div>
            </div>
        </div>
    </div>
    
    <div class="print-button">
        <p style="text-align: center; margin-top: 20px; color: #666;">
            This is an official certificate generated by Flood Relief Management System<br>
            Valid for verification purposes | Contact: relief@floodhelp.bd
        </p>
        <p style="text-align: center; margin-top: 10px; font-size: 0.85rem; color: #999;">
            Certificate ID: <?php echo $certificate['certificate_id']; ?> | Generated: <?php echo date('Y-m-d H:i:s'); ?>
        </p>
    </div>
</body>
</html>