<?php
require_once '../plugins/EmailNotification.php';

try {
    $emailNotifier = new EmailNotification();
    $testContent = "
        <h2>Test Email Configuration</h2>
        <p>This is a test email to verify SMTP configuration.</p>
        <p>If you receive this email, your email configuration is working correctly.</p>
        <p>Sent time: " . date('Y-m-d H:i:s') . "</p>
    ";
    
    $result = $emailNotifier->sendEmail(
        'hhduong@vhe.com.vn', // Replace with your test email
        'Test Email Configuration',
        $testContent
    );
    
    if ($result) {
        echo "✅ Email sent successfully! Please check your inbox.";
    } else {
        echo "❌ Failed to send email.";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>