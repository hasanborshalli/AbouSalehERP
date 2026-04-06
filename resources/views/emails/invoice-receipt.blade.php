<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Receipt | إيصال دفع</title>
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

        .info-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
        }

        .info-box p {
            margin: 4px 0;
        }

        .footer {
            font-size: 11px;
            color: #777;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 12px;
        }

        .amount {
            font-size: 20px;
            font-weight: 900;
            color: #1e3a5f;
        }
    </style>
</head>

<body>
    <div class="container">

        {{-- ══ ENGLISH ══════════════════════════════════════════════ --}}
        <p>Dear <strong>{{ $invoice->contract->client->name ?? 'Client' }}</strong>,</p>
        <p>Your payment has been successfully received. Please find the receipt attached to this email.</p>

        <div class="info-box">
            <p><strong>Invoice:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Amount paid:</strong> <span class="amount">${{ number_format($invoice->amount_paid ??
                    $invoice->amount, 2) }}</span></p>
            <p><strong>Date:</strong> {{ $invoice->paid_at?->format('d M Y') ?? now()->format('d M Y') }}</p>
            <p><strong>Status:</strong> ✅ Paid</p>
        </div>

        <p>📎 The receipt PDF is attached to this email for your records.</p>
        <p>Thank you for your payment. If you have any questions, contact us at <a
                href="mailto:info@abousaleh.me">info@abousaleh.me</a>.</p>
        <p>Best regards,<br><strong>Abou Saleh Real Estate</strong></p>

        <hr class="divider">

        {{-- ══ ARABIC ════════════════════════════════════════════════ --}}
        <div class="section-ar">
            <p>عزيزي/عزيزتي <strong>{{ $invoice->contract->client->name ?? 'العميل' }}</strong>،</p>
            <p>تم استلام دفعتك بنجاح. يُرجى الاطلاع على الإيصال المرفق بهذا البريد.</p>

            <div class="info-box">
                <p><strong>رقم الفاتورة:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>المبلغ المدفوع:</strong> <span class="amount">${{ number_format($invoice->amount_paid ??
                        $invoice->amount, 2) }}</span></p>
                <p><strong>التاريخ:</strong> {{ $invoice->paid_at?->format('d M Y') ?? now()->format('d M Y') }}</p>
                <p><strong>الحالة:</strong> ✅ مدفوع</p>
            </div>

            <p>📎 الإيصال مرفق بهذا البريد للاحتفاظ به في سجلاتك.</p>
            <p>شكراً لك. لأي استفسار تواصل معنا على <a href="mailto:info@abousaleh.me">info@abousaleh.me</a>.</p>
            <p>مع تحياتنا،<br><strong>أبو صالح للعقارات</strong></p>
        </div>

        <div class="footer">
            This email contains confidential information. | هذا البريد يحتوي على معلومات سرية.
        </div>

    </div>
</body>

</html>