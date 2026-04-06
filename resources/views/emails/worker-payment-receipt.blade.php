<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payment Voucher | سند صرف</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.8;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
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

        .divider {
            border: none;
            border-top: 2px solid #e5e7eb;
            margin: 30px 0;
        }

        .section-ar {
            direction: rtl;
            text-align: right;
        }

        .amount {
            font-size: 18px;
            font-weight: 900;
            color: #1e3a5f;
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
        <p>Dear <strong>{{ $payment->contract->worker->name ?? 'Contractor' }}</strong>,</p>
        <p>A payment has been processed for your work contract with <strong>Abou Saleh General Trading</strong>.</p>

        <div class="info-box">
            <p><strong>Payment #:</strong> {{ $payment->installment_index }} of {{ $payment->contract->payment_months }}
            </p>
            <p><strong>Amount:</strong> <span class="amount">${{ number_format($payment->amount_paid ??
                    $payment->amount, 2) }}</span></p>
            <p><strong>Date paid:</strong> {{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d M
                Y') : '—' }}</p>
            <p><strong>Scope:</strong> {{ $payment->contract->scope_of_work }}</p>
        </div>

        <p>📎 The <strong>Payment Voucher (PDF)</strong> is attached to this email for your records.</p>
        <p style="margin-top:16px;">
            <a href="{{ url('/worker/payments') }}" class="btn">View all payments</a>
        </p>
        <p>Kind regards,<br><strong>Abou Saleh General Trading</strong><br>+961 71 999 219 | info@abousaleh.me</p>

        <hr class="divider">

        {{-- ══ ARABIC ════════════════════════════════════════════════ --}}
        <div class="section-ar">
            <p>عزيزي <strong>{{ $payment->contract->worker->name ?? 'المقاول' }}</strong>،</p>
            <p>تم معالجة دفعة لعقد عملك مع <strong>أبو صالح للتجارة العامة</strong>.</p>

            <div class="info-box">
                <p><strong>رقم الدفعة:</strong> {{ $payment->installment_index }} من {{
                    $payment->contract->payment_months }}</p>
                <p><strong>المبلغ:</strong> <span class="amount">${{ number_format($payment->amount_paid ??
                        $payment->amount, 2) }}</span></p>
                <p><strong>تاريخ الدفع:</strong> {{ $payment->paid_at ?
                    \Carbon\Carbon::parse($payment->paid_at)->format('d M Y') : '—' }}</p>
                <p><strong>نطاق العمل:</strong> {{ $payment->contract->scope_of_work }}</p>
            </div>

            <p>📎 <strong>سند الصرف (PDF)</strong> مرفق بهذا البريد للاحتفاظ به في سجلاتك.</p>
            <p style="margin-top:16px;">
                <a href="{{ url('/worker/payments') }}" class="btn">عرض جميع المدفوعات</a>
            </p>
            <p>مع تحياتنا،<br><strong>أبو صالح للتجارة العامة</strong><br>219 999 71 961+ | info@abousaleh.me</p>
        </div>

        <div class="footer">
            This email contains confidential information. | هذا البريد يحتوي على معلومات سرية.
        </div>

    </div>
</body>

</html>