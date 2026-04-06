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

    // Bilingual Arabic additions
    $arDocTitle = ArabicPdf::shape('عقد عمل');
    $arCompanyAr = ArabicPdf::shape('أبو صالح للتجارة العامة');
    $arParties = ArabicPdf::shape('اطراف العقد');
    $arWorkDetails = ArabicPdf::shape('تفاصيل العمل');
    $arPaySchedule = ArabicPdf::shape('جدول الدفع');
    $arSig = ArabicPdf::shape('التوقيعات');
    $arTerms = ArabicPdf::shape('الشروط والاحكام');
    $arLblWorker = ArabicPdf::shape('المقاول / العامل');
    $arLblPhone = ArabicPdf::shape('الهاتف');
    $arLblScope = ArabicPdf::shape('نطاق العمل');
    $arLblTotal = ArabicPdf::shape('المبلغ الاجمالي');
    $arLblMonthly = ArabicPdf::shape('الدفعة الشهرية');
    $arLblMonths = ArabicPdf::shape('عدد الاقساط');
    $arLblFirstDate = ArabicPdf::shape('تاريخ اول دفعة');
    $arWorkerName = ArabicPdf::shape($contract->worker->name ?? '');
    $arLblCompany = ArabicPdf::shape('الشركة (صاحب العمل)');
    $arLblWorker = ArabicPdf::shape('المقاول / العامل');
    $arLblEmail = ArabicPdf::shape('البريد الإلكتروني');
    $arLblProjects = ArabicPdf::shape('المشاريع');
    $arLblUnits = ArabicPdf::shape('الوحدات');
    $arLblManagedP = ArabicPdf::shape('العقارات المدارة');
    $arLblDesc = ArabicPdf::shape('الوصف');
    $arLblCategory = ArabicPdf::shape('الفئة');
    $arLblStartDate = ArabicPdf::shape('تاريخ البداية');
    $arLblEndDate = ArabicPdf::shape('تاريخ الانتهاء المتوقع');
    $arCompanyName = ArabicPdf::shape('أبو صالح للتجارة العامة');
    $arScopeOfWork = ArabicPdf::shape($contract->scope_of_work ?? '');
    $arWorkerTerms = [
    ArabicPdf::shape('يلتزم المقاول بانجاز العمل بمستوى مهني عالٍ.'),
    ArabicPdf::shape('المقاول مسؤول عن ادواته ومعداته وسلامته في موقع العمل.'),
    ArabicPdf::shape('يجب اكتمال العمل ضمن الجدول الزمني المتفق عليه.'),
    ArabicPdf::shape('اي تعديل يجب ان يكون كتابيا وموقعا من الطرفين.'),
    ArabicPdf::shape('يخضع هذا العقد للقوانين المحلية المعمول بها.'),
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
            left: 50%;
            top: 52%;
            transform: translate(-50%, -50%);
            width: 500px;
            opacity: 0.06;
            z-index: -1;
        }

        .muted {
            color: #6b7280;
        }

        .small {
            font-size: 10.5px;
        }

        .h1 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
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

        .h2-ar {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            font-size: 13px;
            font-weight: 700;
            display: block;
        }

        .h2 {
            font-size: 13px;
            font-weight: 700;
            margin: 0 0 8px;
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

        .field-lbl {
            font-weight: 700;
            font-size: 10.5px;
            color: #555;
            margin-bottom: 3px;
        }

        .field-val {
            font-size: 12px;
        }

        .money th,
        .money td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            vertical-align: top;
        }

        .money th {
            width: 40%;
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

        .schedule th,
        .schedule td {
            border: 1px solid #e5e7eb;
            padding: 7px 10px;
            font-size: 11px;
        }

        .schedule th {
            background: #f8fafc;
            font-weight: 700;
            text-align: left;
        }

        .notes {
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .header td {
            vertical-align: top;
        }

        .logo {
            width: 110px;
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

        .badge-cat {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 10px;
            font-weight: 700;
        }

        /* Arabic text shaping — direction:ltr because utf8Glyphs pre-orders visually */
        .ar {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: inline-block;
            width: 100%;
        }
    </style>
</head>

<body>
    @if($logoB64)
    <img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="">
    @endif

    <!-- HEADER -->
    <table class="header" style="margin-bottom:10px;">
        <tr>
            <td style="padding-right:10px;">
                <div class="h1">Service / Work Contract</div>
                <div
                    style="font-family:'Amiri',sans-serif;direction:ltr;unicode-bidi:bidi-override;text-align:center;font-size:16px;font-weight:bold;margin-top:4px;">
                    {{ $arDocTitle }}</div>
                <div class="muted small" style="margin-top:2px;">
                    Contract ID: <b>#{{ $contract->id }}</b> &nbsp;•&nbsp;
                    Date: <b>{{ $contract->contract_date->format('Y-m-d') }}</b><br>
                    Generated: <b>{{ now()->timezone('Asia/Beirut')->format('Y-m-d H:i') }}</b>
                </div>
            </td>
            <td style="text-align:right;">
                @if($logoB64)
                <img src="data:image/png;base64,{{ $logoB64 }}" class="logo" alt="Logo">
                @endif
            </td>
        </tr>
    </table>

    <div class="line"></div>

    <!-- PARTIES -->
    <div class="card">
        <table style="width:100%;border:0;border-collapse:collapse;margin-bottom:4px;">
            <tr>
                <td style="border:0;">
                    <div class="h2" style="margin-bottom:0;">Contracting Parties</div>
                </td>
                <td style="border:0;text-align:right;"><span class="h2-ar">{{ $arParties }}</span></td>
            </tr>
        </table>
        @php
        $linkedProjects = \App\Models\Project::whereIn('id', $contract->allProjectIds())->get();
        $linkedApartments = \App\Models\Apartment::whereIn('id', $contract->allApartmentIds())->with('project')->get();
        $linkedManagedProps = \App\Models\ManagedProperty::whereIn('id', $contract->allManagedPropertyIds())->get();
        $projectCosts = $contract->project_costs ?? [];
        $apartmentCosts = $contract->apartment_costs ?? [];
        $managedPropertyCosts = $contract->managed_property_costs ?? [];
        @endphp
        <table class="info">
            <tr>
                <td>
                    <div class="field-lbl">Company (Employer)</div>
                    <div class="field-val">Abou Saleh General Trading</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblCompany }}</span>
                    <span class="ar-val">{{ $arCompanyName }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Contractor / Worker</div>
                    <div class="field-val"><b>{{ $contract->worker->name }}</b></div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblWorker }}</span>
                    <span class="ar-val">{{ $arWorkerName }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Phone</div>
                    <div class="field-val">{{ $contract->worker->phone ?? '—' }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblPhone }}</span>
                    <span class="ar-val">{{ $contract->worker->phone ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Email</div>
                    <div class="field-val">{{ $contract->worker->email ?? '—' }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblEmail }}</span>
                    <span class="ar-val">{{ $contract->worker->email ?? '—' }}</span>
                </td>
            </tr>
            @if($linkedProjects->isNotEmpty())
            <tr>
                <td>
                    <div class="field-lbl">Project(s)</div>
                    <div class="field-val">@foreach($linkedProjects as $proj){{ $proj->name }}@if($proj->code) ({{
                        $proj->code }})@endif @if(!$loop->last), @endif @endforeach</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblProjects }}</span>
                    <span class="ar-val">@foreach($linkedProjects as $proj){{ ArabicPdf::shape($proj->name)
                        }}@if(!$loop->last)، @endif @endforeach</span>
                </td>
            </tr>
            @endif
            @if($linkedApartments->isNotEmpty())
            <tr>
                <td>
                    <div class="field-lbl">Unit(s)</div>
                    <div class="field-val">@foreach($linkedApartments as $apt)@if($apt->project){{ $apt->project->name
                        }} – @endif Unit {{ $apt->unit_number ?? '#'.$apt->id }}@if(!$loop->last), @endif @endforeach
                    </div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblUnits }}</span>
                    <span class="ar-val">{{ $arLblUnits }}</span>
                </td>
            </tr>
            @endif
            @if($linkedManagedProps->isNotEmpty())
            <tr>
                <td>
                    <div class="field-lbl">Managed Property(ies)</div>
                    <div class="field-val">@foreach($linkedManagedProps as $mp){{ $mp->address }}@if($mp->city), {{
                        $mp->city }}@endif @if(!$loop->last); @endif @endforeach</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblManagedP }}</span>
                    <span class="ar-val">@foreach($linkedManagedProps as $mp){{ ArabicPdf::shape($mp->address)
                        }}@if(!$loop->last)؛ @endif @endforeach</span>
                </td>
            </tr>
            @endif
        </table>
    </div>

    <div class="sp-10"></div>

    <!-- SCOPE -->
    <div class="card">
        <table style="width:100%;border:0;border-collapse:collapse;margin-bottom:4px;">
            <tr>
                <td style="border:0;">
                    <div class="h2" style="margin-bottom:0;">Scope of Work</div>
                </td>
                <td style="border:0;text-align:right;"><span class="h2-ar">{{ $arWorkDetails }}</span></td>
            </tr>
        </table>
        <table class="info">
            <tr>
                <td>
                    <div class="field-lbl">Description</div>
                    <div class="field-val"><b>{{ $contract->scope_of_work }}</b></div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblDesc }}</span>
                    <span class="ar-val">{{ $arScopeOfWork }}</span>
                </td>
            </tr>
            @if($contract->category)
            <tr>
                <td>
                    <div class="field-lbl">Category</div>
                    <div class="field-val"><span class="badge-cat">{{ strtoupper($contract->category) }}</span></div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblCategory }}</span>
                    <span class="ar-val">{{ ArabicPdf::shape($contract->category) }}</span>
                </td>
            </tr>
            @endif
            @if($contract->start_date)
            <tr>
                <td>
                    <div class="field-lbl">Start Date</div>
                    <div class="field-val">{{ $contract->start_date->format('Y-m-d') }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblStartDate }}</span>
                    <span class="ar-val">{{ $contract->start_date->format('Y-m-d') }}</span>
                </td>
            </tr>
            @endif
            @if($contract->expected_end_date)
            <tr>
                <td>
                    <div class="field-lbl">Expected Completion</div>
                    <div class="field-val">{{ $contract->expected_end_date->format('Y-m-d') }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblEndDate }}</span>
                    <span class="ar-val">{{ $contract->expected_end_date->format('Y-m-d') }}</span>
                </td>
            </tr>
            @endif
        </table>
    </div>

    <div class="sp-10"></div>

    <!-- ASSIGNMENT & COST BREAKDOWN -->
    @if($linkedProjects->isNotEmpty() || $linkedApartments->isNotEmpty() || $linkedManagedProps->isNotEmpty())
    <div class="card">
        <div class="h2">Assignment & Cost Breakdown</div>
        <table class="money">
            @foreach($linkedProjects as $proj)
            <tr>
                <th>Project: {{ $proj->name }}@if($proj->code) ({{ $proj->code }})@endif</th>
                <td>
                    @if(isset($projectCosts[$proj->id]) && $projectCosts[$proj->id] > 0)
                    USD ${{ number_format($projectCosts[$proj->id], 2) }}
                    @else
                    <span style="color:#6b7280;">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
            @foreach($linkedApartments as $apt)
            <tr>
                <th>
                    Unit {{ $apt->unit_number ?? '#'.$apt->id }}
                    @if($apt->project) <span style="font-weight:400;color:#6b7280;">({{ $apt->project->name
                        }})</span>@endif
                </th>
                <td>
                    @if(isset($apartmentCosts[$apt->id]) && $apartmentCosts[$apt->id] > 0)
                    USD ${{ number_format($apartmentCosts[$apt->id], 2) }}
                    @else
                    <span style="color:#6b7280;">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
            @foreach($linkedManagedProps as $mp)
            <tr>
                <th>
                    Property: {{ $mp->address }}@if($mp->city), {{ $mp->city }}@endif
                    <span style="font-weight:400;color:#6b7280;">({{ ucfirst($mp->type) }})</span>
                </th>
                <td>
                    @if(isset($managedPropertyCosts[$mp->id]) && $managedPropertyCosts[$mp->id] > 0)
                    USD ${{ number_format($managedPropertyCosts[$mp->id], 2) }}
                    @else
                    <span style="color:#6b7280;">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
            <tr class="final">
                <th>Total Contract Amount</th>
                <td>USD ${{ number_format($contract->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>
    @endif

    <div class="sp-10"></div>

    <!-- PAYMENT -->
    <div class="card">
        <table style="width:100%;border:0;border-collapse:collapse;margin-bottom:4px;">
            <tr>
                <td style="border:0;">
                    <div class="h2" style="margin-bottom:0;">Payment Terms</div>
                </td>
                <td style="border:0;text-align:right;"><span class="h2-ar">{{ $arPaySchedule }}</span></td>
            </tr>
        </table>
        <table class="info">
            @if($linkedProjects->isEmpty() && $linkedApartments->isEmpty() && $linkedManagedProps->isEmpty())
            <tr>
                <td>
                    <div class="field-lbl">Total Contract Amount</div>
                    <div class="field-val">USD ${{ number_format($contract->total_amount,2) }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblTotal }}</span>
                    <span class="ar-val">USD ${{ number_format($contract->total_amount,2) }}</span>
                </td>
            </tr>
            @endif
            <tr>
                <td>
                    <div class="field-lbl">Monthly Payment</div>
                    <div class="field-val"><b>USD ${{ number_format($contract->monthly_amount,2) }}</b></div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblMonthly }}</span>
                    <span class="ar-val">USD ${{ number_format($contract->monthly_amount,2) }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">Number of Payments</div>
                    <div class="field-val">{{ $contract->payment_months }} months</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblMonths }}</span>
                    <span class="ar-val">{{ $contract->payment_months }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="field-lbl">First Payment Date</div>
                    <div class="field-val">{{ $contract->first_payment_date->format('Y-m-d') }}</div>
                </td>
                <td style="text-align:right;">
                    <span class="ar-lbl">{{ $arLblFirstDate }}</span>
                    <span class="ar-val">{{ $contract->first_payment_date->format('Y-m-d') }}</span>
                </td>
            </tr>
        </table>

        @if($contract->notes)
        <div class="notes">
            <div style="font-weight:800;margin-bottom:6px;">Notes</div>
            <div>{{ $contract->notes }}</div>
        </div>
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
                <td style="border:0;text-align:right;"><span class="h2-ar">{{ $arSig }}</span></td>
            </tr>
        </table>
        <table style="border:0;">
            <tr>
                <td style="width:50%;padding-right:10px;border:0;vertical-align:top;">
                    <div class="sig-box">
                        <div class="sig-title">Contractor / Worker</div>
                        <div><b>Name:</b> {{ $contract->worker->name }}</div>
                        <div style="margin-top:26px;"><span class="k">Signature:</span> ____________________________
                        </div>
                        <div style="margin-top:10px;"><span class="k">Date:</span> ____ / ____ / ______</div>
                    </div>
                </td>
                <td style="width:50%;padding-left:10px;border:0;vertical-align:top;">
                    <div class="sig-box">
                        <div class="sig-title">Employer / Company</div>
                        <div><b>Company:</b> Abou Saleh General Trading</div>
                        <div><b>Representative:</b> ____________________________</div>
                        <div style="margin-top:10px;">
                            <span class="k">Signature:</span>
                            <span
                                style="display:inline-block;width:240px;border-bottom:1px solid #111;height:18px;vertical-align:bottom;">
                                @if($signatureB64)
                                <img class="sig-signature-img" style="position:relative;top:-18px;left:10px;"
                                    src="data:image/png;base64,{{ $signatureB64 }}" alt="Signature">
                                @endif
                            </span>
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
        <div class="h2">Terms & Conditions</div>
        <ol class="small">
            <li>The contractor agrees to complete the described scope of work to professional standards.</li>
            <li>Payments will be made monthly as per the schedule above, provided work is progressing satisfactorily.
            </li>
            <li>The employer reserves the right to withhold payment if work quality does not meet agreed standards.</li>
            <li>The contractor is responsible for their own tools, equipment, and safety at the work site.</li>
            <li>Any changes to scope or payment terms must be agreed upon in writing by both parties.</li>
            <li>This contract is governed by applicable local laws and regulations.</li>
            <li>Both parties acknowledge they have read and accepted all terms herein.</li>
        </ol>
        <table
            style="width:100%;border-collapse:collapse;font-family:'Amiri',sans-serif;font-size:11px;margin-top:8px;">
            @foreach($arWorkerTerms as $i => $term)
            <tr>
                <td
                    style="direction:ltr;unicode-bidi:bidi-override;text-align:right;padding:0 6px 4px 0;vertical-align:top;">
                    {{ $term }}</td>
                <td style="width:22px;text-align:right;padding:0 0 4px 4px;vertical-align:top;font-weight:700;">.{{ $i +
                    1 }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $left = "Abou Saleh General Trading • Work Contract #{{ $contract->id }}";
            $pdf->page_text(22, 825, $left, null, 9, array(0.42,0.45,0.50));
            $pdf->page_text(500, 825, "Page {PAGE_NUM} of {PAGE_COUNT}", null, 9, array(0.42,0.45,0.50));
        }
    </script>
</body>

</html>