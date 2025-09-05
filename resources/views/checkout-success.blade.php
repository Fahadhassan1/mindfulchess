<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Mindful Chess') }} - Payment Success</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: rgb(83 37 99);
            color: #333;
            line-height: 1.5;
        }
        .container {
            max-width: 768px;
            margin: 0 auto;
            padding: 3rem 1rem;
        }
        .success-card {
            background-color: white;
            border-radius: 8px;
            padding: 3rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .success-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background-color: #d1fadf;
            border-radius: 50%;
            margin: 0 auto 2rem;
        }
        .success-icon svg {
            width: 40px;
            height: 40px;
            color: #039855;
        }
        .success-title {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .success-message {
            font-size: 1.1rem;
            color: #6c757d;
            max-width: 500px;
            margin: 0 auto 2.5rem;
        }
        .order-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: left;
            margin-bottom: 2rem;
        }
        .order-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        .order-row:last-child {
            border-bottom: none;
        }
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 4px;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            text-decoration: none;
        }
        .btn-primary {
            color: #fff;
            background-color: rgb(83 37 99);
            border-color: rgb(83 37 99);
        }
        .btn-primary:hover {
            background-color: #333;
            border-color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            <h1 class="success-title">Payment Successful!</h1>
            
            <p class="success-message">
                Thank you for your payment. Your chess lesson has been booked successfully.
                A confirmation email will be sent to your email address shortly.
            </p>
            
            <div class="order-details">
                <div class="order-row">
                    <span>Payment ID</span>
                    <span>{{ $paymentId }}</span>
                </div>
                <div class="order-row">
                    <span>Customer Name</span>
                    <span>{{ $customerName }}</span>
                </div>
                <div class="order-row">
                    <span>Email</span>
                    <span>{{ $customerEmail }}</span>
                </div>
                <div class="order-row">
                    <span>Plan</span>
                    <span>{{ $planName }}</span>
                </div>
                @if(isset($sessionTypeName))
                <div class="order-row">
                    <span>Session Type</span>
                    <span>{{ $sessionTypeName }}</span>
                </div>
                @endif
                @if(isset($couponCode))
                <div class="order-row">
                    <span>Coupon Applied</span>
                    <span>{{ $couponCode }} ({{ $discountPercentage }}% off)</span>
                </div>
                <div class="order-row">
                    <span>Original Price</span>
                    <span>£{{ number_format($originalPrice, 2) }}</span>
                </div>
                @endif
                <div class="order-row">
                    <span>Total Paid</span>
                    <span>£{{ number_format($price, 2) }}</span>
                </div>
            </div>
            
            <a href="{{ url('https://www.mindfulchess.org/') }}" class="btn btn-primary">Return to Home</a>
        </div>
    </div>
</body>
</html>
