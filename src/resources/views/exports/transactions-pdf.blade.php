<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #343a40;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #dee2e6;
        }
        td {
            padding: 6px;
            border: 1px solid #dee2e6;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-end {
            text-align: right;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }
        .page-number:before {
            content: counter(page);
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    
    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 30%;">Description</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 18%;">Notes</th>
                <th style="width: 10%;" class="text-end">Withdraw</th>
                <th style="width: 10%;" class="text-end">Deposit</th>
                <th style="width: 10%;" class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->date->format('M d, Y') }}</td>
                <td>{{ $transaction->description }}</td>
                <td>{{ $transaction->category ? $transaction->category->name : '-' }}</td>
                <td>{{ $transaction->notes ?? '-' }}</td>
                <td class="text-end">{{ $transaction->withdraw ? '$' . number_format($transaction->withdraw, 2) : '-' }}</td>
                <td class="text-end">{{ $transaction->deposit ? '$' . number_format($transaction->deposit, 2) : '-' }}</td>
                <td class="text-end">${{ number_format($transaction->balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        Page <span class="page-number"></span> | Generated on {{ now()->format('F d, Y') }}
    </div>
</body>
</html>
