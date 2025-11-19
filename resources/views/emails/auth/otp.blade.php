<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
    <style>
        /* Reset styles */
        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f4f7;
            color: #51545E;
        }

        /* Main container */
        .email-wrapper {
            width: 100%;
            background-color: #f4f4f7;
            padding: 40px 0;
        }

        .email-content {
            width: 100%;
            max-width: 570px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Header */
        .email-header {
            padding: 30px;
            text-align: center;
            background-color: #3d4852;
            color: #ffffff;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        /* Body */
        .email-body {
            padding: 40px;
        }

        .email-body h2 {
            margin-top: 0;
            color: #333333;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
        }

        .email-body p {
            margin-top: 10px;
            font-size: 16px;
            line-height: 1.6;
            color: #51545E;
            text-align: center;
        }

        /* OTP Box */
        .otp-box {
            background-color: #f0f2f5;
            border-radius: 4px;
            text-align: center;
            padding: 20px;
            margin: 30px 0;
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 5px;
            font-size: 32px;
            font-weight: bold;
            color: #2d3748;
            border: 1px dashed #cbd5e0;
        }

        /* Footer */
        .email-footer {
            padding: 20px;
            text-align: center;
            background-color: #f4f4f7;
            color: #6b7280;
            font-size: 12px;
        }

        .email-footer p {
            margin: 5px 0;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-content {
                width: 100% !important;
                border-radius: 0 !important;
            }

            .email-body {
                padding: 20px !important;
            }
        }
    </style>
</head>

<body>

    <div class="email-wrapper">
        <div class="email-content">

            <!-- Header -->
            <div class="email-header">
                <!-- Replace 'Your Brand' with config('app.name') -->
                <h1>{{ config('app.name') }}</h1>
            </div>

            <!-- Body -->
            <div class="email-body">
                <h2>Verification Required</h2>
                <p>Hello,</p>
                <p>You recently requested to reset your password or verify your account. Use the code below to complete
                    the process:</p>

                <!-- The OTP Code -->
                <div class="otp-box">
                    {{ $otp }}
                </div>

                <p>This code is valid for <strong>5 minutes</strong>.</p>
                <p style="font-size: 14px; color: #718096; margin-top: 30px;">
                    If you did not request this code, please ignore this email. No further action is required.
                </p>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p>Need help? Contact our support team.</p>
            </div>

        </div>
    </div>

</body>

</html>
