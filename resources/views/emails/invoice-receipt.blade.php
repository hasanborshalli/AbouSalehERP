<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Receipt | إيصال دفع</title>
</head>

<body style="font-family: Arial, sans-serif; color:#333; line-height:1.8;">

    <p>Dear {{ $invoice->contract->client->name ?? 'Client' }},</p>
    <p>Your payment has been received and a receipt is attached to this email.</p>
    <p>
        <strong>Invoice:</strong> {{ $invoice->invoice_number }}<br>
        <strong>Amount:</strong> ${{ number_format($invoice->amount_paid ?? $invoice->amount, 2) }}<br>
        <strong>Date:</strong> {{ $invoice->paid_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}
    </p>
    <p>Thank you for your payment.</p>

    <hr style="margin:24px 0; border:none; border-top:1px solid #eee;">

    <div dir="rtl" style="text-align:right;">
        <p>عزيزي {{ $invoice->contract->client->name ?? 'العميل' }}،</p>
        <p>تم استلام دفعتك وإيصال مرفق بهذا البريد.</p>
        <p>
            <strong>الفاتورة:</strong> {{ $invoice->invoice_number }}<br>
            <strong>المبلغ:</strong> ${{ number_format($invoice->amount_paid ?? $invoice->amount, 2) }}<br>
            <strong>التاريخ:</strong> {{ $invoice->paid_at?->format('Y-m-d') ?? now()->format('Y-m-d') }}
        </p>
        <p>شكراً لك.</p>
        <p>أبو صالح للعقارات</p>
    </div>
</body>

</html>