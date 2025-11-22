<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style>
        /* Base */
        body {
            background-color: #f4f4f7;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 100%;
            -webkit-text-size-adjust: none;
            color: #51545E;
        }

        /* Container */
        .email-wrapper {
            width: 100%;
            background-color: #f4f4f7;
            padding: 40px 0;
        }

        .email-content {
            background-color: #ffffff;
            margin: 0 auto;
            width: 570px;
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* Header */
        .email-header {
            background-color: #2d3748;
            padding: 25px;
            text-align: center;
            color: #ffffff;
        }

        .email-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #ffffff;
        }

        /* Body */
        .email-body {
            padding: 35px;
        }

        .headline {
            color: #333333;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .text-regular {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        /* Order Table */
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            font-size: 14px;
        }

        .order-table th {
            text-align: left;
            border-bottom: 2px solid #edf2f7;
            padding: 10px;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
        }

        .order-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #edf2f7;
            color: #2d3748;
        }

        .order-table .price-col {
            text-align: right;
            font-weight: 600;
        }

        .order-table .total-row td {
            border-top: 2px solid #cbd5e0;
            border-bottom: none;
            font-weight: bold;
            font-size: 16px;
            padding-top: 15px;
            color: #1a202c;
        }

        /* Footer */
        .email-footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
            border-top: 1px solid #edf2f7;
        }
    </style>
</head>

<body>

    <div class="email-wrapper">
        <div class="email-content">

            <!-- Branding Header -->
            <div class="email-header">
                <h1>{{ config('app.name') }}</h1>
            </div>

            <div class="email-body">
                <!-- Main Message -->
                <div class="headline">{{ $headline }}</div>

                <p class="text-regular">Hello <strong>{{ $order->user->name }}</strong>,</p>
                <p class="text-regular">{{ $customMessage }}</p>

                <p class="text-regular" style="color: #718096; font-size: 14px;">
                    Order #{{ $order->id }} • {{ $order->created_at->format('M d, Y') }}
                </p>

                <!-- Items Table -->
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th style="text-align: center;">Qty</th>
                            <th class="price-col">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->products as $item)
                            <tr>
                                <td>
                                    {{ $item->name }}
                                    <br>
                                    <span style="font-size: 12px; color: #718096;">SKU: {{ $item->id }}</span>
                                </td>
                                <td style="text-align: center;">{{ $item->pivot->quantity }}</td>
                                <td class="price-col">${{ number_format($item->pivot->price ?? $item->price, 2) }}</td>
                            </tr>
                        @endforeach

                        <!-- Total Row -->
                        <tr class="total-row">
                            <td colspan="2" style="text-align: right; padding-right: 20px;">Total</td>
                            <td class="price-col">${{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>

</body>

</html>
