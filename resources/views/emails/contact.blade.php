<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Form Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f8f8;
            padding: 20px;
            margin-bottom: 20px;
            border-bottom: 3px solid #e53e3e;
        }
        .message-content {
            background-color: #f9fafb;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 3px solid #e53e3e;
        }
        .contact-info {
            background-color: #f8f8f8;
            padding: 15px;
            border-radius: 4px;
        }
        h1 {
            color: #e53e3e;
            margin-top: 0;
        }
        h2 {
            font-size: 18px;
            margin-top: 0;
            color: #4a5568;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #718096;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Contact Form Submission</h1>
        <p>{{ now()->format('F j, Y, g:i a') }}</p>
    </div>
    
    <div class="message-content">
        <h2>Message:</h2>
        <p>{{ $userMessage }}</p>
    </div>
    
    <div class="contact-info">
        <h2>Contact Information:</h2>
        <p><strong>Name:</strong> {{ $name }}</p>
        <p><strong>Email:</strong> {{ $email }}</p>
        <p><strong>Phone:</strong> {{ $phone }}</p>
    </div>
    
    <div class="footer">
        <p>This is an automated message from the {{ env('COMPANY_NAME') }} website contact form.</p>
    </div>
</body>
</html>