<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    use App\Support\ArabicPdf;
    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    $signaturePath = public_path('img/abousaleh-signature.png');
    $signatureB64 = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : null;

    $arVoucher = ArabicPdf::shape('سند صرف');
    $arReceiptNo = ArabicPdf::shape('رقم الإيصال');
    $arDate = ArabicPdf::shape('التاريخ');
    $arPaidTo = ArabicPdf::shape('مدفوع لـ (المقاول)');
    $arSumOf = ArabicPdf::shape('المبلغ بالكلمات');
    $arAmount = ArabicPdf::shape('المبلغ بالأرقام');
    $arFor = ArabicPdf::shape('وذلك بدل');
    $arMethod = ArabicPdf::shape('طريقة الدفع');
    $arCash = ArabicPdf::shape('نقداً');
    $arCheque = ArabicPdf::shape('شيك');
    $arTransfer = ArabicPdf::shape('تحويل بنكي');
    $arPaidBy = ArabicPdf::shape('دفعت بواسطة');
    $arAuthSig = ArabicPdf::shape('التوقيع المخوّل');
    $arCompany = ArabicPdf::shape('أبو صالح للتجارة العامة');
    $arNote = ArabicPdf::shape('يؤكد هذا السند إتمام الدفعة أعلاه للمقاول. يُرجى الاحتفاظ به للسجلات.');
    @endphp
    <style>
        @page {
            margin: 32px 40px;
        }

        body {
            font-family: 'Amiri', DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0b2545;
        }

        .watermark {
            position: fixed;
            left: 50%;
            top: 55%;
            transform: translate(-50%, -50%);
            width: 480px;
            opacity: 0.07;
            z-index: -1;
        }

        .logo-top {
            text-align: center;
            margin-bottom: 8px;
        }

        .logo-top img {
            width: 110px;
        }

        .header {
            text-align: center;
            margin-bottom: 6px;
        }

        .header .title-en {
            font-size: 13px;
            letter-spacing: 1px;
            font-weight: bold;
        }

        .header .title-ar {
            font-size: 13px;
            font-weight: bold;
            direction: rtl;
        }

        .contact-row {
            display: table;
            width: 100%;
            margin: 8px 0 14px;
        }

        .contact-en {
            display: table-cell;
            width: 50%;
            font-size: 11px;
        }

        .contact-ar {
            display: table-cell;
            width: 50%;
            font-size: 11px;
            text-align: right;
            direction: rtl;
        }

        .voucher-bar {
            background: #1e3a5f;
            color: #fff;
            padding: 10px 14px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 18px;
            text-align: center;
        }

        .bi {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .bi-en {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .bi-ar {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
            direction: rtl;
        }

        .label {
            font-weight: bold;
        }

        .val-line {
            border-bottom: 1px dotted #333;
            display: inline-block;
            min-width: 200px;
            padding-bottom: 2px;
            margin-left: 6px;
        }

        .wide-line {
            border-bottom: 1px dotted #333;
            display: block;
            width: 100%;
            padding-bottom: 2px;
            margin-top: 4px;
        }

        .payment-method {
            margin-top: 14px;
        }

        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            text-align: center;
            font-size: 10px;
            line-height: 12px;
            margin: 0 6px 0 14px;
        }

        .sig-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }

        .sig-en {
            display: table-cell;
            width: 55%;
            vertical-align: top;
        }

        .sig-ar {
            display: table-cell;
            width: 45%;
            vertical-align: top;
            text-align: right;
            direction: rtl;
        }

        .sig-img {
            position: relative;
            top: -8px;
            height: 40px;
            max-width: 220px;
            display: inline-block;
            vertical-align: bottom;
        }

        .stamp-box {
            width: 90px;
            height: 90px;
            border: 1px solid #000;
            margin-top: 16px;
            display: inline-block;
        }

        .footer-line {
            margin-top: 26px;
            border-top: 3px solid #1e3a5f;
        }

        .info-note {
            font-size: 10px;
            color: #6b7280;
            margin-top: 12px;
            border-top: 1px dashed #e5e7eb;
            padding-top: 8px;
        }
    </style>
</head>

