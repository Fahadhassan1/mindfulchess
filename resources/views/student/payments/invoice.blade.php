<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $payment->payment_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 30px;
            border-bottom: 2px solid #eee;
        }
        
        .company-info h3,
        .customer-info h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .company-info p,
        .customer-info p {
            margin-bottom: 8px;
            color: #555;
        }
        
        .invoice-meta {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 2px solid #eee;
        }
        
        .invoice-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .meta-item strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-succeeded {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        
        .invoice-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .invoice-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .invoice-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .amount {
            font-weight: 600;
            color: #333;
        }
        
        .total-section {
            background: #f8f9fa;
            padding: 30px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px 0;
        }
        
        .total-row.final {
            border-top: 2px solid #667eea;
            margin-top: 15px;
            padding-top: 15px;
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
        }
        
        .discount-info {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 30px;
        }
        
        .discount-info h4 {
            color: #2d5a2d;
            margin-bottom: 10px;
        }
        
        .discount-info p {
            color: #2d5a2d;
            margin-bottom: 5px;
        }
        
        .session-details {
            background: #f0f4ff;
            border: 1px solid #c3d9ff;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 30px;
        }
        
        .session-details h4 {
            color: #1a365d;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .session-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .session-details p {
            color: #2d3748;
            margin-bottom: 8px;
        }
        
        .session-details strong {
            color: #1a365d;
        }
        
        .footer {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            color: #666;
            border-top: 2px solid #eee;
        }
        
        .actions {
            padding: 20px 30px;
            text-align: center;
            background: white;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }
        
        @media print {
            .actions {
                display: none;
            }
            
            .container {
                box-shadow: none;
                margin: 0;
            }
            
            body {
                background: white;
            }
        }
        
        @media (max-width: 768px) {
            .invoice-details {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .invoice-meta-grid {
                grid-template-columns: 1fr;
            }
            
            .session-details-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                margin: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>INVOICE</h1>
            <p>Payment Receipt for Chess Lesson</p>
        </div>

        <!-- Company and Customer Information -->
        <div class="invoice-details">
            <div class="company-info">
                <h3>From:</h3>
                <p><strong>Mindful Chess</strong></p>
                <p>Online Chess Learning Platform</p>
                <p>Email: support@mindfulchess.com</p>
                <p>Website: www.mindfulchess.com</p>
            </div>
            <div class="customer-info">
                <h3>To:</h3>
                <p><strong>{{ $payment->customer_name }}</strong></p>
                <p>{{ $payment->customer_email }}</p>
                @if($payment->chessSession && $payment->chessSession->student)
                    <p>Student ID: {{ $payment->chessSession->student->id }}</p>
                @endif
            </div>
        </div>

        <!-- Invoice Metadata -->
        <div class="invoice-meta">
            <div class="invoice-meta-grid">
                <div class="meta-item">
                    <strong>Invoice Number:</strong>
                    {{ $payment->payment_id }}
                </div>
                <div class="meta-item">
                    <strong>Payment Date:</strong>
                    {{ $payment->paid_at->format('F d, Y') }}
                </div>
                <div class="meta-item">
                    <strong>Payment Method:</strong>
                    {{ ucfirst($payment->payment_method_type ?? 'Card') }}
                </div>
                <div class="meta-item">
                    <strong>Status:</strong>
                    <span class="status-badge status-{{ $payment->status }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Session Details (if available) -->
        @if($payment->chessSession)
        <div class="session-details">
            <h4>Chess Session Information</h4>
            <div class="session-details-grid">
                <div>
                    <p><strong>Session Name:</strong> {{ $payment->chessSession->session_name }}</p>
                    <p><strong>Session Type:</strong> {{ ucfirst($payment->chessSession->session_type) }}</p>
                    <p><strong>Duration:</strong> {{ $payment->chessSession->duration }} minutes</p>
                </div>
                <div>
                    <p><strong>Status:</strong> {{ ucfirst($payment->chessSession->status) }}</p>
                    @if($payment->chessSession->scheduled_at)
                        <p><strong>Scheduled:</strong> {{ $payment->chessSession->scheduled_at->format('F d, Y H:i') }}</p>
                    @endif
                    @if($payment->chessSession->teacher)
                        <p><strong>Teacher:</strong> {{ $payment->chessSession->teacher->name }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Discount Information (if applicable) -->
        @if($payment->coupon_code)
        <div class="discount-info">
            <h4>Discount Applied</h4>
            <p><strong>Coupon Code:</strong> {{ $payment->coupon_code }}</p>
            <p><strong>Discount:</strong> {{ $payment->discount_percentage }}% off</p>
            <p><strong>Original Amount:</strong> £{{ number_format($payment->original_amount, 2) }}</p>
        </div>
        @endif

        <!-- Invoice Items -->
        <div style="padding: 0 30px;">
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Chess Lesson Session</strong>
                            @if($payment->chessSession)
                                <br><small>{{ $payment->chessSession->session_name }}</small>
                            @endif
                        </td>
                        <td>
                            @if($payment->chessSession)
                                {{ ucfirst($payment->chessSession->session_type) }}
                            @else
                                Chess Session
                            @endif
                        </td>
                        <td>
                            @if($payment->chessSession)
                                {{ $payment->chessSession->duration }} minutes
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="amount">£{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Total Section -->
        <div class="total-section">
            @if($payment->coupon_code && $payment->original_amount)
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>£{{ number_format($payment->original_amount, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>Discount ({{ $payment->discount_percentage }}%):</span>
                    <span>-£{{ number_format($payment->original_amount - $payment->amount, 2) }}</span>
                </div>
            @endif
            <div class="total-row final">
                <span>Total Paid:</span>
                <span>£{{ number_format($payment->amount, 2) }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <a href="{{ route('student.payments') }}" class="btn btn-secondary">← Back to Payments</a>
            <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for choosing Mindful Chess!</p>
            <p>This is an automatically generated invoice. For any questions, please contact our support team.</p>
            <p style="margin-top: 15px; font-size: 0.9rem;">
                Generated on {{ now()->format('F d, Y H:i') }}
            </p>
        </div>
    </div>
</body>
</html>
