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
    $arCompany = ArabicPdf::shape('أبو صالح للعقارات');

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
            left: 50%;
            top: 52%;
            transform: translate(-50%, -50%);
            width: 500px;
            opacity: 0.06;
            z-index: -1;
        }

        .logo {
            position: absolute;
            top: 0;
            right: 0;
            width: 120px;
        }

        .muted {
            color: #6b7280;
            font-size: 10.5px;
        }

        .title-bar {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 6px;
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

        .sig-img {
            height: 45px;
            max-width: 220px;
            display: block;
            margin-top: 6px;
        }
    </style>
</head>

<body>
    @if($logoB64)
    <img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="">
    <img class="logo" src="data:image/png;base64,{{ $logoB64 }}" alt="Logo">
    @endif

    <div class="title-bar">Invoice &nbsp;|&nbsp; <span class="ar" style="font-size:18px;font-weight:bold;">{{ $arTitle
            }}</span></div>
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
                <td><span class="ar">{{ $arDesc }}</span></td>
            </tr>
        </table>
    </div>

    <table class="sig-table">
        <tr>
            <td class="en">
                <strong>Authorized Signature</strong>
                @if($signatureB64)
                <img class="sig-img" src="data:image/png;base64,{{ $signatureB64 }}" alt="Sig">
                @else
                <div class="sig-line"></div>
                @endif
                <div style="margin-top:4px;font-weight:600;">Abou Saleh Real Estate</div>
            </td>
            <td style="text-align:right;">
                <span class="ar">{{ $arSig }}</span>
                <div class="sig-line" style="margin-left:auto;margin-right:0;"></div>
                <span class="ar" style="font-weight:600;">{{ $arCompany }}</span>
            </td>
        </tr>
    </table>
</body>

</html>