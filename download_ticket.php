<?php
// download_ticket.php
require_once __DIR__ . '/vendor/autoload.php';
require 'server/db_connection.php'; // PDO connection

// Get PNR/UUID from URL
$uuid = isset($_GET['pnr']) ? $_GET['pnr'] : (isset($_GET['uuid']) ? $_GET['uuid'] : '');

if (empty($uuid)) {
    die("Error: No PNR/UUID provided. Please provide a valid application ID.");
}

try {
    // ১. Database থেকে ডেটা লোড করা
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE uuid = ?");
    $stmt->execute([$uuid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("Error: Application with PNR/UUID '$uuid' not found in database.");
    }

    // ২. JSON ডেটা ডিকোড করা
    $appData = isset($row['application_data']) && !empty($row['application_data'])
        ? json_decode($row['application_data'], true)
        : [];

    if (empty($appData)) {
        die("Error: Application data is empty or invalid for PNR/UUID: " . htmlspecialchars($uuid));
    }

    // ৩. ফরম্যাটিং ফাংশন
    function formatDate($dateString)
    {
        if (empty($dateString)) return '';

        $date = new DateTime($dateString);

        $day = $date->format('d');
        $month = strtoupper($date->format('M')); // DEC
        $year = $date->format('y');

        return $day . $month . $year;
    }

    function formatDateTime($dateTimeString)
    {
        if (empty($dateTimeString)) return '';

        $date = new DateTime($dateTimeString);

        $day = $date->format('D');       // Mon, Tue, Wed
        $fullDate = $date->format('d M y'); // Dec 03, 2025

        return $day . ",<br>" . $fullDate;
    }

    // === ৪. HTML কন্টেন্ট তৈরি শুরু ===
    ob_start();
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Thai Visa Cover Letter - <?php echo htmlspecialchars($uuid); ?></title>
        <style>
            @page {
                margin: 20mm 15mm;
            }

            body {
                font-size: 12px;
                line-height: 1.6;
                color: #000;
                margin: 0;
                padding: 0;
            }

            .container {
                max-width: 210mm;
                margin: 0 auto;
            }

            .letterhead {
                border-bottom: 2px solid #000;
                padding-bottom: 15px;
                margin-bottom: 25px;
            }

            .letterhead h1 {
                font-size: 24pt;
                font-weight: bold;
                text-align: center;
                margin: 0;
                color: #1a237e;
            }

            .letterhead p {
                text-align: center;
                margin: 5px 0;
                color: #333;
            }

            .date-section {
                text-align: right;
                margin-bottom: 30px;
            }

            .recipient {
                margin-bottom: 20px;
            }

            .subject {
                font-weight: bold;
                margin: 20px 0;
                font-size: 13pt;
            }

            .salutation {
                margin-bottom: 15px;
            }

            .content {
                text-align: justify;
                margin-bottom: 20px;
            }

            .signature {
                margin-top: 50px;
            }

            .signature-line {
                border-top: 1px solid #000;
                width: 250px;
                margin-top: 40px;
            }

            .applicant-info {
                font-weight: bold;
                margin: 5px 0;
            }

            .highlight {
                background-color: #fffacd;
                padding: 2px 4px;
                border-radius: 2px;
            }

            .section-title {
                font-weight: bold;
                margin-top: 20px;
                margin-bottom: 10px;
                color: #1a237e;
                border-bottom: 1px solid #ccc;
                padding-bottom: 5px;
            }

            .info-table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                font-size: 11pt;
            }

            .info-table th,
            .info-table td {
                border: 1px solid #000;
                padding: 8px 12px;
                text-align: left;
                vertical-align: top;
            }

            .info-table th {
                background-color: #f5f5f5;
                font-weight: bold;
                width: 30%;
            }

            .applicant-table {
                width: 100%;
                border-collapse: collapse;
                margin: 5px 0px;
                border: 1px solid;
            }

            .applicant-table th {
                background-color: #B0E0E6;
                padding: 10px;
                text-align: left;
                font-weight: bold;
                border-left: 1px solid;
                border-right: 1px solid;
            }

            .applicant-table td {
                border-left: 1px solid;
                border-right: 1px solid;
                padding: 8px 10px;
            }

            .applicant-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .footer {
                margin-top: 50px;
                font-size: 10pt;
                color: #666;
                text-align: center;
                border-top: 1px solid #eee;
                padding-top: 10px;
            }

            .page-break {
                page-break-before: always;
            }

            .header-right {
                float: right;
                text-align: right;
                font-size: 10pt;
                color: #666;
                margin-bottom: 20px;
            }

            .stamp-area {
                float: right;
                width: 150px;
                height: 80px;
                border: 2px dashed #ccc;
                text-align: center;
                padding-top: 25px;
                margin-top: 30px;
                font-size: 10pt;
                color: #999;
            }
        </style>
    </head>

    <body>
        <table style="width: 100%; font-size: 12px">
            <tr>
                <td style="width: 70%;">
                    <p><strong>TravHub Global Limited</strong></p>
                    <p>Address: House 12, Road 21, Sector 04</p>
                    <p>Uttara, Dhaka-1230, Bangladesh</p>
                    <p>Email: tarek@travhub.xyz</p>
                    <p>Phone: +8801611482773, +8801687867603</p>
                </td>
                <td style="width: 30%; text-align: right">
                    <img src="./assets/img/logo.png" style="height: 0.97in; width: 1.12in;" alt="Company Logo">
                </td>
            </tr>
        </table>
        <div class="container">
            <!-- Content -->
            <div class="content">

                <?php if (isset($appData['participants']) && !empty($appData['participants'])): ?>
                    <p><strong>Itinerary</strong></p>
                    <table class="applicant-table">
                        <thead>
                            <tr>
                                <th style="width: 35%;">Name</th>
                                <th style="width: 20%;">Passport No.</th>
                                <th style="width: 20%;">Frequent Flyer Number</th>
                                <th style="width: 25%;">Ticket</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo htmlspecialchars(($appData['surName'] ?? '') . '/' . ($appData['givenName']  ?? '') . ' ' . ($appData['salutation']  ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($appData['passportNo'] ?? ''); ?></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <?php foreach ($appData['participants'] as $index => $participant): ?>
                                <tr>
                                    <td style="border-top: 1px solid;">
                                        <?php
                                        echo strtoupper(
                                            htmlspecialchars(
                                                ($participant['surName'] ?? '') . '/' .
                                                    ($participant['givenName'] ?? '') . ' ' .
                                                    ($participant['salutation'] ?? '')
                                            )
                                        );
                                        ?>
                                    </td>
                                    <td style="border-top: 1px solid;"><?php echo htmlspecialchars($participant['passportNo'] ?? ''); ?></td>
                                    <td style="border-top: 1px solid;"><?php echo htmlspecialchars($participant[''] ?? ''); ?></td>
                                    <td style="border-top: 1px solid;"><?php echo formatDate($participant[''] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <table class="applicant-table">
                    <thead>
                        <tr>
                            <th style="width: 60%;">Airline PNR</th>
                            <th style="width: 20%;">Galileo PNR</th>
                            <th style="width: 20%;">Date of Issue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($appData['airlinePNR'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($appData['galileoPNR'] ?? ''); ?></td>
                            <td><?php echo formatDate($appData['dateOfIssue'] ?? ''); ?></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Flight Information -->
                <?php if (isset($appData['country']) && !empty($appData['country'])): ?>
                    <div style="margin-top: 20px;"><strong>Itinerary Information </strong></div>

                    <table class="applicant-table">
                        <thead>
                            <tr>
                                <th style="width: 12%;"><strong>Flight #</strong></th>
                                <th style="width: 15%;"><strong>From</strong></th>
                                <th style="width: 15%;"><strong>To</strong></th>
                                <th style="width: 15%;"><strong>Depart</strong></th>
                                <th style="width: 15%;"><strong>Arrive</strong></th>
                                <th style="width: 8%;"><strong>Seat</strong></th>
                                <th style="width: 20%;"><strong>Info</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($appData['country'] == "Thailand"): ?>

                                <!-- OUTBOUND -->
                                <tr>
                                    <td>
                                        <img src="./assets/img/THAI.png" alt="airline-logo">
                                        <p>THAI</p><br>
                                        <p><strong>TG 340</strong></p>
                                    </td>

                                    <td><strong>Dhaka</strong> - Hazrat Shahjalal Intl Arpt, Terminal 2</td>
                                    <td><strong>Bangkok</strong> - Suvarnabhumi Intl Arpt</td>

                                    <td><strong><?php echo formatDateTime($appData['travelStart']); ?><br><br>02:45</strong></td>
                                    <td><strong><?php echo formatDateTime($appData['travelStart']); ?><br><br>06:15</strong></td>

                                    <td></td>
                                    <td>Baggage : <br>Class : W-Economy <br>Duration : 2h 30m <br>Status : Confirmed <br>Aircraft : A320</td>
                                </tr>

                                <!-- RETURN -->
                                <tr>
                                    <td style="border-top: 1px solid;">
                                        <img src="./assets/img/THAI.png" alt="airline-logo">
                                        <p>THAI</p><br>
                                        <p><strong>TG 339</strong></p>
                                    </td>

                                    <td style="border-top: 1px solid;"><strong>Bangkok</strong> - Suvarnabhumi Intl Arpt</td>
                                    <td style="border-top: 1px solid;"><strong>Dhaka</strong> - Hazrat Shahjalal Intl Arpt, Terminal 2</td>

                                    <td style="border-top: 1px solid;"><strong><?php echo formatDateTime($appData['travelEnd']); ?><br><br>23:50</strong></td>
                                    <td style="border-top: 1px solid;"><strong><?php echo formatDateTime(date('Y-m-d', strtotime($appData['travelEnd'] . ' +1 day'))); ?><br><br>01:25</strong></td>

                                    <td style="border-top: 1px solid;"></td>
                                    <td style="border-top: 1px solid;">Baggage : <br>Class : W-Economy <br>Duration : 2h 35m <br>Status : Confirmed <br>Aircraft : A320</td>
                                </tr>

                            <?php elseif ($appData['country'] == "Malaysia"): ?>

                                <!-- OUTBOUND -->
                                <tr>
                                    <td>
                                        <img src="./assets/img/MALAYSIA.png" alt="airline-logo">
                                        <p>Malaysia Airlines</p><br>
                                        <p><strong>MH 103</strong></p>
                                    </td>

                                    <td><strong>Dhaka</strong> - Hazrat Shahjalal Intl Arpt, Terminal 2</td>
                                    <td><strong>Kuala Lumpur</strong> - KL International Arpt Terminal 1</td>

                                    <td><strong><?php echo formatDateTime($appData['travelStart']); ?><br><br>02:45</strong></td>
                                    <td><strong><?php echo formatDateTime($appData['travelStart']); ?><br><br>06:15</strong></td>

                                    <td></td>
                                    <td>Baggage : <br>Class : W-Economy <br>Duration : 2h 30m <br>Status : Confirmed <br>Aircraft : A320</td>
                                </tr>

                                <!-- RETURN -->
                                <tr>
                                    <td style="border-top: 1px solid;">
                                        <img src="./assets/img/MALAYSIA.png" alt="airline-logo">
                                        <p>Malaysia Airlines</p><br>
                                        <p><strong>MH 339</strong></p>
                                    </td>

                                    <td style="border-top: 1px solid;"><strong>Kuala Lumpur</strong> - KL International Arpt Terminal 1</td>
                                    <td style="border-top: 1px solid;"><strong>Dhaka</strong> - Hazrat Shahjalal Intl Arpt, Terminal 2</td>

                                    <td style="border-top: 1px solid;"><strong><?php echo formatDateTime($appData['travelEnd']); ?><br><br>23:50</strong></td>
                                    <td style="border-top: 1px solid;"><strong><?php echo formatDateTime(date('Y-m-d', strtotime($appData['travelEnd'] . ' +1 day'))); ?><br><br>01:25</strong></td>

                                    <td style="border-top: 1px solid;"></td>
                                    <td style="border-top: 1px solid;">Baggage : <br>Class : W-Economy <br>Duration : 2h 35m <br>Status : Confirmed <br>Aircraft : A320</td>
                                </tr>

                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            </div>
        </div>
    </body>

    </html>

<?php
    // ৫. HTML কন্টেন্ট ক্যাপচার
    $html = ob_get_clean();

    // ৬. mPDF ইনিশিয়ালাইজ
    $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'tempDir' => __DIR__ . '/tmp',
        'fontDir' => array_merge($fontDirs, [
            __DIR__ . '/fonts',
        ]),
        'fontdata' => $fontData + [
            'poppins' => [
                'R' => 'Poppins-Regular.ttf',
                'B' => 'Poppins-SemiBold.ttf',
                'I' => 'Poppins-Italic.ttf',
                'BI' => 'Poppins-BoldItalic.ttf',
            ]
        ],
        'default_font' => 'poppins'
    ]);

    // Set document information
    $mpdf->SetTitle('Thai Visa Cover Letter - ' . $uuid);
    $mpdf->SetAuthor('Thai Visa Application System');
    $mpdf->SetCreator('TravHub Global Limited');
    $mpdf->SetSubject('Visa Application Cover Letter');

    // Set watermark (optional)
    // $mpdf->SetWatermarkText('CONFIDENTIAL');
    // $mpdf->showWatermarkText = true;
    // $mpdf->watermark_font = 'DejaVuSansCondensed';
    // $mpdf->watermarkTextAlpha = 0.1;

    // ৭. HTML লেখা ও আউটপুট
    $mpdf->WriteHTML($html);

    // Output the PDF for download
    $filename = 'Thai_Visa_Cover_Letter_' . $uuid . '.pdf';
    $mpdf->Output($filename, 'I'); // 'D' for download

    exit;
} catch (Exception $e) {
    die("Error generating PDF: " . $e->getMessage());
}
?>