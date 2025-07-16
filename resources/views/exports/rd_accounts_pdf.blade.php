<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RD Accounts Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .text-right {
            text-align: right;
        }
        .header {
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RD Accounts Report</h1>
        <p>Generated on: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Account Number</th>
                <th>Customer</th>
                <th>Agent</th>
                <th>Monthly Amount</th>
                <th>Total Deposited</th>
                <th>Maturity Amount</th>
                <th>Opening Date</th>
                <th>Maturity Date</th>
                <th>Duration</th>
                <th>Interest Rate</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rdAccounts as $account)
                <tr>
                    <td>{{ $account->account_number }}</td>
                    <td>{{ $account->customer->name }}</td>
                    <td>{{ $account->agent->name }}</td>
                    <td class="text-right">₹{{ number_format($account->monthly_amount, 2) }}</td>
                    <td class="text-right">₹{{ number_format($account->total_deposited, 2) }}</td>
                    <td class="text-right">₹{{ number_format($account->maturity_amount, 2) }}</td>
                    <td>{{ $account->start_date->format('d/m/Y') }}</td>
                    <td>{{ $account->maturity_date->format('d/m/Y') }}</td>
                    <td>{{ $account->duration_months }} months</td>
                    <td>{{ $account->interest_rate }}%</td>
                    <td>{{ ucfirst($account->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Total Accounts:</strong> {{ $rdAccounts->count() }}</p>
        <p><strong>Total Monthly Deposits:</strong> ₹{{ number_format($rdAccounts->sum('monthly_amount'), 2) }}</p>
        <p><strong>Total Deposited Amount:</strong> ₹{{ number_format($rdAccounts->sum('total_deposited'), 2) }}</p>
        <p><strong>Total Maturity Amount:</strong> ₹{{ number_format($rdAccounts->sum('maturity_amount'), 2) }}</p>
        <p><strong>Active Accounts:</strong> {{ $rdAccounts->where('status', 'active')->count() }}</p>
        <p><strong>Matured Accounts:</strong> {{ $rdAccounts->where('status', 'matured')->count() }}</p>
        <p><strong>Closed Accounts:</strong> {{ $rdAccounts->where('status', 'closed')->count() }}</p>
    </div>
</body>
</html> 