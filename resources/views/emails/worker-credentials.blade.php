<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Worker Account Access | حساب العامل</title>
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
        <p>An account has been created for you to access your contracts, work assignments, and payment schedule.</p>

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

        @if($contractPath ?? false)
        <p>📎 A copy of your <strong>work contract (PDF)</strong> is attached to this email.</p>
        @endif

        <p>For any questions, contact us at <a href="mailto:info@abousaleh.me">info@abousaleh.me</a> or +961 71 999 219.
        </p>
        <p>Best regards,<br><strong>Abou Saleh General Trading</strong></p>

        <hr class="divider">

        {{-- ══ ARABIC ════════════════════════════════════════════════ --}}
        <div class="section-ar">
            <p>عزيزي <strong>{{ $user->name }}</strong>،</p>
            <p>تم إنشاء حساب لك للاطلاع على عقودك ومهامك وجدول مدفوعاتك.</p>

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
            <p><strong>ملاحظة:</strong> يُرجى تغيير كلمة المرور عند أول دخول.</p>

            @if($contractPath ?? false)
            <p>📎 نسخة من <strong>عقد العمل (PDF)</strong> مرفقة بهذا البريد.</p>
            @endif

            <p>لأي استفسار: <a href="mailto:info@abousaleh.me">info@abousaleh.me</a> | 219 999 71 961+</p>
            <p>مع تحياتنا،<br><strong>أبو صالح للتجارة العامة</strong></p>
        </div>

        <div class="footer">
            This email contains confidential information. | هذا البريد يحتوي على معلومات سرية.
        </div>

    </div>
</body>

</html>