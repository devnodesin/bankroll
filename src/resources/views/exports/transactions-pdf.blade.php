<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            margin: 5px 0 10px 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        thead {
            display: table-header-group;
        }
        th {
            background-color: #343a40;
            color: white;
            padding: 3px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #dee2e6;
            font-size: 8px;
        }
        td {
            padding: 2px 3px;
            border: 1px solid #dee2e6;
            font-size: 8px;
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
            font-size: 7px;
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
                <th style="width: 10%;">Date</th>
                <th style="width: 32%;">Description</th>
                <th style="width: 22%;">Category</th>
                <th style="width: 12%;" class="text-end">Withdraw</th>
                <th style="width: 12%;" class="text-end">Deposit</th>
                <th style="width: 12%;" class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->date->format('d/m/Y') }}</td>
                <td>{{ $transaction->description }}</td>
                <td>
                    {{ $transaction->category ? $transaction->category->name : '-' }}
                    @if($transaction->notes)
                    <br><span style="color: #6c757d;">[Notes: {{ $transaction->notes }}]</span>
                    @endif
                </td>
                <td class="text-end">{{ $transaction->withdraw ? config('app.currency_symbol', '$') . number_format($transaction->withdraw, 2) : '-' }}</td>
                <td class="text-end">{{ $transaction->deposit ? config('app.currency_symbol', '$') . number_format($transaction->deposit, 2) : '-' }}</td>
                <td class="text-end">{{ config('app.currency_symbol', '$') }}{{ number_format($transaction->balance, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        Page <span class="page-number"></span> | Generated on {{ now()->format('d/m/Y') }}
    </div>
</body>
</html>
