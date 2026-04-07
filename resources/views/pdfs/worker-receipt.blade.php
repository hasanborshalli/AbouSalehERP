<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    use App\Support\ArabicPdf;
    use App\Support\MoneyToWords;
    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    $signaturePath = public_path('img/abousaleh-signature.png');
    $signatureB64 = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : null;

    $numericAmount = (float) preg_replace('/[^0-9.]/', '', $amountNumbers);
    $arAmountWords = ArabicPdf::shape(MoneyToWords::ar($numericAmount, 'USD'));

    $arVoucher = ArabicPdf::shape('سند صرف');
    $arCompany = ArabicPdf::shape('أبو صالح للتجارة العامة');
    $arNo = ArabicPdf::shape('رقم الإيصال');
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
    $arNote1 = ArabicPdf::shape('يؤكد هذا السند إتمام الدفعة للمقاول');
    $arNote2 = ArabicPdf::shape('يرجى الاحتفاظ به للسجلات');

    $arPayeeName = ArabicPdf::shape($payeeName);
    $arForWhat1 = ArabicPdf::shape($forWhatAr1 ?? '');
    $arForWhat2 = ArabicPdf::shape($forWhatAr2 ?? '');
    $arForWhat3 = isset($forWhatAr3) && $forWhatAr3 ? ArabicPdf::shape($forWhatAr3) : null;
    $arPayMethod = ArabicPdf::shape(match($paymentMethod) {
    'cash' => 'نقداً',
    'cheque' => 'شيك',
    'bank_transfer' => 'تحويل بنكي',
    default => $paymentMethod,
    });
    @endphp

    @include('pdfs._arabic_font')

    <style>
        @@page {
            margin: 32px 40px;
        }

        body {
            font-family: 'Amiri', DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0b2545;
        }

        .watermark {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            text-align: center;
        }

        .watermark img {
            margin-top: 250px;
            width: 340px;
            opacity: 0.07;
        }

        .logo-top {
            text-align: center;
            margin-bottom: 8px;
        }

        .logo-top img {
            width: 110px;
        }

        .ar {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: inline-block;
            width: 100%;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .header-table td {
            padding: 0 4px;
            vertical-align: middle;
            font-size: 13px;
            font-weight: bold;
        }

        .voucher-bar {
            background: #1e3a5f;
            color: #fff;
            padding: 10px 14px;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0 16px;
            text-align: center;
        }

        .box {
            border: 1px solid #ccc;
            margin-bottom: 16px;
        }

        .bi {
            width: 100%;
            border-collapse: collapse;
        }

        .bi td {
            padding: 9px 14px;
            border-bottom: 1px solid #eee;
            width: 50%;
            vertical-align: top;
        }

        .bi tr:last-child td {
            border-bottom: none;
        }

        .en {
            text-align: left;
            direction: ltr;
        }

        .label {
            font-weight: bold;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 34px;
        }

        .sig-table td {
            vertical-align: top;
            width: 50%;
            padding: 0 6px;
        }

        .stamp-box {
            width: 80px;
            height: 80px;
            border: 1px solid #000;
            margin-top: 10px;
            display: inline-block;
        }

        .sig-img {
            height: 38px;
            max-width: 200px;
            display: block;
            margin-top: 4px;
        }

        .footer-line {
            margin-top: 24px;
            border-top: 3px solid #1e3a5f;
        }

        .info-note {
            font-size: 10px;
            color: #6b7280;
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px dashed #e5e7eb;
        }

        .field-lbl {
            font-weight: 700;
            font-size: 10.5px;
            color: #555;
            margin-bottom: 3px;
        }

        .field-val {
            font-size: 12px;
        }

        .ar-lbl {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: block;
            font-weight: bold;
            font-size: 10px;
            color: #555;
            margin-bottom: 1px;
        }

        .ar-val {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: block;
        }
    </style>
</head>

<body>
    @if($logoB64)<div class="watermark"><img src="data:image/png;base64,{{ $logoB64 }}" alt=""></div>@endif

    <div class="logo-top">
        @if($logoB64)<img src="data:image/png;base64,{{ $logoB64 }}" alt="Logo">@endif
    </div>

    <table class="header-table">
        <tr>
            <td>ABOU SALEH GENERAL TRADING</td>
            <td style="text-align:right;"><span class="ar">{{ $arCompany }}</span></td>
        </tr>
        <tr>
            <td style="font-size:11px;font-weight:normal;">Email: info@abousaleh.me</td>
            <td style="font-size:11px;font-weight:normal;text-align:right;">Tel: +961 71 999 219</td>
        </tr>
    </table>

    <div class="voucher-bar">PAYMENT VOUCHER &nbsp;|&nbsp; <span class="ar" style="display:inline;">{{ $arVoucher
            }}</span></div>

    <div class="box">
        <table class="bi">
            <tr>
                <td>
                    <div class="field-lbl">Receipt No</div>
                    <div class="field-val">{{ $receiptNo }}</div>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arNo }}</span>
                    <span class="ar-val">{{ $receiptNo }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Date</div>
                    <div class="field-val">{{ $date }}</div>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arDate }}</span>
                    <span class="ar-val">{{ $date }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Paid To</div>
                    <div class="field-val"><b>{{ $payeeName }}</b></div>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arPaidTo }}</span>
                    <span class="ar-val">{{ $arPayeeName }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">The Sum of</div>
                    <div class="field-val"><b>{{ $sumOf }}</b></div>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arSumOf }}</span>
                    <span class="ar-val">{{ $arAmountWords }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Amount</div>
                    <div class="field-val"><b>{{ $amountNumbers }}</b></div>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arAmount }}</span>
                    <span class="ar-val">{{ $amountNumbers }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">For</div>
                    <div class="field-val">{{ $forWhat }}</div>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arFor }}</span>
                    <span class="ar-val">{{ $arForWhat1 }}</span>
                    <span class="ar-val">{{ $arForWhat2 }}</span>
                    @if($arForWhat3)<span class="ar-val">{{ $arForWhat3 }}</span>@endif
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Payment Method</div>
                    <div class="field-val">{{ $paymentMethod }}</div>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arMethod }}</span>
                    <span class="ar-val">{{ $arPayMethod }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="sig-table">
        <tr>
            <td class="en">
                <span class="label">Paid by:</span> Abou Saleh General Trading<br>
                <span class="label">Authorised Signature:</span>
                @if($signatureB64)
                <img class="sig-img" src="data:image/png;base64,{{ $signatureB64 }}" alt="Sig">
                @endif
            </td>
            <td style="text-align:right;">
                <span class="ar">{{ $arCompany }} : {{ $arPaidBy }}</span><br>
                <span class="ar">{{ $arAuthSig }}</span>
            </td>
        </tr>
    </table>

    <div class="info-note">
        <span class="ar">{{ $arNote1 }}</span>
        <span class="ar">{{ $arNote2 }}</span>
    </div>
    <div class="footer-line"></div>
</body>

</html>