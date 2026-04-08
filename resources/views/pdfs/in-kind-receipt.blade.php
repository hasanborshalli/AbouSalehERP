<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    use App\Support\ArabicPdf;
    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    $contract = $payment->contract;
    $client = $contract->client;

    $arTitle = ArabicPdf::shape('إيصال دفع عيني');
    $arCompany = ArabicPdf::shape('أبو صالح للتجارة العامة');
    $arNo = ArabicPdf::shape('رقم الإيصال');
    $arDate = ArabicPdf::shape('التاريخ');
    $arFrom = ArabicPdf::shape('استلمنا من');
    $arFor = ArabicPdf::shape('وذلك بدل');
    $arMethod = ArabicPdf::shape('طريقة الدفع');
    $arInKind = ArabicPdf::shape('دفع عيني (مواد من المخزون)');
    $arItems = ArabicPdf::shape('البنود المستلمة');
    $arItem = ArabicPdf::shape('البند');
    $arQty = ArabicPdf::shape('الكمية');
    $arUnitPrice= ArabicPdf::shape('سعر الوحدة');
    $arTotal = ArabicPdf::shape('الإجمالي');
    $arNotes = ArabicPdf::shape('ملاحظات');
    $arTotalVal = ArabicPdf::shape('القيمة الإجمالية التقديرية');
    $arRecBy = ArabicPdf::shape('استلمت بواسطة');
    $arSig = ArabicPdf::shape('التوقيع المخوّل');
    $arNote1 = ArabicPdf::shape('يؤكد هذا الإيصال استلام المواد أعلاه');
    $arNote2 = ArabicPdf::shape('من العميل كدفعة.');
    $arNote3 = ArabicPdf::shape('تحتفظ الشركة بالحق في إعادة التقييم.');
    $arClientName = ArabicPdf::shape($client->name ?? '');

    $projectNameAr = $contract->project ? ($contract->project->name_ar ?? $contract->project->name) : '';
    $aptUnit = $contract->apartment->unit_number ?? '';
    if ($payment->invoice) {
    $arForWhat = ArabicPdf::shape('فاتورة رقم ' . $payment->invoice->invoice_number . ' - شقة ' . $aptUnit);
    $arForWhat2 = $projectNameAr ? ArabicPdf::shape($projectNameAr) : null;
    } else {
    $arForWhat = ArabicPdf::shape('دفع عيني - شقة ' . $aptUnit);
    $arForWhat2 = $projectNameAr ? ArabicPdf::shape($projectNameAr) : null;
    }
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

        .items-table .ar-th {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
        }

        .badge-inkind {
            display: inline-block;
            background: #1e3a5f;
            color: #fff;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
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
            <td>ABOU SALEH GENERAL TRADING</td>
            <td style="text-align:right;"><span class="ar">{{ $arCompany }}</span></td>
        </tr>
        <tr>
            <td style="font-size:11px;font-weight:normal;">Email: info@abousaleh.me</td>
            <td style="font-size:11px;font-weight:normal;text-align:right;">Tel: +961 71 999 219</td>
        </tr>
    </table>

    <div class="voucher-bar">
        IN-KIND PAYMENT RECEIPT | <span class="ar" style="display:inline;">{{ $arTitle }}</span>
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
                <td class="en"><span class="en-lbl">Received from:</span><span class="en-val">{{ $client->name ?? '—'
                        }}</span></td>
                <td><span class="ar-lbl">{{ $arFrom }}</span><span class="ar-val">{{ $arClientName }}</span></td>
            </tr>
            <tr>
                <td class="en"><span class="en-lbl">For:</span><span class="en-val">
                        @if($payment->invoice) Invoice #{{ $payment->invoice->invoice_number }} — Apt {{
                        $contract->apartment->unit_number ?? '' }} — {{ $contract->project->name ?? '' }}
                        @else Full purchase (in-kind) — Apt {{ $contract->apartment->unit_number ?? '' }} — {{
                        $contract->project->name ?? '' }}
                        @endif
                    </span></td>
                <td>
                    <span class="ar-lbl">{{ $arFor }}</span>
                    <span class="ar-val">{{ $arForWhat }}</span>
                    @if($arForWhat2)<span class="ar-val">{{ $arForWhat2 }}</span>@endif
                </td>
            </tr>
            <tr>
                <td class="en"><span class="en-lbl">Payment method:</span><span class="en-val">In-Kind (Inventory
                        Items)</span></td>
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

    <div class="sig-wrap" style="margin-top:20px;">
        <table style="border:0;border-collapse:collapse;width:100%;">
            <tr>
                <td style="width:50%;padding-right:10px;border:0;vertical-align:top;">
                    <div class="sig-box">
                        <div class="sig-title">Client</div>
                        <div><b>Name:</b> {{ $client->name ?? '—' }}</div>
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

    <div class="info-note">
        <span class="ar">{{ $arNote1 }}</span>
        <span class="ar">{{ $arNote2 }}</span>
        <span class="ar">{{ $arNote3 }}</span>
    </div>
    <div class="footer-line"></div>
</body>

</html>