<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Account Access Details</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif; color:#333; line-height:1.6;">

    <p>Dear {{ $user->name }},</p>

    <p>
        As part of our real estate agreement process, we have created a secure account for you
        to access your contract and related information.
    </p>

    <p><strong>Your login credentials are as follows:</strong></p>

    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <td><strong>User ID:</strong></td>
            <td>{{ $user->id }}</td>
        </tr>
        <tr>
            <td><strong>Temporary Password:</strong></td>
            <td>{{ $rawPassword }}</td>
        </tr>
    </table>

    <p>
        Login using the link below:
    </p>

    <p>
        <a href="{{ url('/login') }}" style="color:#1a73e8;">
            {{ url('/login') }}
        </a>
    </p>

    <p>
        <strong>Important:</strong> For security reasons, please log in and change your password
        immediately after your first access.
    </p>
    @if($contractPath)
    <p>
        Please note that a copy of your <strong>real estate contract is attached to this email (PDF)</strong>.
        Kindly review the document carefully and contact us if you have any questions.
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