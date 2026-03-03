<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Worker Portal Access</title>
</head>

<body style="font-family: Arial, Helvetica, sans-serif; color:#333; line-height:1.6;">

    <p>Dear {{ $user->name }},</p>

    <p>
        We have created a secure <strong>Worker Portal</strong> account for you to track your
        contract payments with Abou Saleh General Trading.
    </p>

    <p><strong>Your login credentials:</strong></p>

    <table cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
        <tr>
            <td><strong>Login ID:</strong></td>
            <td>{{ $user->id }}</td>
        </tr>
        <tr>
            <td><strong>Temporary Password:</strong></td>
            <td>{{ $rawPassword }}</td>
        </tr>
    </table>

    <p>
        Access your portal here:<br>
        <a href="{{ url('/login') }}" style="color:#1a73e8;">{{ url('/login') }}</a>
    </p>

    <p>
        In your portal you can:
    </p>
    <ul>
        <li>View your contract details and payment schedule</li>
        <li>See which payments have been made and which are upcoming</li>
        <li>Download payment receipts</li>
    </ul>

    @if($contractPath)
    <p>
        A copy of your <strong>work contract is attached to this email (PDF)</strong>.
        Please review it carefully and contact us if you have any questions.
    </p>
    @endif

    <p>
        <strong>Important:</strong> Please log in and change your password immediately after first access.
    </p>

    <p>Kind regards,</p>
    <p>
        <strong>Abou Saleh General Trading</strong><br>
        Phone: +961 71 999 219<br>
        Email: info@abousaleh.me
    </p>

    <hr style="margin-top:30px;">
    <p style="font-size:12px;color:#777;">
        This email contains confidential information intended only for the recipient.
    </p>
</body>

</html>