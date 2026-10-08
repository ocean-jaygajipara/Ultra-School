<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Shree Patel Vidhya Mandir Science College - Keshod Admission Form</title>
    <style>
        /* ====== Force A4 Print Size ====== */
        @page {
            size: A4 portrait;
            margin: 15mm 20mm;
            /* top-bottom / left-right */
        }

        html,
        body {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 0;
            background: #f5f8fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .container {
            width: 100%;
            max-width: 750px;
            /* keep inside A4 margins */
            margin: 0 auto;
            background: #fff;
            border: 3px solid #004aad;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 25px 35px;
        }

        h2 {
            text-align: center;
            color: #004aad;
            font-weight: 800;
            margin: 0;
        }

        h4 {
            text-align: center;
            color: #f57c00;
            font-style: italic;
            margin-top: 5px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .photo-box {
            border: 2px solid #004aad;
            width: 120px;
            height: 150px;
            border-radius: 8px;
            background: #f0f6ff;
            text-align: center;
            font-size: 12px;
            color: #666;
            line-height: 150px;
        }

        hr {
            border: none;
            border-top: 2px dashed #1e88e5;
            margin: 5px 0;
        }

        .admission-header {
            font-size: 18px;
            font-weight: 700;
            color: #1565c0;
            margin-bottom: 6px;
        }

        .admission-details-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .admission-details-table td {
            padding: 6px 10px;
        }

        .admission-details-table td strong {
            color: #004aad;
        }

        .info-section {
            display: flex;
            align-items: center;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .info-section label {
            display: inline-block;
            width: 155px;
            font-weight: 600;
            color: #1e88e5;
        }

        .underline-input {
            display: inline-block;
            border-bottom: 1px solid #90caf9;
            min-width: 200px;
            padding: 2px 6px;
            color: #000;
            white-space: nowrap;
        }

        .checkbox-label {
            font-weight: 600;
            margin-right: 12px;
            color: #2e7d32;
        }

        .checkbox-label input {
            accent-color: #004aad;
            margin-right: 4px;
        }

        .education-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 15px;
        }

        .education-table th {
            background: #004aad;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 6px;
            border: 1px solid #1565c0;
        }

        .education-table td {
            border: 1px solid #90caf9;
            text-align: center;
            padding: 6px;
        }

        .education-table tbody tr:nth-child(even) {
            background-color: #f4f8ff;
        }

        .signature-box {
            text-align: center;
            margin-top: 35px;
        }

        .signature-box div {
            display: inline-block;
            width: 30%;
            border-top: 2px solid #004aad;
            padding-top: 6px;
            font-weight: 600;
            color: #004aad;
        }

        .footer-college-info {
            text-align: center;
            background: #004aad;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 0;
            border-top: 3px solid #f57c00;
            margin-top: 25px;
            border-radius: 0 0 8px 8px;
        }

        /* ======= Print View ======= */
        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
                background: #fff;
            }

            .container {
                box-shadow: none;
                border: 1px solid #000;
                border-radius: 0;
                margin: 0;
                padding: 15mm;
            }

            .photo-box {
                border: 1px solid #000;
                background: none;
            }

            .education-table th {
                background: #004aad;
                color: #fff;
                font-weight: bold;
                text-align: center;
                padding: 6px;
                border: 1px solid #1565c0;
            }

            .footer-college-info {
                text-align: center;
                background: #004aad;
                color: #fff;
                font-size: 16px;
                font-weight: bold;
                padding: 10px 0;
                border-top: 3px solid #f57c00;
                margin-top: 25px;
                border-radius: 0 0 8px 8px;
            }



        }
    </style>


</head>

