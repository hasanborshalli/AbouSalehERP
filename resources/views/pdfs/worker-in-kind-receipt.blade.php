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

    $contract = $payment->workerContract;
    $worker = $contract->worker;

    $arTitle = ArabicPdf::shape('إيصال دفع عيني للعامل');
    $arCompany = ArabicPdf::shape('أبو صالح للعقارات');
    $arNo = ArabicPdf::shape('رقم الإيصال');
    $arDate = ArabicPdf::shape('التاريخ');
    $arTo = ArabicPdf::shape('صُرف إلى');
    $arFor = ArabicPdf::shape('وذلك بدل');
    $arMethod = ArabicPdf::shape('طريقة الدفع');
    $arInKind = ArabicPdf::shape('دفع عيني (مواد من المخزون)');
    $arItems = ArabicPdf::shape('البنود المسلَّمة');
    $arItem = ArabicPdf::shape('البند');
    $arQty = ArabicPdf::shape('الكمية');
    $arUnitPrice= ArabicPdf::shape('سعر الوحدة');
    $arTotal = ArabicPdf::shape('الإجمالي');
    $arTotalVal = ArabicPdf::shape('القيمة الإجمالية التقديرية');
    $arSig = ArabicPdf::shape('التوقيع المخوّل');
    $arNote1 = ArabicPdf::shape('يؤكد هذا الإيصال تسليم المواد أعلاه');
    $arNote2 = ArabicPdf::shape('للعامل المذكور بدلاً عن الدفعة النقدية.');
    $arWorkerName = ArabicPdf::shape($worker->name ?? '');

    $projectName = $contract->project?->name ?? '';
    $installment = $payment->workerPayment?->installment_index ?? '';

    $arForWhat1 = ArabicPdf::shape('دفعة رقم ' . $installment);
    $arForWhat2 = ArabicPdf::shape($contract->scope_of_work ?? '');
    $arForWhat3 = $projectName ? ArabicPdf::shape('مشروع: ' . $projectName) : null;
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

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .header-table td {
            font-size: 13px;
            font-weight: bold;
            padding: 0 4px;
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

        .ar {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: inline-block;
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

        .box {
            border: 1px solid #ccc;
            margin-bottom: 16px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }

        .items-table th {
            background: #1e3a5f;
            color: #fff;
            padding: 7px 10px;
            font-size: 11px;
        }

        .items-table td {
            padding: 6px 10px;
            font-size: 11px;
            border-bottom: 1px solid #e5e7eb;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .sig-table td {
            vertical-align: top;
            width: 50%;
            padding: 0 6px;
        }

        .sig-img {
            height: 38px;
            max-width: 200px;
            display: block;
            margin-top: 4px;
        }

        .info-note {
            font-size: 10px;
            color: #6b7280;
            margin-top: 10px;
            border-top: 1px dashed #e5e7eb;
            padding-top: 8px;
        }

        .footer-line {
            margin-top: 24px;
            border-top: 3px solid #1e3a5f;
        }
    </style>
</head>

<body>
    @if($logoB64)<div class="watermark"><img src="data:image/png;base64,{{ $logoB64 }}" alt=""></div>@endif
    <div class="logo-top">@if($logoB64)<img src="data:image/png;base64,{{ $logoB64 }}" alt="Logo">@endif</div>
    <table class="header-table">
        <tr>
            <td>ABOU SALEH REAL ESTATE</td>
            <td style="text-align:right;"><span class="ar">{{ $arCompany }}</span></td>
        </tr>
        <tr>
            <td style="font-size:11px;font-weight:normal;">Email: info@abousaleh.me</td>
            <td style="font-size:11px;font-weight:normal;text-align:right;">Tel: +961 71 999 219</td>
        </tr>
    </table>

    <div class="voucher-bar">
        WORKER IN-KIND PAYMENT RECEIPT | <span class="ar" style="display:inline;">{{ $arTitle }}</span>
    </div>

    <div class="box">
        <table class="bi">
            <tr>
                <td class="en"><span class="en-lbl">Receipt No:</span><span class="en-val">{{ $receiptNo }}</span></td>
                <td><span class="ar-lbl">{{ $arNo }}</span><span class="ar-val">{{ $receiptNo }}</span></td>
            </tr>
            <tr>
                <td class="en"><span class="en-lbl">Date:</span><span class="en-val">{{
                        \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</span></td>
                <td><span class="ar-lbl">{{ $arDate }}</span><span class="ar-val">{{
                        \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</span></td>
            </tr>
            <tr>
                <td class="en"><span class="en-lbl">Paid to (Worker):</span><span class="en-val">{{ $worker->name ?? '—'
                        }}</span></td>
                <td><span class="ar-lbl">{{ $arTo }}</span><span class="ar-val">{{ $arWorkerName }}</span></td>
            </tr>
            <tr>
                <td class="en"><span class="en-lbl">For:</span><span class="en-val">
                        Installment #{{ $installment }}
                        @if($contract->scope_of_work) — {{ $contract->scope_of_work }}@endif
                        @if($projectName) — {{ $projectName }}@endif
                    </span></td>
                <td>
                    <span class="ar-lbl">{{ $arFor }}</span>
                    <span class="ar-val">{{ $arForWhat1 }}</span>
                    <span class="ar-val">{{ $arForWhat2 }}</span>
                    @if($arForWhat3)<span class="ar-val">{{ $arForWhat3 }}</span>@endif
                </td>
            </tr>
            <tr>
                <td class="en"><span class="en-lbl">Payment method:</span><span class="en-val">In-Kind (Materials from
                        Stock)</span></td>
                <td><span class="ar-lbl">{{ $arMethod }}</span><span class="ar-val">{{ $arInKind }}</span></td>
            </tr>
        </table>
    </div>

    {{-- Items table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment->items as $i => $line)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $line->inventoryItem->name ?? '—' }}@if($line->inventoryItem->name_ar) / <span
                        style="font-family:'Amiri',sans-serif;direction:ltr;unicode-bidi:bidi-override;">{{
                        ArabicPdf::shape($line->inventoryItem->name_ar) }}</span>@endif</td>
                <td>{{ $line->quantity }} {{ $line->inventoryItem->unit ?? '' }}</td>
                <td>${{ number_format($line->unit_price_snapshot, 2) }}</td>
                <td>${{ number_format($line->total_value, 2) }}</td>
                <td>{{ $line->notes ?? '—' }}</td>
            </tr>
            @endforeach
            <tr style="font-weight:bold; background:#f0f4f8;">
                <td colspan="4" style="text-align:right;"><span class="ar" style="display:inline;">{{ $arTotalVal
                        }}</span> / Estimated Total:</td>
                <td colspan="2">${{ number_format($payment->total_estimated_value, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="sig-table">
        <tr>
            <td class="en">
                <strong>Issued by:</strong> Abou Saleh Real Estate<br>
                <strong>Authorised Signature:</strong>
                @if($signatureB64)<img class="sig-img" src="data:image/png;base64,{{ $signatureB64 }}" alt="Sig">@endif
            </td>
            <td style="text-align:right;">
                <span class="ar-lbl">{{ $arSig }}</span>
                <span class="ar-val">{{ $arCompany }}</span>
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