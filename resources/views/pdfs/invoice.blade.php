<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    use App\Support\ArabicPdf;
    $logoPath = public_path('img/abosaleh-logo.png');
    $logoB64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
    $baseAmount = (float) $invoice->amount;
    $lateFee = (float) ($invoice->late_fee_amount ?? 0);
    $totalDue = $baseAmount + $lateFee;

    $arTitle = ArabicPdf::shape('فاتورة');
    $arNo = ArabicPdf::shape('رقم الفاتورة');
    $arStatus = ArabicPdf::shape('الحالة');
    $arIssue = ArabicPdf::shape('تاريخ الإصدار');
    $arDue = ArabicPdf::shape('تاريخ الاستحقاق');
    $arClient = ArabicPdf::shape('العميل');
    $arProject = ArabicPdf::shape('المشروع');
    $arApartment = ArabicPdf::shape('الشقة');
    $arContract = ArabicPdf::shape('رقم العقد');
    $arBase = ArabicPdf::shape('المبلغ الأساسي');
    $arLateFee = ArabicPdf::shape('رسوم التأخير');
    $arTotal = ArabicPdf::shape('المجموع المستحق');
    $arDesc = ArabicPdf::shape('قسط شهري');
    $arSig = ArabicPdf::shape('التوقيع المخوّل');
    $arCompany = ArabicPdf::shape('ابو صالح للتجارة العامة');
    $arLblDesc = ArabicPdf::shape('الوصف');

    $arClientName = ArabicPdf::shape($contract->client->name ?? '');
    $arProjectName= ArabicPdf::shape($contract->project->name ?? '-');
    $arUnitNum = ArabicPdf::shape($contract->apartment->unit_number ?? '-');
    $arStatusVal = ArabicPdf::shape(match(strtolower($invoice->status)) {
    'paid' => 'مدفوع',
    'pending' => 'قيد الانتظار',
    'overdue' => 'متأخر',
    default => strtoupper($invoice->status),
    });
    @endphp

    @include('pdfs._arabic_font')

    <style>
        @@page {
            margin: 26px 22px 55px 22px;
        }

        body {
            font-family: 'Amiri', DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
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
            margin: 10px 0 14px;
            text-align: center;
        }

        .muted {
            color: #6b7280;
            font-size: 10.5px;
            margin-bottom: 10px;
        }

        .ar {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: inline-block;
            width: 100%;
        }

        .box {
            border: 1px solid #eee;
            margin-top: 10px;
        }

        .bi {
            width: 100%;
            border-collapse: collapse;
        }

        .bi td {
            padding: 7px 10px;
            border-bottom: 1px solid #f0f0f0;
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

        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }

        .sig-table td {
            vertical-align: top;
            padding: 0 6px;
            width: 50%;
        }

        .sig-line {
            width: 240px;
            border-bottom: 1px solid #111;
            margin-top: 6px;
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
        INVOICE &nbsp;|&nbsp; <span style="font-family:'Amiri',sans-serif;direction:ltr;unicode-bidi:bidi-override;">{{
            $arTitle }}</span>
    </div>
    <div class="muted">Generated: {{ ($generatedAt ?? now())->timezone('Asia/Beirut')->format('Y-m-d H:i') }}</div>

    <div class="box">
        <table class="bi">
            <tr>
                <td class="en"><strong>No:</strong> {{ $invoice->invoice_number }}</td>
                <td><span class="ar">{{ $invoice->invoice_number }} : {{ $arNo }}</span></td>
            </tr>
            <tr>
                <td class="en"><strong>Status:</strong> {{ strtoupper($invoice->status) }}</td>
                <td><span class="ar">{{ $arStatusVal }} : {{ $arStatus }}</span></td>
            </tr>
            <tr>
                <td class="en"><strong>Issue date:</strong> {{ $invoice->issue_date }}</td>
                <td><span class="ar">{{ $invoice->issue_date }} : {{ $arIssue }}</span></td>
            </tr>
            <tr>
                <td class="en"><strong>Due date:</strong> {{ $invoice->due_date }}</td>
                <td><span class="ar">{{ $invoice->due_date }} : {{ $arDue }}</span></td>
            </tr>
        </table>
    </div>

    <div class="box">
        <table class="bi">
            <tr>
                <td class="en"><strong>Client:</strong> {{ $contract->client->name }}</td>
                <td><span class="ar">{{ $arClientName }} : {{ $arClient }}</span></td>
            </tr>
            <tr>
                <td class="en"><strong>Project:</strong> {{ $contract->project->name ?? '-' }}</td>
                <td><span class="ar">{{ $arProjectName }} : {{ $arProject }}</span></td>
            </tr>
            <tr>
                <td class="en"><strong>Apartment:</strong> {{ $contract->apartment->unit_number ?? '-' }}</td>
                <td><span class="ar">{{ $arUnitNum }} : {{ $arApartment }}</span></td>
            </tr>
            <tr>
                <td class="en"><strong>Contract ID:</strong> #{{ $contract->id }}</td>
                <td><span class="ar">#{{ $contract->id }} : {{ $arContract }}</span></td>
            </tr>
        </table>
    </div>

    <div class="box">
        <table class="bi">
            <tr>
                <td class="en"><strong>Base amount:</strong> ${{ number_format($baseAmount, 2) }}</td>
                <td><span class="ar">${{ number_format($baseAmount, 2) }} : {{ $arBase }}</span></td>
            </tr>
            @if(strtolower($invoice->status) === 'overdue' && $lateFee > 0)
            <tr>
                <td class="en"><strong>Late fee:</strong> ${{ number_format($lateFee, 2) }}</td>
                <td><span class="ar">${{ number_format($lateFee, 2) }} : {{ $arLateFee }}</span></td>
            </tr>
            @endif
            <tr>
                <td class="en"><strong>Total due:</strong> ${{ number_format($totalDue, 2) }}</td>
                <td><span class="ar">${{ number_format($totalDue, 2) }} : {{ $arTotal }}</span></td>
            </tr>
            <tr>
                <td class="en"><strong>Description:</strong> Monthly installment</td>
                <td><span class="ar">{{ $arDesc }} : {{ $arLblDesc }}</span></td>
            </tr>
        </table>
    </div>

    <div class="sig-wrap" style="margin-top:20px;">
        <table style="border:0;border-collapse:collapse;width:100%;">
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
</body>

</html>