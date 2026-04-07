<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    @php
    use App\Support\ArabicPdf;
    $arDocTitle = ArabicPdf::shape('اتفاقية إدارة عقار');
    $arCompanyAr = ArabicPdf::shape('أبو صالح للعقارات');
    $arParties = ArabicPdf::shape('اطراف الاتفاقية');
    $arAgreement = ArabicPdf::shape('تفاصيل الاتفاقية');
    $arOwner = ArabicPdf::shape('معلومات المالك');
    $arProperty = ArabicPdf::shape('معلومات العقار');
    $arFinancial = ArabicPdf::shape('التفاصيل المالية');
    $arTerms = ArabicPdf::shape('الشروط والاحكام');
    $arSig = ArabicPdf::shape('التوقيعات');
    $arLblName = ArabicPdf::shape('الاسم');
    $arLblPhone = ArabicPdf::shape('الهاتف');
    $arLblDate = ArabicPdf::shape('التاريخ');
    $arLblType = ArabicPdf::shape('نوع الخدمة');
    $arOwnerName = ArabicPdf::shape($property->owner_name ?? '');
    @endphp
    @include('pdfs._arabic_font')
    <style>
        @page {
            margin: 36px 44px;
        }

        body {
            font-family: 'Amiri', DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0b2545;
        }

        .watermark {
            position: fixed;
            left: 50%;
            top: 52%;
            transform: translate(-50%, -50%);
            width: 480px;
            opacity: 0.06;
            z-index: -1;
        }

        .logo-top {
            text-align: center;
            margin-bottom: 6px;
        }

        .logo-top img {
            width: 110px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header .title {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 0;
        }

        .contact-row {
            display: table;
            width: 100%;
            margin: 6px 0 12px;
        }

        .contact-col {
            display: table-cell;
            width: 50%;
            font-size: 11px;
            vertical-align: top;
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

        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e3a5f;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 4px;
            margin: 16px 0 10px;
        }

        .row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .col-label {
            display: table-cell;
            width: 38%;
            font-weight: bold;
            font-size: 11px;
        }

        .col-value {
            display: table-cell;
            border-bottom: 1px dotted #333;
            font-size: 11px;
            padding-bottom: 2px;
        }

        .highlight-box {
            background: #f0f9f0;
            border: 1px solid #86efac;
            border-radius: 6px;
            padding: 12px 16px;
            margin: 14px 0;
        }

        .highlight-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .highlight-label {
            display: table-cell;
            width: 55%;
            font-size: 11px;
            font-weight: bold;
        }

        .highlight-value {
            display: table-cell;
            font-size: 12px;
            font-weight: 800;
            color: #059669;
            text-align: right;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 16px;
            margin: 14px 0;
            font-size: 11px;
            color: #374151;
            line-height: 1.7;
        }

        .signature-section {
            display: table;
            width: 100%;
            margin-top: 36px;
        }

        .sig-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .sig-label {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 28px;
        }

        .sig-line {
            border-top: 1px solid #333;
            width: 80%;
            margin-top: 4px;
        }

        .signature-img {
            position: relative;
            top: -8px;
            height: 40px;
            max-width: 220px;
            display: inline-block;
            vertical-align: bottom;
        }

        .stamp-box {
            width: 80px;
            height: 80px;
            border: 1px solid #000;
            display: inline-block;
            margin-top: 12px;
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

        /* Arabic text shaping — direction:ltr because utf8Glyphs pre-orders visually */
        .ar {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: inline-block;
            width: 100%;
        }

        .ar {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: inline-block;
            width: 100%;
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
        }

        .ar-val {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            text-align: right;
            display: block;
        }

        .section-title-ar {
            font-family: 'Amiri', sans-serif;
            direction: ltr;
            unicode-bidi: bidi-override;
            font-size: 11px;
            font-weight: bold;
            color: #1e3a5f;
            float: right;
        }
    </style>
</head>

<body>
    @if($logoB64)<img class="watermark" src="data:image/png;base64,{{ $logoB64 }}" alt="">@endif

    <div class="logo-top">
        @if($logoB64)<img src="data:image/png;base64,{{ $logoB64 }}" alt="Logo">@endif
    </div>

    <div class="header">
        <div class="title">ABOU SALEH GENERAL TRADING</div>
    </div>

    <div class="contact-row">
        <div class="contact-col">Address: ___________________________<br>Email: info@abousaleh.me</div>
        <div class="contact-col" style="text-align:right;">Tel: +961 71 999 219<br>www.abousaleh.me</div>
    </div>

    <div class="voucher-bar">
        PROPERTY MANAGEMENT AGREEMENT<br>
        <span style="font-family:'Amiri',sans-serif;direction:ltr;unicode-bidi:bidi-override;font-size:13px;">{{
            $arDocTitle }}</span><br>
        {{ $property->isFlip() ? 'FLIP / SALE' : 'RENTAL MANAGEMENT' }}
    </div>

    <table style="width:100%;border:0;border-collapse:collapse;margin:16px 0 10px;">
        <tr>
            <td style="border:0;">
                <div class="section-title" style="margin:0;border:0;">Agreement Details</div>
            </td>
            <td style="border:0;text-align:right;"><span class="section-title-ar">{{$arAgreement}}</span></td>
        </tr>
    </table>
    <div class="row">
        <div class="col-label">Agreement No:</div>
        <div class="col-value">MP-{{ str_pad($property->id, 5, '0', STR_PAD_LEFT) }}</div>
    </div>
    <div class="row">
        <div class="col-label">Date:</div>
        <div class="col-value">{{ $property->agreement_date->format('d F Y') }}</div>
    </div>
    <div class="row">
        <div class="col-label">Service Type:</div>
        <div class="col-value">{{ $property->isFlip() ? 'Flip — Renovation & Sale' : 'Rental Management' }}</div>
    </div>

    <table style="width:100%;border:0;border-collapse:collapse;margin:16px 0 10px;">
        <tr>
            <td style="border:0;">
                <div class="section-title" style="margin:0;border:0;">Owner Information</div>
            </td>
            <td style="border:0;text-align:right;"><span class="section-title-ar">{{$arOwner}}</span></td>
        </tr>
    </table>
    <div class="row">
        <div class="col-label">Owner Name:</div>
        <div class="col-value">{{ $property->owner_name }}</div>
    </div>
    <div class="row">
        <div class="col-label">Phone:</div>
        <div class="col-value">{{ $property->owner_phone }}</div>
    </div>
    @if($property->owner_email)
    <div class="row">
        <div class="col-label">Email:</div>
        <div class="col-value">{{ $property->owner_email }}</div>
    </div>
    @endif

    <table style="width:100%;border:0;border-collapse:collapse;margin:16px 0 10px;">
        <tr>
            <td style="border:0;">
                <div class="section-title" style="margin:0;border:0;">Property Details</div>
            </td>
            <td style="border:0;text-align:right;"><span class="section-title-ar">{{ $arProperty }}</span></td>
        </tr>
    </table>
    <div class="row">
        <div class="col-label">Address:</div>
        <div class="col-value">{{ $property->address }}</div>
    </div>
    @if($property->city || $property->area)
    <div class="row">
        <div class="col-label">City / Area:</div>
        <div class="col-value">{{ $property->city }}{{ $property->area ? ' — '.$property->area : '' }}</div>
    </div>
    @endif
    @if($property->bedrooms || $property->bathrooms || $property->area_sqm)
    <div class="row">
        <div class="col-label">Specifications:</div>
        <div class="col-value">
            @if($property->bedrooms) {{ $property->bedrooms }} Bedroom(s) @endif
            @if($property->bathrooms) · {{ $property->bathrooms }} Bathroom(s) @endif
            @if($property->area_sqm) · {{ number_format($property->area_sqm, 0) }} m² @endif
        </div>
    </div>
    @endif

    <table style="width:100%;border:0;border-collapse:collapse;margin:16px 0 10px;">
        <tr>
            <td style="border:0;">
                <div class="section-title" style="margin:0;border:0;">Financial Terms</div>
            </td>
            <td style="border:0;text-align:right;"><span class="section-title-ar">{{ $arFinancial }}</span></td>
        </tr>
    </table>

    @if($property->isFlip())
    <div class="highlight-box">
        <div class="highlight-row">
            <div class="highlight-label">Owner Guaranteed Payout (on sale):</div>
            <div class="highlight-value">${{ number_format($property->owner_asking_price, 2) }}</div>
        </div>
        @if($property->agreed_listing_price)
        <div class="highlight-row">
            <div class="highlight-label">Agreed Listing Price:</div>
            <div class="highlight-value">${{ number_format($property->agreed_listing_price, 2) }}</div>
        </div>
        @endif
        @if($property->estimated_renovation_cost)
        <div class="highlight-row">
            <div class="highlight-label">Estimated Renovation Budget:</div>
            <div class="highlight-value">${{ number_format($property->estimated_renovation_cost, 2) }}</div>
        </div>
        @endif
    </div>
    <div class="info-box">
        <strong>How this works:</strong><br>
        Abou Saleh General Trading will manage all renovation and maintenance work required to prepare the property for
        sale.
        All renovation costs will be covered by the company. Upon successful sale of the property,
        the owner will receive their full asking amount of <strong>${{ number_format($property->owner_asking_price, 2)
            }}</strong>.
        The company retains any profit above this amount after deducting renovation expenses.
    </div>
    @endif

    @if($property->isRental())
    <div class="highlight-box">
        <div class="highlight-row">
            <div class="highlight-label">Expected Monthly Rent:</div>
            <div class="highlight-value">${{ number_format($property->agreed_rent_price ?? 0, 2) }}</div>
        </div>
        @if($property->company_commission_pct)
        @php
        $commAmt = ($property->agreed_rent_price ?? 0) * $property->company_commission_pct / 100;
        $ownerAmt = ($property->agreed_rent_price ?? 0) - $commAmt;
        @endphp
        <div class="highlight-row">
            <div class="highlight-label">Company Commission ({{ $property->company_commission_pct }}%):</div>
            <div class="highlight-value">${{ number_format($commAmt, 2) }}/month</div>
        </div>
        <div class="highlight-row">
            <div class="highlight-label">Owner Monthly Share:</div>
            <div class="highlight-value">${{ number_format($ownerAmt, 2) }}/month</div>
        </div>
        @endif
    </div>
    <div class="info-box">
        <strong>How this works:</strong><br>
        Abou Saleh General Trading will handle all rental management including tenant finding, rent collection,
        and property maintenance coordination. Monthly rent collected from the tenant will be split as detailed above.
        The owner's share will be transferred promptly after each collection.
    </div>
    @endif

    @if($property->notes)
    <table style="width:100%;border:0;border-collapse:collapse;margin:16px 0 10px;">
        <tr>
            <td style="border:0;">
                <div class="section-title" style="margin:0;border:0;">Additional Notes</div>
            </td>
            <td style="border:0;text-align:right;"><span class="section-title-ar">{{ $arTerms }}</span></td>
        </tr>
    </table>
    <div class="info-box">{{ $property->notes }}</div>
    @endif

    <table style="width:100%;border:0;border-collapse:collapse;margin:16px 0 10px;">
        <tr>
            <td style="border:0;">
                <div class="section-title" style="margin:0;border:0;">Signatures</div>
            </td>
            <td style="border:0;text-align:right;"><span class="section-title-ar">{{$arSig}}</span></td>
        </tr>
    </table>
    <div class="signature-section">
        <div class="sig-col">
            <p class="sig-label">Owner: {{ $property->owner_name }}</p>
            <div class="sig-line"></div>
            <p style="font-size:10px;color:#6b7280;margin-top:4px;">Signature & Date</p>
        </div>
        <div class="sig-col">
            <p class="sig-label">Abou Saleh General Trading</p>
            @if($signatureB64)
            <img class="signature-img" src="data:image/png;base64,{{ $signatureB64 }}" alt="Signature">
            @else
            <div class="sig-line"></div>
            @endif
        </div>
    </div>

    <div class="info-note">
        This agreement is valid from the date of signing. Both parties agree to the terms stated above.
        Agreement Reference: MP-{{ str_pad($property->id, 5, '0', STR_PAD_LEFT) }}
    </div>
    <div class="footer-line"></div>
</body>

</html>