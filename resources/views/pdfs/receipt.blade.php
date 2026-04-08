<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    use App\Support\ArabicPdf;
    use App\Support\MoneyToWords;

    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

    $numericAmount = (float) preg_replace('/[^0-9.]/', '', $amountNumbers);
    $arAmountWords = ArabicPdf::shape(MoneyToWords::ar($numericAmount, 'USD'));

    $arTitle = ArabicPdf::shape('إيصال استلام');
    $arCompany = ArabicPdf::shape('ابو صالح للتجارة العامة');
    $arNo = ArabicPdf::shape('رقم الإيصال');
    $arDate = ArabicPdf::shape('التاريخ');
    $arFrom = ArabicPdf::shape('استلمنا من');
    $arSum = ArabicPdf::shape('مبلغ وقدره');
    $arAmtNum = ArabicPdf::shape('المبلغ بالارقام');
    $arFor = ArabicPdf::shape('وذلك بدل');
    $arMethod = ArabicPdf::shape('طريقة الدفع');
    $arReceiver = ArabicPdf::shape('المستلم');
    $arNote = ArabicPdf::shape('هذا الإيصال يثبت استلام المبلغ المذكور أعلاه');

    $arFaqat = ArabicPdf::shape('فقط لا غير');
    $arReceivedFrom = ArabicPdf::shape($receivedFrom);
    $arForWhat1 = ArabicPdf::shape($forWhatAr1 ?? '');
    $arForWhat2 = isset($forWhatAr2) && $forWhatAr2 ? ArabicPdf::shape($forWhatAr2) : null;
    $arForWhat3 = isset($forWhatAr3) && $forWhatAr3 ? ArabicPdf::shape($forWhatAr3) : null;
    $arReceiverName = ArabicPdf::shape($receiverName);
    $arCompanyName = ArabicPdf::shape('ابو صالح للتجارة العامة');
    $arPayMethod = ArabicPdf::shape(match($paymentMethod) {
    'cash' => 'نقدا',
    'cheque' => 'شيك',
    'bank_transfer' => 'تحويل بنكي',
    default => $paymentMethod,
    });
    @endphp

    @include('pdfs._arabic_font')

    <style>
        @@page {
            margin: 30px 40px;
        }

        body {
            font-family: 'Amiri', DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #111;
        }

        /* Watermark centered on the page */
        .watermark {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            text-align: center;
        }

        .watermark img {
            margin-top: 220px;
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

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .header-table td {
            font-size: 13px;
            font-weight: bold;
            padding: 0 4px;
            vertical-align: middle;
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

        /* Arabic spans: ltr + bidi-override because utf8Glyphs pre-orders visually */
        .ar {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: block;
            width: 100%;
        }

        .ar-lbl {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: block;
            width: 100%;
            font-weight: bold;
            font-size: 11px;
            color: #555;
            margin-bottom: 2px;
        }

        .ar-val {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: block;
            width: 100%;
            font-size: 13px;
        }

        .box {
            border: 2px solid #111;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .bi {
            width: 100%;
            border-collapse: collapse;
        }

        .bi td {
            padding: 10px 14px;
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

        .en-lbl {
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
            color: #555;
            font-size: 11px;
        }

        .en-val {
            display: block;
        }

        .amount-val {
            font-size: 16px;
            font-weight: bold;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .footer-table td {
            vertical-align: top;
            width: 50%;
            padding: 0 8px;
        }

        .sig-box {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 12px;
            height: 130px;
            page-break-inside: avoid;
        }

        .sig-title {
            font-weight: 800;
            margin-bottom: 8px;
        }

        .sig-wrap {
            page-break-inside: avoid;
        }

        .k {
            font-weight: 700;
            display: inline-block;
            width: 72px;
        }

        .note {
            font-size: 10px;
            color: #777;
            margin-top: 16px;
            border-top: 1px dashed #ccc;
            padding-top: 8px;
            text-align: center;
        }
    </style>
</head>

<body>
    {{-- Watermark centered on full page --}}
    @if($logoB64)
    <div class="watermark">
        <img src="data:image/png;base64,{{ $logoB64 }}" alt="">
    </div>
    @endif

    <div class="logo-top">@if($logoB64)<img src="data:image/png;base64,{{ $logoB64 }}" alt="Logo">@endif</div>
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
    <div class="voucher-bar">
        RECEIPT &nbsp;|&nbsp; <span style="font-family:'Amiri',sans-serif;direction:ltr;unicode-bidi:bidi-override;">{{
            $arTitle }}</span>
    </div>

    {{-- Data box --}}
    <div class="box">
        <table class="bi">
            {{-- No --}}
            <tr>
                <td class="en">
                    <span class="en-lbl">No:</span>
                    <span class="en-val">{{ $receiptNo }}</span>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arNo }}</span>
                    <span class="ar-val">{{ $receiptNo }}</span>
                </td>
            </tr>
            {{-- Date --}}
            <tr>
                <td class="en">
                    <span class="en-lbl">Date:</span>
                    <span class="en-val">{{ $date }}</span>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arDate }}</span>
                    <span class="ar-val">{{ $date }}</span>
                </td>
            </tr>
            {{-- Received from --}}
            <tr>
                <td class="en">
                    <span class="en-lbl">Received from:</span>
                    <span class="en-val">{{ $receivedFrom }}</span>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arFrom }}</span>
                    <span class="ar-val">{{ $arReceivedFrom }}</span>
                </td>
            </tr>
            {{-- Sum of --}}
            <tr>
                <td class="en">
                    <span class="en-lbl">Sum of:</span>
                    <span class="en-val amount-val">{{ $sumOf }}</span>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arSum }}</span>
                    <span class="ar-val" style="font-size:12px;">{{ $arAmountWords }}</span>
                    <span class="ar-val" style="font-size:12px; color:#555;">{{ $arFaqat }}</span>
                </td>
            </tr>
            {{-- Amount --}}
            <tr>
                <td class="en">
                    <span class="en-lbl">Amount:</span>
                    <span class="en-val amount-val">{{ $amountNumbers }}</span>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arAmtNum }}</span>
                    <span class="ar-val amount-val">{{ $amountNumbers }}</span>
                </td>
            </tr>
            {{-- For --}}
            <tr>
                <td class="en">
                    <span class="en-lbl">For:</span>
                    <span class="en-val">{{ $forWhat }}</span>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arFor }}</span>
                    <span class="ar-val">{{ $arForWhat1 }}</span>
                    @if($arForWhat2)<span class="ar-val">{{ $arForWhat2 }}</span>@endif
                    @if($arForWhat3)<span class="ar-val">{{ $arForWhat3 }}</span>@endif
                </td>
            </tr>
            {{-- Payment method --}}
            <tr>
                <td class="en">
                    <span class="en-lbl">Payment method:</span>
                    <span class="en-val">{{ ucfirst($paymentMethod) }}</span>
                </td>
                <td>
                    <span class="ar-lbl">{{ $arMethod }}</span>
                    <span class="ar-val">{{ $arPayMethod }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="sig-wrap" style="margin-top:20px;">
        <table style="border:0;border-collapse:collapse;width:100%;">
            <tr>
                <td style="width:50%;padding-right:10px;border:0;vertical-align:top;">
                    <div class="sig-box">
                        <div class="sig-title">Client</div>
                        <div><b>Name:</b> {{ $receivedFrom }}</div>
                        <div style="margin-top:26px;"><span class="k">Signature:</span> ____________________________
                        </div>
                        <div style="margin-top:10px;"><span class="k">Date:</span> ____ / ____ / ______</div>
                    </div>
                </td>
                <td style="width:50%;padding-left:10px;border:0;vertical-align:top;">
                    <div class="sig-box">
                        <div class="sig-title">Company</div>
                        <div><b>Company:</b> Abou Saleh General Trading</div>
                        <div><b>Representative:</b> ____________________________</div>
                        <div style="margin-top:10px;"><span class="k">Signature:</span> ____________________________
                        </div>
                        <div style="margin-top:10px;"><span class="k">Date:</span> ____ / ____ / ______</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="note"><span class="ar" style="display:inline;">{{ $arNote }}</span></div>
</body>

</html>