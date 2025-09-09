<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Increase Response - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #495057;
            margin-bottom: 20px;
        }
        .icon {
            font-size: 48px;
            margin: 20px 0;
        }
        .message {
            font-size: 16px;
            margin: 20px 0;
            padding: 20px;
            border-radius: 5px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .success .message {
            background-color: #d4edda;
            border-left-color: #28a745;
        }
        .error .message {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
        .contact-info {
            margin-top: 30px;
            padding: 15px;
            background-color: #e3f2fd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">{{ config('app.name') }}</div>
        
        <div class="{{ $success ? 'success' : 'error' }}">
            <div class="icon">
                @if($success)
                    ✅
                @else
                    ❌
                @endif
            </div>
            
            <h2>
                @if($success)
                    Request Processed Successfully
                @else
                    Request Could Not Be Processed
                @endif
            </h2>
            
            <div class="message">
                {{ $message }}
            </div>
        </div>
        
        @if($success && isset($teacher))
            <div class="contact-info">
                <strong>What happens next?</strong><br>
                • Our admin team has been automatically notified<br>
                • You will be contacted within 2 business days<br>
                • We'll help you find an alternative teacher at standard rates<br>
                • Your current lessons with {{ $teacher->name }} will continue until the transition
            </div>
        @endif
    
        
        <a href="{{ route('login') }}" class="btn">Go to Dashboard</a>
    </div>
</body>
</html>
