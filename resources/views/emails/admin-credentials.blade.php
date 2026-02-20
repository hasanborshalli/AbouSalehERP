<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admin Account Access Details</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif; color:#333; line-height:1.6;">

    <p>Dear {{ $user->name }},</p>

    <p>
        An <strong>administrator account</strong> has been created for you to manage the system and access
        administrative features.
    </p>

    <p><strong>Your admin login credentials are as follows:</strong></p>

    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <td><strong>Admin ID:</strong></td>
            <td>{{ $user->id }}</td>
        </tr>
        <tr>
            <td><strong>Temporary Password:</strong></td>
            <td>{{ $rawPassword }}</td>
        </tr>
    </table>

    <p>Login using the link below:</p>

    <p>
        <a href="{{ url('/login') }}" style="color:#1a73e8;">
            {{ url('/login') }}
        </a>
    </p>

    <p>
        <strong>Important:</strong> For security reasons, please log in and change your password
        immediately after your first access.
    </p>

    <p>
        <strong>Admin Access Notice:</strong> This account provides elevated permissions. Please use it responsibly and
        do not share your credentials with anyone.
    </p>

    @if(!empty($role))
    <p>
        Your assigned role: <strong>{{ $role }}</strong>
    </p>
    @endif

    <p>
        If you experience any issues accessing your account, feel free to reach out to us.
    </p>

    <p>Kind regards,</p>

    <p>
        <strong>Abou Saleh</strong><br>
        Real Estate Management<br>
        Phone: +961 71 999 219<br>
        Email: info@abousaleh.me
    </p>

    <hr style="margin-top:30px;">

    <p style="font-size:12px; color:#777;">
        This email contains confidential information intended only for the recipient.
        If you are not the intended recipient, please delete this email immediately.
    </p>

</body>

</html>