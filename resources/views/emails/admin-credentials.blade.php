<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admin Account Access | بيانات حساب المسؤول</title>
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
        <p>An <strong>administrator account</strong> has been created for you to manage the system and access
            administrative features.</p>

        <p><strong>Your admin login credentials:</strong></p>
        <table>
            <tr>
                <td>ID:</td>
                <td>{{ $user->id }}</td>
            </tr>
            <tr>
                <td>Temporary Password:</td>
                <td><strong>{{ $rawPassword }}</strong></td>
            </tr>
            @if(!empty($role))
            <tr>
                <td>Role:</td>
                <td>{{ $role }}</td>
            </tr>
            @endif
        </table>

        <p style="margin-top:16px;">
            <a href="{{ url('/login') }}" class="btn">Login to admin panel</a>
        </p>
        <p><strong>Important:</strong> Please change your password after your first login for security. This account
            provides elevated permissions — do not share your credentials.</p>

        <p>If you have any issues accessing your account, contact us at <a
                href="mailto:info@abousaleh.me">info@abousaleh.me</a> or call +961 71 999 219.</p>

        <p>Kind regards,<br><strong>Abou Saleh General Trading</strong></p>

        <hr class="divider">

        {{-- ══ ARABIC ════════════════════════════════════════════════ --}}
        <div class="section-ar">
            <p>عزيزي/عزيزتي <strong>{{ $user->name }}</strong>،</p>
            <p>تم إنشاء <strong>حساب مسؤول</strong> لك لإدارة النظام والوصول إلى الميزات الإدارية.</p>

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
                @if(!empty($role))
                <tr>
                    <td>الصلاحية:</td>
                    <td>{{ $role }}</td>
                </tr>
                @endif
            </table>

            <p style="margin-top:16px;">
                <a href="{{ url('/login') }}" class="btn">تسجيل الدخول إلى لوحة التحكم</a>
            </p>
            <p><strong>ملاحظة مهمة:</strong> يُرجى تغيير كلمة المرور فور تسجيل الدخول للمرة الأولى. هذا الحساب يمتلك
                صلاحيات موسّعة — لا تشارك بياناتك مع أي شخص.</p>

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