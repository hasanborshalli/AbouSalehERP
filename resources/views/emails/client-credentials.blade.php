<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Account Access Details | تفاصيل الوصول إلى الحساب</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif; color:#333; line-height:1.8;">

    {{-- ── English ───────────────────────────────────── --}}
    <p>Dear {{ $user->name }},</p>
    <p>As part of our real estate agreement process, we have created a secure account for you to access your contract
        and related information.</p>

    <p><strong>Your login credentials:</strong></p>
    <table cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
        <tr>
            <td><strong>Email:</strong></td>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <td><strong>Temporary Password:</strong></td>
            <td>{{ $rawPassword }}</td>
        </tr>
    </table>

    <p>Login here: <a href="{{ url('/login') }}" style="color:#1a73e8;">{{ url('/login') }}</a></p>
    <p><strong>Important:</strong> Please change your password after your first login.</p>
    @if($contractPath)
    <p>A copy of your <strong>real estate contract (PDF)</strong> is attached to this email. Please review it carefully.
    </p>
    @endif

    <hr style="margin:24px 0; border:none; border-top:1px solid #eee;">

    {{-- ── Arabic ────────────────────────────────────── --}}
    <div dir="rtl" style="text-align:right; font-family: Arial, sans-serif;">
        <p>عزيزي/عزيزتي {{ $user->name }}،</p>
        <p>في إطار اتفاقيتنا العقارية، تم إنشاء حساب آمن لك للاطلاع على عقدك ومعلوماتك.</p>

        <p><strong>بيانات تسجيل الدخول:</strong></p>
        <table cellpadding="6" cellspacing="0" style="border-collapse:collapse; direction:rtl;">
            <tr>
                <td><strong>البريد الإلكتروني:</strong></td>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <td><strong>كلمة المرور المؤقتة:</strong></td>
                <td>{{ $rawPassword }}</td>
            </tr>
        </table>

        <p>رابط تسجيل الدخول: <a href="{{ url('/login') }}" style="color:#1a73e8;">{{ url('/login') }}</a></p>
        <p><strong>ملاحظة:</strong> يُرجى تغيير كلمة المرور فور تسجيل الدخول للمرة الأولى.</p>
        @if($contractPath)
        <p>نسخة من <strong>عقدك العقاري (PDF)</strong> مرفقة بهذا البريد. يُرجى مراجعتها بعناية.</p>
        @endif

        <p>مع تحياتنا،</p>
        <p>
            <strong>أبو صالح</strong><br>
            إدارة العقارات<br>
            هاتف: 219 999 71 961+<br>
            البريد: info@abousaleh.me
        </p>
    </div>

    <hr style="margin-top:30px;">
    <p style="font-size:11px; color:#777;">
        This email contains confidential information. | هذا البريد يحتوي على معلومات سرية.
    </p>
</body>

</html>