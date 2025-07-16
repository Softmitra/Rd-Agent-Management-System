<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .receipt-title {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .receipt-details {
            margin-bottom: 20px;
        }
        .receipt-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-details th, .receipt-details td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .receipt-details th {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
        }
        .signature {
            margin-top: 40px;
            border-top: 1px solid #000;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="company-name">Your Company Name</div>
            <div class="receipt-title">Payment Receipt</div>
        </div>

        <div class="receipt-details">
            <table>
                <tr>
                    <th>Receipt Number</th>
                    <td>{{ $payment->receipt_number }}</td>
                    <th>Date</th>
                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Customer Name</th>
                    <td>{{ $payment->customer->name }}</td>
                    <th>Account Number</th>
                    <td>{{ $payment->rdAccount->account_number }}</td>
                </tr>
                <tr>
                    <th>Amount</th>
                    <td>₹{{ number_format($payment->amount, 2) }}</td>
                    <th>Payment Mode</th>
                    <td>{{ ucfirst($payment->payment_mode) }}</td>
                </tr>
                @if($payment->payment_mode === 'cheque')
                <tr>
                    <th>Cheque Number</th>
                    <td>{{ $payment->cheque_number }}</td>
                    <th>Bank Name</th>
                    <td>{{ $payment->bank_name }}</td>
                </tr>
                @endif
                @if($payment->payment_mode === 'online')
                <tr>
                    <th>Transaction ID</th>
                    <td colspan="3">{{ $payment->transaction_id }}</td>
                </tr>
                @endif
                @if($payment->payment_mode === 'upi')
                <tr>
                    <th>UPI ID</th>
                    <td colspan="3">{{ $payment->upi_id }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="footer">
            <div class="signature">
                Authorized Signatory
            </div>
            <div style="margin-top: 20px;">
                This is a computer generated receipt. No signature required.
            </div>
        </div>
    </div>
</body>
</html> 