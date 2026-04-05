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

    $arInvoice = ArabicPdf::shape('فاتورة');
    $arNo = ArabicPdf::shape('الرقم');
    $arStatus = ArabicPdf::shape('الحالة');
    $arIssue = ArabicPdf::shape('تاريخ الإصدار');
    $arDue = ArabicPdf::shape('تاريخ الاستحقاق');
    $arClient = ArabicPdf::shape('العميل');
    $arProject = ArabicPdf::shape('المشروع');
    $arApartment = ArabicPdf::shape('الشقة');
    $arContract = ArabicPdf::shape('رقم العقد');
    $arBase = ArabicPdf::shape('المبلغ الأساسي');
    $arLateFee = ArabicPdf::shape('رسوم التأخير');
    $arTotal = ArabicPdf::shape('المجموع');
    $arDesc = ArabicPdf::shape('الوصف');
    $arInstallment= ArabicPdf::shape('قسط شهري');
    $arSignature = ArabicPdf::shape('التوقيع المخوّل');
    $arCompany = ArabicPdf::shape('أبو صالح للعقارات');
    @endphp

    @include('pdfs._arabic_font')

    <style>
        @page {
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

        .box {
            border: 1px solid #eee;
            padding: 0;
            margin-top: 10px;
        }

        .title-row {
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: 900;
        }

        .ar {
            font-family: 'Amiri', sans-serif;
            direction: rtl;
            text-align: right;
        }

        .bi-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bi-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #f0f0f0;
            width: 50%;
            vertical-align: top;
        }

        .bi-table tr:last-child td {
            border-bottom: none;
        }

        .bi-table .en {
            text-align: left;
            direction: ltr;
        }

        .bi-table .ar {
            text-align: right;
            direction: rtl;
            font-family: 'Amiri', sans-serif;
        }

        .sig-section {
            margin-top: 40px;
        }

        .sig-table {
            width: 100%;
            border-collapse: collapse;
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

        .sig-line-ar {
            width: 240px;
            border-bottom: 1px solid #111;
            margin-top: 6px;
            margin-left: auto;
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

    <div class="title-row">Invoice &nbsp;|&nbsp; <span class="ar">{{ $arInvoice }}</span></div>
    <div class="muted">Generated: {{ ($generatedAt ?? now())->timezone('Asia/Beirut')->format('Y-m-d H:i') }}</div>

    <div class="box">
        <table class="bi-table">
            <tr>
                <td class="en"><strong>No:</strong> {{ $invoice->invoice_number }}</td>
                <td class="ar"><strong>{{ $arNo }}:</strong> {{ $invoice->invoice_number }}</td>
            </tr>
            <tr>
                <td class="en"><strong>Status:</strong> {{ strtoupper($invoice->status) }}</td>
                <td class="ar"><strong>{{ $arStatus }}:</strong> {{ strtoupper($invoice->status) }}</td>
            </tr>
            <tr>
                <td class="en"><strong>Issue date:</strong> {{ $invoice->issue_date }}</td>
                <td class="ar"><strong>{{ $arIssue }}:</strong> {{ $invoice->issue_date }}</td>
            </tr>
            <tr>
                <td class="en"><strong>Due date:</strong> {{ $invoice->due_date }}</td>
                <td class="ar"><strong>{{ $arDue }}:</strong> {{ $invoice->due_date }}</td>
            </tr>
        </table>
    </div>

    <div class="box" style="margin-top:10px;">
        <table class="bi-table">
            <tr>
                <td class="en"><strong>Client:</strong> {{ $contract->client->name }}</td>
                <td class="ar"><strong>{{ $arClient }}:</strong> {{ $contract->client->name }}</td>
            </tr>
            <tr>
                <td class="en"><strong>Project:</strong> {{ $contract->project->name ?? '-' }}</td>
                <td class="ar"><strong>{{ $arProject }}:</strong> {{ $contract->project->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="en"><strong>Apartment:</strong> {{ $contract->apartment->unit_number ?? '-' }}</td>
                <td class="ar"><strong>{{ $arApartment }}:</strong> {{ $contract->apartment->unit_number ?? '-' }}</td>
            </tr>
            <tr>
                <td class="en"><strong>Contract ID:</strong> #{{ $contract->id }}</td>
                <td class="ar"><strong>{{ $arContract }}:</strong> #{{ $contract->id }}</td>
            </tr>
        </table>
    </div>

    <div class="box" style="margin-top:10px;">
        <table class="bi-table">
            <tr>
                <td class="en"><strong>Base amount:</strong> ${{ number_format($baseAmount, 2) }}</td>
                <td class="ar"><strong>{{ $arBase }}:</strong> ${{ number_format($baseAmount, 2) }}</td>
            </tr>
            @if(strtolower($invoice->status) === 'overdue' && $lateFee > 0)
            <tr>
                <td class="en"><strong>Late fee:</strong> ${{ number_format($lateFee, 2) }}</td>
                <td class="ar"><strong>{{ $arLateFee }}:</strong> ${{ number_format($lateFee, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="en"><strong>Total due:</strong> ${{ number_format($totalDue, 2) }}</td>
                <td class="ar"><strong>{{ $arTotal }}:</strong> ${{ number_format($totalDue, 2) }}</td>
            </tr>
            <tr>
                <td class="en"><strong>Description:</strong> Monthly installment</td>
                <td class="ar"><strong>{{ $arDesc }}:</strong> {{ $arInstallment }}</td>
            </tr>
        </table>
    </div>

    <div class="sig-section">
        <table class="sig-table">
            <tr>
                <td>
                    <strong>Authorized Signature</strong>
                    @if($signatureB64)
                    <img class="sig-img" src="data:image/png;base64,{{ $signatureB64 }}" alt="Sig">
                    @else
                    <div class="sig-line"></div>
                    @endif
                    <div style="margin-top:4px; font-weight:600;">Abou Saleh Real Estate</div>
                </td>
                <td style="text-align:right; direction:rtl; font-family:'Amiri',sans-serif;">
                    <strong>{{ $arSignature }}</strong>
                    <div class="sig-line-ar"></div>
                    <div style="margin-top:4px; font-weight:600;">{{ $arCompany }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>