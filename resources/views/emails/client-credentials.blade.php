<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Account Access Details | تفاصيل الوصول إلى الحساب</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            line-height: 1.8;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .divider {
            border: none;
            border-top: 2px solid #e5e7eb;
            margin: 30px 0;
        }

        .section-ar {
            direction: rtl;
            text-align: right;
        }

        table {
            border-collapse: collapse;
        }

        td {
            padding: 6px 12px;
        }

        td:first-child {
            font-weight: bold;
        }

        .footer {
            font-size: 11px;
            color: #777;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 12px;
        }

        .btn {
            display: inline-block;
            background: #1e3a5f;
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">

        {{-- ══ ENGLISH ══════════════════════════════════════════════ --}}
        <p>Dear <strong>{{ $user->name }}</strong>,</p>
        <p>As part of our real estate agreement process, we have created a secure account for you to access your
            contract and related information.</p>

        <p><strong>Your login credentials:</strong></p>
        <table>
            <tr>
                <td>ID:</td>
                <td>{{ $user->id }}</td>
            </tr>
            <tr>
                <td>Temporary Password:</td>
                <td><strong>{{ $rawPassword }}</strong></td>
            </tr>
        </table>

        <p style="margin-top:16px;">
            <a href="{{ url('/login') }}" class="btn">Login to your account</a>
        </p>
        <p><strong>Important:</strong> Please change your password after your first login for security.</p>

        @if($contractPath)
        <p>📎 A copy of your <strong>real estate contract (PDF)</strong> is attached to this email. Please review it
            carefully and keep it for your records.</p>
        @endif

        <p>If you have any questions, feel free to contact us at <a
                href="mailto:info@abousaleh.me">info@abousaleh.me</a> or call +961 71 999 219.</p>

        <p>Kind regards,<br><strong>Abou Saleh General Trading</strong></p>

        <hr class="divider">

        {{-- ══ ARABIC ════════════════════════════════════════════════ --}}
        <div class="section-ar">
            <p>عزيزي/عزيزتي <strong>{{ $user->name }}</strong>،</p>
            <p>في إطار اتفاقيتنا العقارية، تم إنشاء حساب آمن لك للاطلاع على عقدك ومعلوماتك.</p>

            <p><strong>بيانات تسجيل الدخول:</strong></p>
            <table>
                <tr>
                    <td>رقم الحساب:</td>
                    <td>{{ $user->id }}</td>
                </tr>
                <tr>
                    <td>كلمة المرور المؤقتة:</td>
                    <td><strong>{{ $rawPassword }}</strong></td>
                </tr>
            </table>

            <p style="margin-top:16px;">
                <a href="{{ url('/login') }}" class="btn">تسجيل الدخول إلى حسابك</a>
            </p>
            <p><strong>ملاحظة مهمة:</strong> يُرجى تغيير كلمة المرور فور تسجيل الدخول للمرة الأولى.</p>

            @if($contractPath)
            <p>📎 نسخة من <strong>عقدك العقاري (PDF)</strong> مرفقة بهذا البريد. يُرجى مراجعتها بعناية والاحتفاظ بها.
            </p>
            @endif

            <p>لأي استفسار، تواصل معنا على <a href="mailto:info@abousaleh.me">info@abousaleh.me</a> أو على الرقم 219 999
                71 961+.</p>

            <p>مع تحياتنا،<br><strong>أبو صالح للتجارة العامة</strong></p>
        </div>

        <div class="footer">
            This email contains confidential information intended only for the recipient. |
            هذا البريد يحتوي على معلومات سرية مخصصة للمستلم فقط.
        </div>

    </div>
</body>

</html>