<body>
    @if($logoB64)<img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif

    <div class="logo-top">
        @if($logoB64)<img src="data:image/png;base64,{{ $logoB64 }}" alt="Logo">@endif
    </div>

    <div class="header">
        <div class="title-en">ABOU SALEH GENERAL TRADING &nbsp;|&nbsp; <span class="title-ar">{{ $arCompany }}</span>
        </div>
    </div>

    <div class="contact-row">
        <div class="contact-en">Email: info@abousaleh.me</div>
        <div class="contact-ar">Tel: +961 71 999 219 &nbsp;|&nbsp; www.abousaleh.me</div>
    </div>

    <div class="voucher-bar">PAYMENT VOUCHER &nbsp;|&nbsp; {{ $arVoucher }}</div>

    <div class="bi">
        <div class="bi-en"><span class="label">Receipt No:</span><span class="val-line">{{ $receiptNo }}</span></div>
        <div class="bi-ar">{{ $arReceiptNo }}: {{ $receiptNo }}</div>
    </div>
    <div class="bi">
        <div class="bi-en"><span class="label">Date:</span><span class="val-line">{{ $date }}</span></div>
        <div class="bi-ar">{{ $arDate }}: {{ $date }}</div>
    </div>
    <div class="bi">
        <div class="bi-en"><span class="label">Paid To (Contractor):</span>
            <div class="wide-line">{{ $payeeName }}</div>
        </div>
        <div class="bi-ar">{{ $arPaidTo }}: <div class="wide-line">{{ $payeeName }}</div>
        </div>
    </div>
    <div class="bi">
        <div class="bi-en"><span class="label">The Sum of:</span>
            <div class="wide-line">{{ $sumOf }}</div>
        </div>
        <div class="bi-ar">{{ $arSumOf }}: <div class="wide-line">{{ $sumOf }}</div>
        </div>
    </div>
    <div class="bi">
        <div class="bi-en"><span class="label">Amount:</span>
            <div class="wide-line">{{ $amountNumbers }}</div>
        </div>
        <div class="bi-ar">{{ $arAmount }}: <div class="wide-line">{{ $amountNumbers }}</div>
        </div>
    </div>
    <div class="bi">
        <div class="bi-en"><span class="label">For:</span>
            <div class="wide-line">{{ $forWhat }}</div>
        </div>
        <div class="bi-ar">{{ $arFor }}: <div class="wide-line">{{ $forWhat }}</div>
        </div>
    </div>

    <div class="payment-method">
        <div class="bi">
            <div class="bi-en">
                <span class="label">Payment Method:</span>
                <span class="checkbox">{{ $paymentMethod === 'cash' ? '✓' : '' }}</span> Cash
                <span class="checkbox">{{ $paymentMethod === 'cheque' ? '✓' : '' }}</span> Cheque
                <span class="checkbox">{{ $paymentMethod === 'bank_transfer' ? '✓' : '' }}</span> Bank Transfer
            </div>
            <div class="bi-ar">
                {{ $arMethod }}: &nbsp;
                <span class="checkbox">{{ $paymentMethod === 'cash' ? '✓' : '' }}</span> {{ $arCash }}
                <span class="checkbox">{{ $paymentMethod === 'cheque' ? '✓' : '' }}</span> {{ $arCheque }}
                <span class="checkbox">{{ $paymentMethod === 'bank_transfer' ? '✓' : '' }}</span> {{ $arTransfer }}
            </div>
        </div>
    </div>

    <div class="sig-section">
        <div class="sig-en">
            <span class="label">Paid by (Company):</span> Abou Saleh General Trading<br><br>
            <span class="label">Authorised Signature:</span>
            @if($signatureB64)<img class="sig-img" src="data:image/png;base64,{{ $signatureB64 }}" alt="Sig">@endif
        </div>
        <div class="sig-ar">
            <div>{{ $arPaidBy }}: {{ $arCompany }}</div><br>
            <div>{{ $arAuthSig }}:</div>
            <div class="stamp-box"></div>
        </div>
    </div>

    <div class="info-note">
        This voucher confirms payment has been made to the contractor. &nbsp;|&nbsp; {{ $arNote }}
    </div>
    <div class="footer-line"></div>
</body>

</html>