<body>
    <div class="container">
        <div class="header-section" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 3px double #004aad; padding-bottom: 15px; margin-bottom: 20px;">
            <div class="logo-box" style="width: 100px; text-align: left; flex-shrink: 0;">
                <img src="{{ asset('uploads/logo/logo.png') }}" alt="College Logo" style="width: 85px; height: auto;">
            </div>
            <div class="college-info-box" style="text-align: center; flex-grow: 1; padding: 0 10px; line-height: 1.3;">
                {{-- <span style="font-size: 11px; font-weight: 600; color: #555; text-transform: uppercase; letter-spacing: 0.5px;">Shree Patel Vidhyarthi Ashram Sanchalit</span><br>
                <h2 style="font-size: 20px; font-weight: 800; color: #004aad; margin: 2px 0; text-transform: uppercase;">Shree Patel Vidhya Mandir Science College</h2>
                <span style="font-size: 11px; color: #666; font-weight: 500;">Behind Maruti Service Center, Veraval Road, Keshod – 362220</span><br>
                <span style="font-size: 11px; color: #666; font-weight: 500;">Mo. 96874 51774 | Email: pvmsciencecollegekeshod@gmail.com</span><br> --}}
                <h4 style="font-size: 16px; font-weight: 700; color: #f57c00; font-style: italic; margin-top: 5px; margin-bottom: 0; text-transform: uppercase;">Admission Form</h4>
            </div>
            <div class="photo-box" style="margin-left: 10px; flex-shrink: 0; line-height: 150px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                @if($admission->profile_pic && file_exists(public_path($admission->profile_pic)))
                    <img src="{{ $admission->profile_pic_url }}" alt="Profile Picture" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <span style="display: none; font-size: 13px; color: #777; font-weight: 600;">Photo</span>
                @else
                    <span style="font-size: 13px; color: #777; font-weight: 600;">Photo</span>
                @endif
            </div>
        </div>

        <div class="admission-header">Admission Details:</div>
        <table class="admission-details-table">
            <tr>
                <td style="width: 141px;"><strong>GR No. :</strong> {{ $admission->gr_no ?? $admission->id }}</td>
                <td style="width: 195px;"><strong>Adm. Date :</strong> {{ $admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('d-m-Y') : ($admission->created_at ? $admission->created_at->format('d-m-Y') : '-') }}</td>
                <td><strong>Bus Route :</strong> {{ $admission->bus_route_village ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Adm. Std :</strong> {{ $admission->admission_std ?? '-' }}</td>
                <td><strong>Current Std :</strong> {{ $admission->current_std ?? '-' }}</td>
                <td><strong>Division :</strong> {{ $admission->division ?? '-' }}</td>
            </tr>
        </table>

        <hr>

        <div class="info-section">
            <label>Student's Full Name :</label>
            <span class="underline-input">
                {{ strtoupper($admission->first_name ?? '') }}
                {{ strtoupper($admission->last_name ?? '') }}
                {{ strtoupper($admission->father_name ?? '') }}
            </span>
        </div>

        <div class="info-section">
            <label>Mother Name :</label>
            <span class="underline-input">{{ $admission->mother_name ?? '' }}</span>
        </div>

        <div class="info-section">
            <label>Father Occupation :</label>
            <span class="underline-input">{{ $admission->occupation ?? '' }}</span>
            <label style="margin-left: 15px;">Mother Occupation :</label>
            <span class="underline-input">{{ $admission->mother_occupation ?? '' }}</span>
        </div>

        <div class="info-section" style="align-items: flex-start;">
            <label>Permanent Address :</label>
            <span class="underline-input" style="white-space: normal; flex-grow: 1; line-height: 1.4; min-height: 24px;">{{ $admission->permanent_address ?? '' }}</span>
        </div>

        <div class="info-section">
            <label>Contact No. (Self):</label>
            <span class="underline-input">{{ $admission->mobile_no ?? '' }}</span>
            <label style="margin-left: 15px;">(Father):</label>
            <span class="underline-input">{{ $admission->parent_mobile_no ?? '' }}</span>
        </div>

        <div class="info-section">
            <label>Email:</label>
            <span class="underline-input">{{ $admission->email_address ?? '' }}</span>
        </div>

        <div class="info-section">
            <label>Date of Birth:</label>
            <span
                class="underline-input">{{ !empty($admission->date_of_birth) ? \Carbon\Carbon::parse($admission->date_of_birth)->format('d/m/Y') : '' }}</span>
            <label style="margin-left: 15px;">Birth Place:</label>
            <span class="underline-input">{{ $admission->birth_place ?? '' }}</span>
            <label style="margin-left: 15px;">Aadhaar No.:</label>
            <span class="underline-input">{{ $admission->aadhar_card_no ?? '' }}</span>
            @if(!empty($admission->pen_no))
            <label style="margin-left: 15px;">PEN No.:</label>
            <span class="underline-input">{{ $admission->pen_no }}</span>
            @endif
        </div>

        <div class="info-section">
            <label>Gender:</label>
            <label class="checkbox-label"><input type="checkbox"
                    {{ $admission->gender === 'Male' ? 'checked' : '' }}>Male</label>
            <label class="checkbox-label"><input type="checkbox"
                    {{ $admission->gender === 'Female' ? 'checked' : '' }}>Female</label>
        </div>

        <div class="info-section">
            <label>Caste:</label>
            @foreach (['Baxipanch', 'SC', 'ST', 'Handicaped', 'Vichar Vimukti jati', 'ex- serviceman', 'general', 'low-profession', 'minority'] as $castOption)
                <label class="checkbox-label"><input type="checkbox"
                        {{ (strcasecmp($admission->cast ?? '', $castOption) === 0) ? 'checked' : '' }}>{{ $castOption }}</label>
            @endforeach
        </div>

        <div class="info-section">
            <label>Religion :</label>
            <span class="underline-input">{{ $admission->religion ?? '-' }}</span>
            <label style="margin-left: 15px;">House :</label>
            <span class="underline-input">{{ $admission->house ?? '-' }}</span>
            <label style="margin-left: 15px;">Stream :</label>
            <span class="underline-input">{{ $admission->stream ?? '-' }}</span>
        </div>

        <div class="info-section">
            <label>Category:</label>
            @php
                $viewCategories = \App\Models\Master\MasterCategory::where('status', 'active')->orderBy('id')->pluck('name')->toArray();
                if (!empty($admission->category) && !in_array($admission->category, $viewCategories)) {
                    $viewCategories[] = $admission->category;
                }
            @endphp
            @foreach ($viewCategories as $catOption)
                <label class="checkbox-label"><input type="checkbox"
                        {{ (strcasecmp($admission->category ?? '', $catOption) === 0) ? 'checked' : '' }}>{{ $catOption }}</label>
            @endforeach
        </div>

        @if(!empty($admission->bank_name) || !empty($admission->bank_account_no))
        <div class="info-section">
            <label>Bank Name :</label>
            <span class="underline-input">{{ $admission->bank_name ?? '-' }}</span>
            <label style="margin-left: 15px;">Account No. :</label>
            <span class="underline-input">{{ $admission->bank_account_no ?? '-' }}</span>
        </div>
        @endif

        <div class="form-section">
            <strong style="font-size: 18px; color: #1565c0;">Previous / Last School Details:</strong>
            @if(!empty($admission->is_new_admission))
                <div class="info-section" style="margin-top: 8px;">
                    <span class="badge" style="background: #e8f5e9; color: #2e7d32; font-weight: bold; padding: 4px 10px; border-radius: 4px; border: 1px solid #c8e6c9;">Fresh / New Admission (No Previous School)</span>
                </div>
            @else
                <div class="info-section" style="margin-top: 8px;">
                    <label>Last School Name :</label>
                    <span class="underline-input" style="flex-grow: 1;">{{ $admission->last_school_name ?? '-' }}</span>
                </div>
                <div class="info-section">
                    <label>Old GR No. :</label>
                    <span class="underline-input">{{ $admission->old_gr_no ?? '-' }}</span>
                    <label style="margin-left: 15px;">Passed Standard :</label>
                    <span class="underline-input">{{ $admission->passed_standard ? 'Std ' . $admission->passed_standard : '-' }}</span>
                    <label style="margin-left: 15px;">Attendance :</label>
                    <span class="underline-input">{{ $admission->attendance ?? '-' }}</span>
                </div>
                <div class="info-section">
                    <label>LC No. :</label>
                    <span class="underline-input">{{ $admission->lc_no ?? '-' }}</span>
                    <label style="margin-left: 15px;">LC Date :</label>
                    <span class="underline-input">{{ !empty($admission->lc_date) ? \Carbon\Carbon::parse($admission->lc_date)->format('d-m-Y') : '-' }}</span>
                </div>
            @endif
        </div>

        <div class="signature-box">
            <div>Applicant's Signature</div>
            <div>Parent's Signature</div>
            <div>Principal</div>
        </div>

        {{-- <div class="footer-college-info">
            Shree Patel Vidhya Mandir Science College - Keshod | Mo. 96874 51774
        </div> --}}
    </div>
</body>

</html>
