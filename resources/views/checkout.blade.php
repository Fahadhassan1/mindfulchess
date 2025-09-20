<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Mindful Chess') }} - Checkout</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    
    <!-- Scripts -->
    <script src="https://js.stripe.com/v3/"></script>
    
    <!-- Stripe Elements CSS -->
    <style>
        .StripeElement {
            background-color: white;
            padding: 14px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            box-shadow: 0 1px 3px 0 #e6ebf1;
            -webkit-transition: box-shadow 150ms ease;
            transition: box-shadow 150ms ease;
        }

        .StripeElement--focus {
            box-shadow: 0 1px 3px 0 #cfd7df;
        }

        .StripeElement--invalid {
            border-color: #fa755a;
        }

        .StripeElement--webkit-autofill {
            background-color: #fefde5 !important;
        }
        
        #card-errors {
            color: #721c24;
            margin-top: 10px;
            font-size: 14px;
        }
        
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border: 3px solid rgba(0, 0, 0, 0.3);
            border-radius: 50%;
            border-top-color: #000;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to {
                -webkit-transform: rotate(360deg);
            }
        }
    </style>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f8fa;
            color: #333;
            line-height: 1.5;
        }
        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        @media (min-width: 768px) {
            .checkout-grid {
                grid-template-columns: 3fr 2fr;
            }
        }
        .checkout-form {
            background-color: white;
            border-radius: 8px;
            padding: 2.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .section-badge {
            position: absolute;
            top: -12px;
            left: 2rem;
            background: #000;
            color: white;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        .order-summary {
            background-color: #e3e3e3;
            height: fit-content;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 2rem;
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            /* border-bottom: 2px solid #000000; */
            padding-bottom: 0.5rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        .form-control {
            display: block;
            width: 100%;
            padding: 0.75rem 0rem 1rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #000000;
            border-radius: 4px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            cursor: pointer;
        }
        
        /* Specific styling for date inputs to ensure full clickable area */
        .form-control[type="date"] {
            /* padding: 0.75rem; */
            position: relative;
            cursor: pointer;
        }
        
        /* Ensure the date picker icon is properly positioned and clickable */
        .form-control[type="date"]::-webkit-calendar-picker-indicator {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: auto;
            height: auto;
            color: transparent;
            background: transparent;
            cursor: pointer;
        }
        
        /* For Firefox date inputs */
        .form-control[type="date"]::-moz-focus-inner {
            border: 0;
            padding: 0;
        }
        .form-control:focus {
            color: #495057;
            background-color: #fff;
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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
            cursor: pointer;
            width: 100%;
        }
        .btn-primary {
            color: #fff;
            background-color: #000;
            border-color: #000;
        }
        .btn-primary:hover {
            background-color: #333;
            border-color: #333;
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
            color: #6c757d;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #000000;
        }
        .divider::before {
            margin-right: 1rem;
        }
        .divider::after {
            margin-left: 1rem;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            border-top: 1px solid #000000;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        .text-muted {
            color: #6c757d;
            font-size: 0.875rem;
        }
        .secure-checkout {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1rem;
            color: #6c757d;
            font-size: 0.875rem;
        }
        .secure-checkout svg {
            margin-right: 0.5rem;
            width: 1rem;
            height: 1rem;
        }
        .duration-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .duration-option {
            padding: 1rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .duration-option.active {
            border-color: #000;
            background-color: #f8f9fa;
        }
        .duration-option-time {
            font-weight: 600;
            font-size: 1rem;
        }
        .duration-option-price {
            color: #6c757d;
            font-size: 0.875rem;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        /* Modern Order Summary Styles */
        .summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #000000;
        }
        
        .summary-header .section-title {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .item-count {
            color: #6c757d;
            font-weight: 400;
        }
        
        .edit-cart-link {
            color: #007bff;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .edit-cart-link:hover {
            text-decoration: underline;
        }
        
        .line-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #000000;
            margin-bottom: 1.5rem;
        }
        
        .item-image {
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .item-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .title-price-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .item-name {
            font-weight: 500;
            color: #333;
            flex: 1;
        }
        
        .item-price {
            font-weight: 600;
            color: #333;
            flex-shrink: 0;
        }
        
        .item-quantity {
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .coupon-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #000000;
        }
        
        .promo-code-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            color: #007bff;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            padding: 0;
            text-decoration: underline
        }
        
        .promo-code-btn:hover {
            text-decoration: underline;
        }
        
        .promo-code-btn svg {
            width: 16px;
            height: 16px;
        }
        
        .totals-section {
            margin-bottom: 1.5rem;
        }
        
        .total-breakdown {
            margin-bottom: 1rem;
        }
        
        .subtotal-row,
        .delivery-row,
        .vat-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            color: #333;
            font-size: 0.875rem;
        }
        
        .final-total {
            padding-top: 1rem;
        }
        
        .final-total .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .total-label,
        .total-amount {
            font-size: 1.125rem;
            font-weight: 700;
            color: #333;
        }
        
        .secure-checkout-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            color: #6c757d;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .secure-checkout-footer svg {
            flex-shrink: 0;
        }
        
        /* Radio Button Styles */
        .radio-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .radio-option {
            position: relative;
        }
        
        .radio-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            cursor: pointer;
        }
        
        .radio-label {
            display: block;
            padding: 0.875rem 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            background: white;
            color: #495057;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 0;
        }
        
        .radio-option input[type="radio"]:checked + .radio-label {
            border-color: #532563;
            background: rgba(83, 37, 99, 0.05);
            color: #532563;
        }
        
        .radio-option input[type="radio"]:focus + .radio-label {
            box-shadow: 0 0 0 3px rgba(83, 37, 99, 0.1);
        }
        
        .radio-label:hover {
            border-color: #532563;
            background: rgba(83, 37, 99, 0.02);
        }
        
        /* Member Info Box Styles */
        .member-info-box {
            margin-bottom: 2rem;
        }
        
        .member-info-card {
            background: #d8d9d9;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            text-align: left;
        }
        
        .member-info-text {
            color: #495057;
            font-size: 0.875rem;
            font-weight: 400;
        }
        
        .login-link-btn {
            background: none;
            border: none;
            color: #000000;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: underline;
            cursor: pointer;
            padding: 0;
            margin-left: 0.25rem;
        }
        
        .login-link-btn:hover {
            color: #0056b3;
            text-decoration: none;
        }
        
        .login-link-btn:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
            border-radius: 2px;
        }
        
        /* Navigation Bar Styles */
        .checkout-navbar {
            background: white;
            border-bottom: 1px solid #000000;
            padding: 1rem 0 0 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

        }
        
        .navbar-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        
        .brand-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: inherit;
        }
        
        .brand-logo {
            width: 56px;
            height: 56px;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }
        
        .brand-logo:hover {
            transform: scale(1.05);
        }
        
        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
        }
        
        .brand-title {
            /* font-size: 1.75rem; */
            color: #000000;
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .nav-link {
            color: #000000;
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            text-decoration: underline;
            font-size: medium;
            

        }
    
        
        /* Responsive navbar adjustments */
        @media (max-width: 768px) {
            .navbar-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .brand-link {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .brand-title {
                font-size: 1.5rem;
            }
            
            .navbar-nav {
                width: 100%;
                justify-content: center;
            }
            
            .nav-link {
                width: auto;
                min-width: 150px;
            }
        }
        
        /* Remove old header styles */
        .text-black {
            color: black !important;
        }
        
        /* Footer Styles */
        .checkout-footer {
            background: #f8f9fa;
            border-top: 1px solid #000000;
            padding: 2rem 0 1rem;
            margin-top: 3rem;
        }
        
        .footer-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 1rem;
            text-align: center;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .footer-link {
            color: #000000;
            text-decoration: underline;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.5rem;
            border-radius: 4px;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .footer-link:hover {
            color: #000000;
            text-decoration: underline;
            transform: translateY(-1px);
        }
        

        

        
        .footer-link:hover::after {
            width: 100%;
        }
        
        .footer-copyright {
            border-top: 1px solid #e9ecef;
            padding-top: 1rem;
        }
        
        .footer-copyright p {
            margin: 0;
            color: #6c757d;
            font-size: 0.8rem;
            font-weight: 400;
        }
        
        /* Responsive footer adjustments */
        @media (max-width: 768px) {
            .footer-links {
                flex-direction: column;
                gap: 1rem;
            }
            
            .footer-link {
                font-size: 0.9rem;
                padding: 0.75rem;
            }
            
            .checkout-footer {
                padding: 1.5rem 0 1rem;
            }
        }
        
        @media (max-width: 480px) {
            .footer-links {
                gap: 0.75rem;
            }
            
            .footer-link {
                font-size: 0.85rem;
                padding: 0.5rem;
            }
        }
        
        /* Time Slots Styling */
        .preferred-date-option {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
        }
        
        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .time-slot {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }
        
        .time-slot:hover {
            border-color: #000;
            background-color: #f5f5f5;
        }
        
        .time-slot.selected {
            background-color: #000;
            color: white;
            border-color: #000;
        }
        
        .time-slot.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #f5f5f5;
        }
        
        .mb-3 {
            margin-bottom: 15px;
        }
        
        .text-sm {
            font-size: 0.875rem;
        }
        
        .text-gray-600 {
            color: #6b7280;
        }
        
        .btn-outline-primary {
            background-color: transparent;
            border: 1px solid #000;
            color: #000;
        }
        
        .btn-outline-primary:hover {
            background-color: #f5f5f5;
        }
        
        .btn-outline-danger {
            background-color: transparent;
            border: 1px solid #dc3545;
            color: #dc3545;
            margin-top: 10px;
        }
        
        .btn-outline-danger:hover {
            background-color: #fff5f5;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="checkout-navbar">
        <div class="navbar-container">
            <div class="navbar-content">
                <div class="navbar-brand">
                    <a href="{{ url('/') }}" class="brand-link">
                        <div class="brand-logo">
                            <img src="{{ asset('images/mindfulchess.png') }}" 
                                 alt="Mindful Chess logo, when clicked will direct to the homepage" 
                                 width="56" height="56">
                        </div>
                        <h4 class="brand-title">CHECKOUT</h4>
                    </a>
                </div>
                <div class="navbar-nav">
                    <a href="{{ url('https://www.mindfulchess.org/') }}" class="nav-link">
                        Continue Browsing
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="checkout-grid">
            <div class="checkout-form">
                <form method="POST" action="{{ route('checkout.process') }}" id="payment-form">
                    @csrf
                    <input type="hidden" name="duration" value="{{ $duration }}">
                    <input type="hidden" name="payment_method" id="payment-method" value="">
                    
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <!-- Member Info Box -->
                    <section class="member-info-box">
                        <div class="member-info-card">
                            <span class="member-info-text">
                                Have an account? 
                                <a href="{{ route('login') }}" class="login-link-btn" data-hook="login-button">
                                    Log in
                                </a>
                            </span>
                        </div>
                    </section>
                    <div style="margin-bottom: 2rem;">
                         <h1 class="section-title">Choose Your Session times</h1>
                        <!-- Section for Calendar Availability -->
                        <div class="form-group" style="margin-top: 2rem;">
                            <label class="form-label">Preferred Session Times *</label>
                            <p class="text-sm text-gray-600 mb-3">Please select dates and time slots when you would be available for your lesson. You can select multiple slots.</p>
                            
                            <div id="preferred-dates-container">
                                <div class="preferred-date-option">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" class="form-control preferred-date" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-label">Available Time Slots (select multiple)</label>
                                        <div class="time-slots-grid" id="time-slots-container-0"></div>
                                    </div>
                                    
                                    <button type="button" class="remove-date-option btn btn-outline-danger mb-3" style="display:none;">Remove Date</button>
                                </div>
                            </div>
                            
                            <button type="button" id="add-date-option" class="btn btn-outline-primary">+ Add Another Date</button>
                            
                            <input type="hidden" name="preferred_times" id="preferred-times-input" required>
                        </div>
                    </div>
                    
                    <div class="divider">Customer Information</div>
                    <!-- Section 1: Customer Details -->
                    <div style="margin-bottom: 2rem;">
                        <h1 class="section-title">Customer Details</h1>
                        
                        <div class="form-group">
                            <label class="form-label" for="email">Email *</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="Enter your email address">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="card_holder">Full Name *</label>
                            <input type="text" name="card_holder" id="card_holder" class="form-control" required placeholder="Enter your full name">
                        </div>
                        
                   
                        
                        <div class="form-group">
                            <label class="form-label" for="phone_number">Phone Number *</label>
                            <input type="text" name="phone_number" id="phone_number" class="form-control" required placeholder="Enter your phone number">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Coaching For *</label>
                            <div class="radio-group">
                              
                                <div class="radio-option">
                                    <input type="radio" name="session_type" id="session_adult" value="adult" 
                                           {{ $sessionType == 'adult' ? 'checked' : '' }} required>
                                    <label for="session_adult" class="radio-label">Adults</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="session_type" id="session_kids" value="kids" 
                                           {{ $sessionType == 'kids' ? 'checked' : '' }} required>
                                    <label for="session_kids" class="radio-label">Kids</label>
                                </div>
                             
                            </div>
                        </div>
                    </div>
                    
                    <div class="divider">Payment Information</div>
                    
                    <!-- Section 2: Payment Method -->
                    <div>
                        <h1 class="section-title">Payment</h1>
                        
                        <div class="form-group">
                            <label class="form-label" for="card-element">Credit or debit card *</label>
                            <div id="card-element" class="form-control"></div>
                            <div id="card-errors" role="alert"></div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <p class="text-muted mb-4">By completing the purchase, you agree to make a one-time payment for your chess lesson.</p>
                        
                        <button type="submit" class="btn btn-primary" id="submit-button">
                            <div class="spinner" id="spinner"></div>
                            <span id="button-text">Complete Payment</span>
                        </button>
                    </div>
                </form>
                
    
                
                <div class="secure-checkout">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Secure Checkout
                </div>
            </div>
            
            <div class="order-summary">
                <div class="summary-header">
                    <h2 class="section-title">Order summary <span class="item-count">(1)</span></h2>
                </div>
                
                <!-- Line Item -->
                <div class="line-item">
                    <div class="item-image">
                        <img src="{{ asset('images/chessicon.png') }}" alt="{{ $name }}" width="60" height="60">
                    </div>
                    <div class="item-details">
                        <div class="title-price-row">
                            <div class="item-name">{{ $name }}</div>
                            <div class="item-price">£{{ number_format($price, 2) }}</div>
                        </div>
                        <div class="item-quantity">Qty: 1</div>
                    </div>
                </div>
                
                <!-- Coupon Section -->
                <div class="coupon-section">
                    <button type="button" class="promo-code-btn text-black" id="promo-toggle">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                            <path fill-rule="evenodd" d="M14.5,3.9997 C15.327,3.9997 16,4.6727 16,5.4997 L16,5.4997 L16,9.6577 C16,10.1847 15.787,10.6997 15.414,11.0727 L15.414,11.0727 L9.925,16.5607 C9.643,16.8437 9.266,16.9997 8.865,16.9997 C8.464,16.9997 8.087,16.8437 7.804,16.5607 L7.804,16.5607 L3.439,12.1957 C2.854,11.6107 2.854,10.6597 3.439,10.0747 L3.439,10.0747 L8.928,4.5857 C9.306,4.2077 9.808,3.9997 10.342,3.9997 L10.342,3.9997 Z M14.5,4.9997 L10.342,4.9997 C10.075,4.9997 9.824,5.1037 9.635,5.2927 L9.635,5.2927 L4.146,10.7817 C3.952,10.9767 3.952,11.2937 4.146,11.4887 L4.146,11.4887 L8.511,15.8537 C8.701,16.0427 9.031,16.0427 9.218,15.8537 L9.218,15.8537 L14.707,10.3657 C14.893,10.1787 15,9.9207 15,9.6577 L15,9.6577 L15,5.4997 C15,5.2237 14.776,4.9997 14.5,4.9997 L14.5,4.9997 Z M11.293,7.293 C11.684,6.902 12.316,6.902 12.707,7.293 C13.098,7.684 13.098,8.316 12.707,8.707 C12.316,9.098 11.684,9.098 11.293,8.707 C10.902,8.316 10.902,7.684 11.293,7.293 Z"></path>
                        </svg>
                        Enter a promo code
                    </button>
                    <div class="promo-input-section" id="promo-input" style="display: none;">
                        <div style="display: flex; gap: 10px; margin-top: 1rem;">
                            <input type="text" name="coupon_code" id="coupon_code" class="form-control" placeholder="Enter promo code" style="flex: 1;">
                            <button type="button" id="apply-coupon" class="btn btn-primary" style="width: auto; padding: 0.75rem 1rem;">Apply</button>
                        </div>
                        <div id="coupon-message" style="font-size: 0.875rem; margin-top: 0.5rem;"></div>
                    </div>
                </div>
                
                <!-- Totals Breakdown -->
                <div class="totals-section">
                    <div class="total-breakdown">
                        <div class="subtotal-row">
                            <span>Subtotal</span>
                            <span>£{{ number_format($price, 2) }}</span>
                        </div>
                        <div class="delivery-row">
                            <span>Delivery</span>
                            <span>Free</span>
                        </div>
                        <div class="vat-row">
                            <span>VAT</span>
                            <span>£0.00</span>
                        </div>
                    </div>
                    <div class="final-total">
                        <div class="total-row">
                            <span class="total-label">Total</span>
                            <span class="total-amount" id="total-price">£{{ number_format($price, 2) }}</span>
                        </div>
                    </div>
                </div>
                
        
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="checkout-footer">
        <div class="footer-container">
            <div class="footer-links">
                <a href="{{ url('/terms-and-conditions') }}" class="footer-link">
                    Terms & Conditions
                </a>
                <a href="{{ url('https://www.mindfulchess.org/contact-us') }}" class="footer-link">
                    Contact Us
                </a>
            </div>
            <div class="footer-copyright">
                <p>&copy; {{ date('Y') }} Mindful Chess. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
<script>
            // Create a Stripe client.
            const stripe = Stripe('{{ config('services.stripe.key') }}');
            
            // Create an instance of Elements.
            const elements = stripe.elements();
            
            // Create a card Element and mount it to the div
            const style = {
                base: {
                    color: '#495057',
                    fontFamily: '"Inter", sans-serif',
                    fontSize: '16px',
                    fontWeight: '400',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            };
            
            const cardElement = elements.create('card', {style: style});
            cardElement.mount('#card-element');
            
            // Handle form submission
            const form = document.getElementById('payment-form');
            const cardErrors = document.getElementById('card-errors');
            const submitButton = document.getElementById('submit-button');
            const spinner = document.getElementById('spinner');
            const buttonText = document.getElementById('button-text');
            
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                
                // Validate preferred times selection
                const preferredTimesInput = document.getElementById('preferred-times-input').value;
                
                try {
                    const preferredTimes = JSON.parse(preferredTimesInput || '[]');
                    if (!preferredTimes.length) {
                        alert('Please select at least one date and time slot for your lesson.');
                        return;
                    }
                    
                    // Check if any date option has a date but no time slots selected
                    const dateOptions = document.querySelectorAll('.preferred-date-option');
                    let hasIncompleteOption = false;
                    
                    dateOptions.forEach((option, index) => {
                        const date = option.querySelector('.preferred-date').value;
                        if (date) {
                            const timeSlotContainer = document.getElementById(`time-slots-container-${index}`);
                            const selectedSlots = timeSlotContainer.querySelectorAll('.time-slot.selected');
                            if (selectedSlots.length === 0) {
                                hasIncompleteOption = true;
                            }
                        }
                    });
                    
                    if (hasIncompleteOption) {
                        alert('Please select at least one time slot for each date, or remove dates without selections.');
                        return;
                    }
                } catch (e) {
                    alert('Please select at least one date and time slot for your lesson.');
                    return;
                }
                
                // Disable the submit button to prevent repeated clicks
                submitButton.disabled = true;
                spinner.style.display = 'inline-block';
                buttonText.textContent = 'Processing...';
                
                // Create a payment method
                const result = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                    billing_details: {
                        name: document.getElementById('card_holder').value,
                        email: document.getElementById('email').value
                    }
                });
                
                if (result.error) {
                    // Show error to the customer
                    cardErrors.textContent = result.error.message;
                    submitButton.disabled = false;
                    spinner.style.display = 'none';
                    buttonText.textContent = 'Buy Now';
                } else {
                    // Set the payment method ID in the hidden input
                    document.getElementById('payment-method').value = result.paymentMethod.id;
                    
                    // Submit the form
                    form.submit();
                }
            });
            
            // Session type functionality
            const sessionTypeRadios = document.querySelectorAll('input[name="session_type"]');
            sessionTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        // Update the duration option links with the new session type
                        const sessionType = this.value;
                        const durationLinks = document.querySelectorAll('.duration-options a');
                        
                        durationLinks.forEach(link => {
                            // Get current duration from the link
                            const url = new URL(link.href);
                            const duration = url.searchParams.get('duration');
                            
                            // Update the link with new session type
                            url.searchParams.set('session_type', sessionType);
                            link.href = url.toString();
                        });
                    }
                });
            });
            
            // Promo code toggle functionality
            const promoToggle = document.getElementById('promo-toggle');
            const promoInput = document.getElementById('promo-input');
            
            promoToggle.addEventListener('click', function() {
                if (promoInput.style.display === 'none') {
                    promoInput.style.display = 'block';
                    promoToggle.setAttribute('aria-expanded', 'true');
                } else {
                    promoInput.style.display = 'none';
                    promoToggle.setAttribute('aria-expanded', 'false');
                }
            });
            
            // Coupon code functionality
            const applyCouponBtn = document.getElementById('apply-coupon');
            const couponInput = document.getElementById('coupon_code');
            const couponMessage = document.getElementById('coupon-message');
            const totalPriceElement = document.getElementById('total-price');
            let originalPrice = {{ $price }};
            
            applyCouponBtn.addEventListener('click', async () => {
                const couponCode = couponInput.value.trim();
                
                if (!couponCode) {
                    couponMessage.textContent = 'Please enter a coupon code';
                    couponMessage.style.color = '#721c24';
                    return;
                }
                
                // Disable the button during API call
                applyCouponBtn.disabled = true;
                applyCouponBtn.textContent = 'Applying...';
                
                try {
                    // Send the coupon code to the server to validate
                    const response = await fetch('{{ route("coupon.validate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ coupon_code: couponCode })
                    });
                    
                    const result = await response.json();
                    
                    if (result.valid) {
                // Update the price display with discount applied
                const discountedPrice = originalPrice - (originalPrice * (result.discount_percentage / 100));
                totalPriceElement.textContent = `£${discountedPrice.toFixed(2)}`;
                
                couponMessage.textContent = result.message || 'Coupon applied successfully!';
                couponMessage.style.color = '#28a745';                        // Add a hidden input to the form to include the coupon code in submission
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'applied_coupon';
                        hiddenInput.value = couponCode;
                        document.getElementById('payment-form').appendChild(hiddenInput);
                    } else {
                        couponMessage.textContent = result.message || 'Invalid coupon code';
                        couponMessage.style.color = '#721c24';
                        
                        // Reset the price to the original amount
                        totalPriceElement.textContent = `£${originalPrice.toFixed(2)}`;
                    }
                } catch (error) {
                    console.error('Error validating coupon:', error);
                    couponMessage.textContent = 'Error validating coupon. Please try again.';
                    couponMessage.style.color = '#721c24';
                }
                
                                // Re-enable the button after API call
                applyCouponBtn.disabled = false;
                applyCouponBtn.textContent = 'Apply';
            });
            
            // Login button functionality
            const loginBtn = document.querySelector('[data-hook="login-button"]');
            if (loginBtn) {
                loginBtn.addEventListener('click', function() {
                    // Redirect to login page with return URL
                    const currentUrl = encodeURIComponent(window.location.href);
                    window.location.href = `/login?redirect=${currentUrl}`;
                });
            }
            
            // Multiple date options with time slots functionality
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('preferred-dates-container');
                const addDateOptionBtn = document.getElementById('add-date-option');
                const preferredTimesInput = document.getElementById('preferred-times-input');
                let dateOptionCounter = 0;
                
                // Set min date to today for all date inputs
                function setDateConstraints(dateInput) {
                    const today = new Date();
                    const yyyy = today.getFullYear();
                    const mm = String(today.getMonth() + 1).padStart(2, '0');
                    const dd = String(today.getDate()).padStart(2, '0');
                    const todayFormatted = `${yyyy}-${mm}-${dd}`;
                    dateInput.min = todayFormatted;
                    
                    // Set max date to 30 days from now
                    const maxDate = new Date();
                    maxDate.setDate(maxDate.getDate() + 30);
                    const maxYyyy = maxDate.getFullYear();
                    const maxMm = String(maxDate.getMonth() + 1).padStart(2, '0');
                    const maxDd = String(maxDate.getDate()).padStart(2, '0');
                    dateInput.max = `${maxYyyy}-${maxMm}-${maxDd}`;
                }
                
                // Set constraints for initial date input
                const initialDateInput = document.querySelector('.preferred-date');
                setDateConstraints(initialDateInput);
                
                // Function to generate time slots
                function generateTimeSlots(containerId) {
                    const container = document.getElementById(containerId);
                    container.innerHTML = ''; // Clear previous slots
                    
                    // Generate time slots from 8am to 8pm in 15-minute increments
                    for (let hour = 8; hour <= 20; hour++) {
                        for (let minute = 0; minute < 60; minute += 15) {
                            // Don't create 8:15pm, 8:30pm, or 8:45pm slots
                            if (hour === 20 && minute > 0) continue;
                            
                            const formattedHour = hour.toString().padStart(2, '0');
                            const formattedMinute = minute.toString().padStart(2, '0');
                            const timeValue = `${formattedHour}:${formattedMinute}`;
                            
                            // Format for display (12-hour format)
                            let displayHour = hour > 12 ? hour - 12 : hour;
                            const ampm = hour >= 12 ? 'PM' : 'AM';
                            displayHour = displayHour === 0 ? 12 : displayHour; // Handle midnight
                            const displayTime = `${displayHour}:${formattedMinute} ${ampm}`;
                            
                            // Create the time slot element
                            const slot = document.createElement('div');
                            slot.className = 'time-slot';
                            slot.textContent = displayTime;
                            slot.dataset.value = timeValue;
                            slot.dataset.containerId = containerId;
                            
                            slot.addEventListener('click', function() {
                                // Toggle selected class
                                this.classList.toggle('selected');
                                
                                // Update hidden input
                                updatePreferredTimesInput();
                            });
                            
                            container.appendChild(slot);
                        }
                    }
                }
                
                // Setup date change event to update hidden input and generate time slots
                function setupDateChangeEvent(dateOption, index) {
                    const dateInput = dateOption.querySelector('.preferred-date');
                    const containerId = `time-slots-container-${index}`;
                    
                    // Initial state - hide the time slots until a date is selected
                    const timeSlotContainer = document.getElementById(containerId);
                    if (!dateInput.value) {
                        timeSlotContainer.parentElement.style.display = 'none';
                    }
                    
                    dateInput.addEventListener('change', function() {
                        if (this.value) {
                            // Show the time slots container
                            timeSlotContainer.parentElement.style.display = 'block';
                            // Generate time slots when a date is selected
                            generateTimeSlots(containerId);
                        } else {
                            // Hide time slots if date is cleared
                            timeSlotContainer.parentElement.style.display = 'none';
                        }
                        updatePreferredTimesInput();
                    });
                }
                
                // Setup initial date change event
                setupDateChangeEvent(document.querySelector('.preferred-date-option'), 0);
                
                // Setup initial date change event
                setupDateChangeEvent(document.querySelector('.preferred-date-option'), 0);
                
                // Function to update hidden input with all preferred times
                function updatePreferredTimesInput() {
                    const dateOptions = document.querySelectorAll('.preferred-date-option');
                    const preferredTimes = [];
                    
                    dateOptions.forEach((dateOption, index) => {
                        const date = dateOption.querySelector('.preferred-date').value;
                        if (!date) return;
                        
                        const timeSlotContainer = document.getElementById(`time-slots-container-${index}`);
                        const selectedSlots = timeSlotContainer.querySelectorAll('.time-slot.selected');
                        
                        if (selectedSlots.length > 0) {
                            const times = Array.from(selectedSlots).map(slot => slot.dataset.value);
                            preferredTimes.push({
                                date: date,
                                times: times
                            });
                        }
                    });
                    
                    preferredTimesInput.value = JSON.stringify(preferredTimes);
                }
                
                // Add Date Option button click event
                addDateOptionBtn.addEventListener('click', function() {
                    // Increment counter
                    dateOptionCounter++;
                    
                    // Clone the first option
                    const newOption = document.querySelector('.preferred-date-option').cloneNode(true);
                    
                    // Reset values in the clone
                    newOption.querySelector('.preferred-date').value = '';
                    
                    // Update the time-slots-container id
                    const newTimeSlotContainer = newOption.querySelector('.time-slots-grid');
                    newTimeSlotContainer.id = `time-slots-container-${dateOptionCounter}`;
                    newTimeSlotContainer.innerHTML = ''; // Clear any existing slots
                    
                    // Show the remove button
                    newOption.querySelector('.remove-date-option').style.display = 'block';
                    
                    // Add to container
                    container.appendChild(newOption);
                    
                    // Set date constraints for the new option
                    setDateConstraints(newOption.querySelector('.preferred-date'));
                    
                    // Hide the time slots container until a date is selected
                    newTimeSlotContainer.parentElement.style.display = 'none';
                    
                    // Setup date change event (which will generate time slots when a date is selected)
                    setupDateChangeEvent(newOption, dateOptionCounter);
                    
                    // Add click event for remove button
                    newOption.querySelector('.remove-date-option').addEventListener('click', function() {
                        container.removeChild(newOption);
                        updatePreferredTimesInput();
                    });
                });
            });
        </script>
</script>
</html>
