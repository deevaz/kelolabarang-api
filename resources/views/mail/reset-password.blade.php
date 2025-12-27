<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode Reset Password</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 40px;
        }
        .code-container {
            text-align: center;
            margin: 30px 0;
        }
        .code {
            display: inline-block;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 10px;
            padding: 20px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            font-family: monospace;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .steps {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
            text-align: center;
        }
        .warning {
            color: #dc2626;
            font-weight: bold;
            background: #fee2e2;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Reset Password</h1>
            <p>{{ config('app.name') }}</p>
        </div>
        
        <div class="content">
            <h2>Halo {{ $name }}!</h2>
            <p>Kami menerima permintaan reset password untuk akun Anda. Gunakan kode di bawah ini untuk melanjutkan proses reset password.</p>
            
            <div class="info-box">
                <strong>⏰ Kode ini berlaku sampai:</strong> {{ $expiresAt }} ({{ $expiryMinutes }} menit)
            </div>
            
            <div class="code-container">
                <div class="code">{{ $code }}</div>
            </div>
            
            <div class="steps">
                <h3>📝 Cara menggunakan kode:</h3>
                <ol>
                    <li>Buka aplikasi {{ config('app.name') }}</li>
                    <li>Masuk ke halaman "Reset Password"</li>
                    <li>Masukkan email: <strong>{{ $email }}</strong></li>
                    <li>Masukkan kode di atas</li>
                    <li>Buat password baru</li>
                </ol>
            </div>
            
            <div class="warning">
                ⚠️ JANGAN BERIKAN KODE INI KEPADA SIAPAPUN!
                <br>Tim {{ config('app.name') }} tidak akan pernah meminta kode ini.
            </div>
            
            <p>Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tetap aman.</p>
            
            <div class="footer">
                <p>Email ini dikirim ke {{ $email }}</p>
                <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p style="font-size: 12px; color: #9ca3af;">
                    Jika Anda mengalami masalah, hubungi support@abdaziz.my.id
                </p>
            </div>
        </div>
    </div>
</body>
</html>