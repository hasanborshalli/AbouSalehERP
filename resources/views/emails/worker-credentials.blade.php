<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Worker Account | حساب العامل</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif; color:#333; line-height:1.8;">

    <p>Dear {{ $worker->name }},</p>
    <p>An account has been created for you to access your contracts and payment schedule.</p>
    <table cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $worker->email }}</td>
        </tr>
        <tr>
            <td><strong>Temporary Password:</strong></td>
            <td>{{ $rawPassword }}</td>
        </tr>
    </table>
    <p>Login: <a href="{{ url('/login') }}" style="color:#1a73e8;">{{ url('/login') }}</a></p>
    <p>Please change your password on first login.</p>

    <hr style="margin:24px 0; border:none; border-top:1px solid #eee;">

    <div dir="rtl" style="text-align:right;">
        <p>عزيزي {{ $worker->name }}،</p>
        <p>تم إنشاء حساب لك للاطلاع على عقودك وجدول المدفوعات.</p>
        <table cellpadding="6" cellspacing="0" style="border-collapse:collapse; direction:rtl;">
            <tr>
                <td><strong>البريد:</strong></td>
                <td>{{ $worker->email }}</td>
            </tr>
            <tr>
                <td><strong>كلمة المرور المؤقتة:</strong></td>
                <td>{{ $rawPassword }}</td>
            </tr>
        </table>
        <p>رابط الدخول: <a href="{{ url('/login') }}" style="color:#1a73e8;">{{ url('/login') }}</a></p>
        <p>يُرجى تغيير كلمة المرور عند أول دخول.</p>
        <p>مع تحياتنا — أبو صالح للتجارة العامة</p>
    </div>
</body>

</html>