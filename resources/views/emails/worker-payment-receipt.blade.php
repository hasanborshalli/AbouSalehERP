<!doctype html>
<html>

<body style="font-family:Arial,sans-serif;color:#333;line-height:1.6;">
    <p>Dear {{ $payment->contract->worker->name ?? 'Contractor' }},</p>

    <p>
        A payment has been processed for your work contract with
        <strong>Abou Saleh General Trading</strong>.
    </p>

    <table cellpadding="6" cellspacing="0" style="border-collapse:collapse;margin:12px 0;">
        <tr>
            <td><strong>Payment #:</strong></td>
            <td>{{ $payment->installment_index }} of {{ $payment->contract->payment_months }}</td>
        </tr>
        <tr>
            <td><strong>Amount:</strong></td>
            <td>${{ number_format($payment->amount,2) }}</td>
        </tr>
        <tr>
            <td><strong>Date Paid:</strong></td>
            <td>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d M Y') : '—' }}</td>
        </tr>
        <tr>
            <td><strong>Scope:</strong></td>
            <td>{{ $payment->contract->scope_of_work }}</td>
        </tr>
    </table>

    <p>Please find the attached <strong>Payment Voucher (PDF)</strong> for your records.</p>

    <p>
        You can also view all your payments on your worker portal:<br>
        <a href="{{ url('/worker/payments') }}" style="color:#1a73e8;">{{ url('/worker/payments') }}</a>
    </p>

    <p>Kind regards,</p>
    <p>
        <strong>Abou Saleh General Trading</strong><br>
        Phone: +961 71 999 219<br>
        Email: info@abousaleh.me
    </p>
</body>

</html>