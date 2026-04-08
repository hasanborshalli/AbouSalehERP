<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    use App\Support\ArabicPdf;
    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    // Bilingual Arabic
    $arDocTitle = ArabicPdf::shape('عقد بيع عقار');
    $arCompanyAr = ArabicPdf::shape('ابو صالح للتجارة العامة');
    $arClientUnit = ArabicPdf::shape('بيانات العميل والوحدة');
    $arPaySummary = ArabicPdf::shape('ملخص الدفع');
    $arSignatures = ArabicPdf::shape('التوقيعات');
    $arTerms = ArabicPdf::shape('الشروط والأحكام');
    $arLblSeller = ArabicPdf::shape('البائع / الشركة');
    $arLblClient = ArabicPdf::shape('العميل');
    $arLblPhone = ArabicPdf::shape('الهاتف');
    $arLblProject = ArabicPdf::shape('المشروع');
    $arLblApt = ArabicPdf::shape('الشقة');
    $arLblTotal = ArabicPdf::shape('السعر الإجمالي');
    $arLblDiscount= ArabicPdf::shape('الخصم');
    $arLblFinal = ArabicPdf::shape('السعر النهائي');
    $arLblDown = ArabicPdf::shape('الدفعة الاولى');
    $arLblMonths = ArabicPdf::shape('عدد الاقساط');
    $arLblMonthly = ArabicPdf::shape('القسط الشهري');
    $arLblStart = ArabicPdf::shape('تاريخ بدء الدفع');
    $arLblLateFee = ArabicPdf::shape('رسوم التاخير');

    $arClientName = ArabicPdf::shape($contract->client->name ?? '');
    $arProjectName = ArabicPdf::shape($contract->project->name_ar ?? $contract->project->name ?? '-');
    $arInstMonths = ArabicPdf::shape($contract->installment_months . ' شهر');
    $arLateFeeVal = $contract->late_fee > 0
    ? ArabicPdf::shape('USD $' . number_format($contract->late_fee, 2))
    : ArabicPdf::shape('لا يوجد');

    $arTermsList = [
    ArabicPdf::shape('يجب سداد جميع المدفوعات وفق الجدول الزمني المتفق عليه.'),
    ArabicPdf::shape('الدفعة الاولى غير قابلة للاسترداد ما لم ينص على خلاف ذلك كتابيا.'),
    ArabicPdf::shape('تخضع المدفوعات المتاخرة لرسوم التاخير المذكورة اعلاه (ان وجدت).'),
    ArabicPdf::shape('اي تعديل على هذا العقد يجب ان يكون كتابيا وموقعا من الطرفين.'),
    ArabicPdf::shape('يخضع هذا العقد للقوانين واللوائح المحلية المعمول بها.'),
    ArabicPdf::shape('يقر الطرفان بانهما قرا ووافقا على جميع الشروط الواردة هنا.'),
    ];
    @endphp
    @include('pdfs._arabic_font')
    <style>
        @page {
            margin: 26px 22px 55px 22px;
        }

        .sig-signature-img {
            height: 42px;
            max-width: 220px;
            display: inline-block;
            vertical-align: middle;
            margin-left: 8px;
        }

        body {
            font-family: 'Amiri', DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 0;
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
            opacity: 0.06;
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
            margin: 10px 0 14px;
            text-align: center;
        }

        .muted {
            color: #6b7280;
        }

        .small {
            font-size: 10.5px;
        }

        .h2 {
            font-size: 13px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .h2-ar {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            font-size: 13px;
            font-weight: 700;
            margin: 0 0 8px;
            display: block;
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
            font-weight: bold;
            font-size: 10.5px;
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

        .field-lbl {
            font-weight: 700;
            font-size: 10.5px;
            color: #555;
            margin-bottom: 3px;
        }

        .field-val {
            font-size: 12px;
        }

        .line {
            height: 1px;
            background: #e5e7eb;
            margin: 12px 0;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            page-break-inside: avoid;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .info td {
            padding: 8px 10px;
            border-bottom: 1px solid #eef2f7;
            vertical-align: top;
            width: 50%;
        }

        .info tr:last-child td {
            border-bottom: 0;
        }

        .info .final td {
            background: #f1f5f9;
            font-weight: 700;
        }

        .notes {
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .money th,
        .money td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            vertical-align: top;
        }

        .money th {
            text-align: left;
            background: #f8fafc;
            font-weight: 700;
        }

        .money td {
            text-align: right;
        }

        .money .final th,
        .money .final td {
            background: #f1f5f9;
            font-weight: 800;
        }

        .sig-wrap {
            page-break-inside: avoid;
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

        .k {
            font-weight: 700;
            display: inline-block;
            width: 72px;
        }

        .sp-10 {
            height: 10px;
        }

        .sp-12 {
            height: 12px;
        }

        .terms {
            page-break-inside: avoid;
        }

        .terms li {
            margin: 0 0 6px;
        }
    </style>
</head>

<body>
    @if($logoB64)<div class="watermark"><img src="data:image/png;base64,{{ $logoB64 }}" alt=""></div>@endif

    <!-- HEADER -->
    <div class="logo-top">
        @if($logoB64)<img src="data:image/png;base64,{{ $logoB64 }}" alt="Logo">@endif
    </div>
    <table class="header-table">
        <tr>
            <td>ABOU SALEH GENERAL TRADING</td>
            <td style="text-align:right;">
                <span style="font-family:'Amiri',sans-serif;direction:ltr;unicode-bidi:bidi-override;">{{ $arCompanyAr
                    }}</span>
            </td>
        </tr>
        <tr>
            <td style="font-size:11px;font-weight:normal;">Email: info@abousaleh.me</td>
            <td style="font-size:11px;font-weight:normal;text-align:right;">Tel: +961 71 999 219</td>
        </tr>
    </table>
    <div class="voucher-bar">
        SALES CONTRACT &nbsp;|&nbsp;
        <span style="font-family:'Amiri',sans-serif;direction:ltr;unicode-bidi:bidi-override;">{{ $arDocTitle }}</span>
    </div>

    <div class="muted small" style="margin-bottom:10px;">
        Contract ID: <b>#{{ $contract->id }}</b> &nbsp;•&nbsp;
        Date: <b>{{ $contract->contract_date }}</b>
        &nbsp;•&nbsp; Generated: <b>{{ ($generatedAt ?? now())->timezone('Asia/Beirut')->format('Y-m-d H:i') }}</b>
    </div>

    <!-- CLIENT / UNIT -->
    <div class="card">
        <table style="width:100%;border:0;border-collapse:collapse;margin-bottom:4px;">
            <tr>
                <td style="border:0;">
                    <div class="h2" style="margin-bottom:0;">Client & Unit Details</div>
                </td>
                <td style="border:0;text-align:right;"><span class="h2-ar">{{ $arClientUnit }}</span></td>
            </tr>
        </table>
        <table class="info">
            <tr>
                <td>
                    <div class="field-lbl">Seller / Company</div>
                    <div class="field-val">Abou Saleh General Trading</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblSeller }}</span>
                    <span class="ar-val">{{ $arCompanyAr }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Client</div>
                    <div class="field-val"><b>{{ $contract->client->name }}</b></div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblClient }}</span>
                    <span class="ar-val">{{ $arClientName }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Phone</div>
                    <div class="field-val">{{ $contract->client->phone ?? '—' }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblPhone }}</span>
                    <span class="ar-val">{{ $contract->client->phone ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Project</div>
                    <div class="field-val">{{ $contract->project->name ?? '—' }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblProject }}</span>
                    <span class="ar-val">{{ $arProjectName }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Apartment</div>
                    <div class="field-val">{{ $contract->apartment->unit_number ?? $contract->apartment->unit_code ??
                        '—' }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblApt }}</span>
                    <span class="ar-val">{{ $contract->apartment->unit_number ?? $contract->apartment->unit_code ?? '—'
                        }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="sp-10"></div>

    <!-- PAYMENT SUMMARY -->
    <div class="card">
        <table style="width:100%;border:0;border-collapse:collapse;margin-bottom:4px;">
            <tr>
                <td style="border:0;">
                    <div class="h2" style="margin-bottom:0;">Payment Summary</div>
                </td>
                <td style="border:0;text-align:right;"><span class="h2-ar">{{ $arPaySummary }}</span></td>
            </tr>
        </table>
        <table class="info">
            <tr>
                <td>
                    <div class="field-lbl">Total Price</div>
                    <div class="field-val">USD ${{ number_format($contract->total_price, 2) }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblTotal }}</span>
                    <span class="ar-val">USD ${{ number_format($contract->total_price, 2) }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Discount</div>
                    <div class="field-val">USD ${{ number_format($contract->discount, 2) }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblDiscount }}</span>
                    <span class="ar-val">USD ${{ number_format($contract->discount, 2) }}</span>
                </td>
            </tr>
            <tr class="final">
                <td>
                    <div class="field-lbl">Final Price</div>
                    <div class="field-val"><b>USD ${{ number_format($contract->final_price, 2) }}</b></div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblFinal }}</span>
                    <span class="ar-val">USD ${{ number_format($contract->final_price, 2) }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Down Payment</div>
                    <div class="field-val">USD ${{ number_format($contract->down_payment, 2) }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblDown }}</span>
                    <span class="ar-val">USD ${{ number_format($contract->down_payment, 2) }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Installment Period</div>
                    <div class="field-val">{{ $contract->installment_months }} months</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblMonths }}</span>
                    <span class="ar-val">{{ $arInstMonths }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Monthly Installment</div>
                    <div class="field-val"><b>USD ${{ number_format($contract->installment_amount, 2) }}</b></div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblMonthly }}</span>
                    <span class="ar-val">USD ${{ number_format($contract->installment_amount, 2) }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Payment Start Date</div>
                    <div class="field-val">{{ $contract->payment_start_date }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblStart }}</span>
                    <span class="ar-val">{{ $contract->payment_start_date }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Late Fee (per delay)</div>
                    <div class="field-val">
                        @if($contract->late_fee > 0) USD ${{ number_format($contract->late_fee, 2) }}
                        @else No late fee @endif
                    </div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblLateFee }}</span>
                    <span class="ar-val">{{ $arLateFeeVal }}</span>
                </td>
            </tr>
        </table>

        @if($contract->notes)
        <div class="notes">
            <div style="font-weight:800;margin-bottom:6px;">Notes</div>
            <div>{{ $contract->notes }}</div>
        </div>
        @endif

        @if($contract->payment_type === 'in_kind')
        <div class="notes" style="border-left:3px solid #1e3a5f;margin-top:12px;">
            <div style="font-weight:800;margin-bottom:6px;color:#1e3a5f;">⚠ In-Kind Payment Agreement</div>
            <div>This contract is settled by delivery of inventory items (in-kind) instead of cash payments.
                No installment invoices will be issued. Items agreed upon are listed below.</div>
            @if($contract->in_kind_notes)
            <div style="margin-top:6px;"><strong>Details:</strong> {{ $contract->in_kind_notes }}</div>
            @endif
        </div>
        @php $ikp = $contract->inKindPayments()->with('items.inventoryItem')->first(); @endphp
        @if($ikp && $ikp->items->count())
        <table class="money" style="margin-top:10px;">
            <tr>
                <th style="width:40%">Item</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Value</th>
            </tr>
            @foreach($ikp->items as $line)
            <tr>
                <td>{{ $line->inventoryItem->name ?? '—' }}</td>
                <td>{{ $line->quantity }} {{ $line->inventoryItem->unit ?? '' }}</td>
                <td>USD ${{ number_format($line->unit_price_snapshot, 2) }}</td>
                <td>USD ${{ number_format($line->total_value, 2) }}</td>
            </tr>
            @endforeach
            <tr class="final">
                <th colspan="3">Estimated Total Value</th>
                <td>USD ${{ number_format($ikp->total_estimated_value, 2) }}</td>
            </tr>
        </table>
        @endif
        @endif
    </div>

    <div class="sp-12"></div>

    <!-- SIGNATURES -->
    <div class="sig-wrap">
        <table style="width:100%;border:0;border-collapse:collapse;margin-bottom:4px;">
            <tr>
                <td style="border:0;">
                    <div class="h2" style="margin-bottom:0;">Signatures</div>
                </td>
                <td style="border:0;text-align:right;"><span class="h2-ar">{{ $arSignatures }}</span></td>
            </tr>
        </table>
        <table style="border:0;">
            <tr>
                <td style="width:50%;padding-right:10px;border:0;vertical-align:top;">
                    <div class="sig-box">
                        <div class="sig-title">Client</div>
                        <div><b>Name:</b> {{ $contract->client->name }}</div>
                        <div style="margin-top:26px;"><span class="k">Signature:</span> ____________________________
                        </div>
                        <div style="margin-top:10px;"><span class="k">Date:</span> ____ / ____ / ______</div>
                    </div>
                </td>
                <td style="width:50%;padding-left:10px;border:0;vertical-align:top;">
                    <div class="sig-box">
                        <div class="sig-title">Seller / Company</div>
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

    <div class="sp-12"></div>

    <!-- TERMS -->
    <div class="card terms">
        <table style="width:100%;border:0;border-collapse:collapse;margin-bottom:4px;">
            <tr>
                <td style="border:0;">
                    <div class="h2" style="margin-bottom:0;">Terms & Conditions</div>
                </td>
                <td style="border:0;text-align:right;"><span class="h2-ar">{{ $arTerms }}</span></td>
            </tr>
        </table>
        <table style="width:100%;border:0;border-collapse:collapse;">
            <tr>
                <td style="border:0;vertical-align:top;width:50%;padding-right:8px;">
                    <ol class="small">
                        <li>All payments must be made according to the agreed schedule stated in this contract.</li>
                        <li>Down payment is non-refundable unless otherwise stated in writing.</li>
                        <li>Late payments are subject to the late fee mentioned above (if applicable).</li>
                        <li>Any amendment to this contract must be made in writing and signed by both parties.</li>
                        <li>This contract is governed by applicable local laws and regulations.</li>
                        <li>Both parties acknowledge they have read and accepted all terms herein.</li>
                    </ol>
                </td>
                <td style="border:0;vertical-align:top;width:50%;padding-left:8px;">
                    <table style="width:100%;border-collapse:collapse;font-family:'Amiri',sans-serif;font-size:11px;">
                        @foreach($arTermsList as $i => $term)
                        <tr>
                            <td
                                style="direction:ltr;unicode-bidi:bidi-override;text-align:right;padding:0 6px 4px 0;vertical-align:top;">
                                {{ $term }}</td>
                            <td
                                style="width:22px;text-align:right;padding:0 0 4px 4px;vertical-align:top;font-weight:700;">
                                .{{ $i + 1 }}</td>
                        </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $left = "Abou Saleh General Trading • info@abousaleh.me";
            $pdf->page_text(22, 825, $left, null, 9, array(0.42,0.45,0.50));
            $pdf->page_text(500, 825, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 9, array(0.42,0.45,0.50));
        }
    </script>

</body>

</html>