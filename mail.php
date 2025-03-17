<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Include PHPMailer library for sending email
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection details
$host = "82.180.142.204";
$user = "u954141192_ipnacademy";
$password = "x?OR+Q2/D";
$dbname = "u954141192_ipnacademy";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['message' => 'Connection failed: ' . $conn->connect_error, 'success' => false]));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['full_name']) && isset($_POST['email']) && isset($_POST['phone'])) {
    // Get form data and sanitize inputs
    $full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $city = filter_var($_POST['city'], FILTER_SANITIZE_STRING);
    $designation = filter_var($_POST['designation'], FILTER_SANITIZE_STRING);
    $school_name = filter_var($_POST['school_name'], FILTER_SANITIZE_STRING);
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');
    
    // Check if email already exists using prepared statement
    $email_check_query = "SELECT * FROM ils WHERE email = ?";
    $stmt = $conn->prepare($email_check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Email already exists in the database
        echo json_encode(['message' => 'You are already registered for the IPN Leadership Summit 2025.', 'success' => true]);
    } else {
        // Insert data using prepared statement
        $insert_sql = "INSERT INTO ils (full_name, email, phone, city, designation, school_name, created_at, updated_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssssssss", $full_name, $email, $phone, $city, $designation, $school_name, $created_at, $updated_at);
        
        if ($stmt->execute()) {
            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            
            try {
                // Store the email template
                $emailTemplate = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmation - IPN Leadership Summit 2025</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px;">
        <!-- Header -->
        <div style="text-align: center; padding-bottom: 20px;">
            <img src="https://leadership.ipnfoundation.org/black_ls.png" alt="IPN Leadership Summit Logo" style="max-width: 200px;">
        </div>

        <!-- Event Banner 
        <div style="margin-bottom: 20px;">
            <img src="https://leadership.ipnfoundation.org/banner.jpg" alt="Event Banner" style="width: 100%; border-radius: 5px;">
        </div> -->

        <!-- Content -->
        <div style="padding: 20px; color: #333333;">
            <h2 style="color: #2c5282;">Welcome, ' . htmlspecialchars($full_name) . '!</h2>
            <p>Thank you for registering for the IPN Leadership Summit 2025! We\'re excited to have you join us for this transformative event focused on School Education 5.0.</p>

            <!-- Registration Details -->
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <tr><td style="padding-bottom: 10px;">Name:</td><td><strong>' . htmlspecialchars($full_name) . '</strong></td></tr>
                <tr><td>Designation:</td><td><strong>' . htmlspecialchars($designation) . '</strong></td></tr>
                <tr><td>School/Organization:</td><td><strong>' . htmlspecialchars($school_name) . '</strong></td></tr>
                <tr><td>City:</td><td><strong>' . htmlspecialchars($city) . '</strong></td></tr>
                <tr><td>Phone:</td><td><strong>' . htmlspecialchars($phone) . '</strong></td></tr>
                <tr><td>Email:</td><td><strong>' . htmlspecialchars($email) . '</strong></td></tr>
            </table>

            <!-- Call-to-Actions -->
            <div style="text-align:center; margin-top:30px;">
                <a href="https://leadership.ipnfoundation.org/" style="display:inline-block; padding:10px 20px; background-color:#2c5282; color:#ffffff; text-decoration:none; border-radius:5px;">View Summit Details</a>
            </div>

            <!-- Closing Statement -->
            <p style="margin-top:30px;">If you have any questions or need assistance, feel free to reach out to our support team.</p>
            <a href="tel:+917753888063" style="color:#2c5282;">+91 77538 88063</a><br/><a href="mailto:team@ipnindia.in" style="color:#2c5282;">team@ipnindia.in</a>
        </div>

        <!-- Footer -->
        <div style="text-align:center; color:#666666; font-size:12px; margin-top:30px;">
            © 2025 IPN Leadership Summit. All rights reserved.<br/>
            Follow us on 
            <a href="https://www.facebook.com/IPNAcademy/" style="color:#2c5282;">Facebook</a>, 
            <a href="https://www.instagram.com/ipnacademy?igshid=NDk5N2NlZjQ%3D" style="color:#2c5282;">Instagram</a>, 
            and 
            <a href="https://www.linkedin.com/company/ipn-leadership-academy/" style="color:#2c5282;">LinkedIn</a>.
            <p>You received this email because you registered for the IPN Leadership Summit 2025. If you did not register, please disregard this email.</p>
        </div>
    </div>
</body>
</html>
';

                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ipnforum@gmail.com';
                $mail->Password = 'xmiyrxmduguqclin';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                // Set additional headers to avoid spam filters
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';
                $mail->XMailer = 'IPN Leadership Summit Mailer';
                
                // Add custom headers for better deliverability
                $mail->addCustomHeader('List-Unsubscribe', '<mailto:team@ipnindia.in?subject=Unsubscribe>');
                $mail->addCustomHeader('Precedence', 'bulk');
                $mail->addCustomHeader('X-Auto-Response-Suppress', 'OOF, AutoReply');

                // Recipients
                $mail->setFrom('ipnforum@gmail.com', 'IPN Leadership Summit');
                $mail->addReplyTo('team@ipnindia.in', 'IPN Support Team');
                $mail->addAddress($email, $full_name);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Registration Confirmation - IPN Leadership Summit 2025';
                $mail->Body = $emailTemplate;
                $mail->AltBody = 'Thank you for registering for the IPN Leadership Summit 2025! We\'re excited to have you join us. Your registration details: Name: ' . $full_name . ', Designation: ' . $designation . ', School/Organization: ' . $school_name . ', City: ' . $city . ', Phone: ' . $phone . ', Email: ' . $email;

                $mail->send();
                echo json_encode(['message' => 'Your registration is complete! Please check your inbox or spam folder for confirmation.', 'success' => true]);
            } catch (Exception $e) {
                // Log the error but still show success to user
                error_log('PHPMailer Error: ' . $e->getMessage());
                echo json_encode(['message' => 'Your registration is complete! Email delivery may be delayed.', 'success' => true]);
            }
        } else {
            echo json_encode(['message' => 'Error: ' . $stmt->error, 'success' => false]);
        }
        
        // Close statement
        $stmt->close();
    }
} else {
    echo json_encode(['message' => 'Please fill all the required fields to complete your registration.', 'success' => false]);
}

// Close connection
$conn->close();
?>
