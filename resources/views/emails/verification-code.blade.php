<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .code-box {
            background-color: #f0f9ff;
            border: 2px solid #2563eb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .expiry {
            color: #dc2626;
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Email Verification</h1>
        </div>

        <div class="content">
            <p>Hello {{ $userName }},</p>
            
            <p>Thank you for registering with <strong>Manhua Platform</strong>! To complete your registration, please use the verification code below:</p>

            <div class="code-box">
                <div class="code">{{ $code }}</div>
            </div>

            <p class="expiry">⏰ This code will expire in 5 minutes</p>

            <p>Enter this code on the verification page to activate your account and start enjoying our manga collection.</p>

            <div class="warning">
                <strong>⚠️ Security Notice:</strong><br>
                If you didn't request this verification code, please ignore this email. Your account is safe.
            </div>
        </div>

        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} Manhua Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

