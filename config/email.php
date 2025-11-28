<?php
// Email configuration
// For production, use SMTP settings
// For development, you can use mail() function or a service like SendGrid, Mailgun, etc.

function sendEnrollmentConfirmationEmail($toEmail, $fullName, $applicationId) {
    $subject = "Xác nhận nộp hồ sơ tuyển sinh - Hệ thống Quản lý Giáo dục";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
            .info-box { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #2563eb; }
            .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Xác nhận nộp hồ sơ tuyển sinh</h2>
            </div>
            <div class='content'>
                <p>Xin chào <strong>{$fullName}</strong>,</p>
                <p>Cảm ơn bạn đã nộp hồ sơ tuyển sinh tại hệ thống của chúng tôi.</p>
                
                <div class='info-box'>
                    <p><strong>Mã hồ sơ:</strong> #{$applicationId}</p>
                    <p><strong>Trạng thái:</strong> Đang chờ xét duyệt</p>
                </div>
                
                <p>Hồ sơ của bạn đã được ghi nhận và đang trong quá trình xét duyệt. Chúng tôi sẽ thông báo kết quả qua email trong thời gian sớm nhất.</p>
                
                <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.</p>
                
                <p>Trân trọng,<br>Ban Quản lý Tuyển sinh</p>
            </div>
            <div class='footer'>
                <p>Email này được gửi tự động, vui lòng không trả lời.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Hệ thống Quản lý Giáo dục <noreply@edu.local>" . "\r\n";
    
    // For development, use mail() function
    // For production, configure SMTP or use a service like PHPMailer, SendGrid, etc.
    return mail($toEmail, $subject, $message, $headers);
}

