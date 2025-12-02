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
        return $date->format('F d, Y');
    }

    function formatDateTime($dateTimeString)
    {
        if (empty($dateTimeString)) return '';
        $date = new DateTime($dateTimeString);
        return $date->format('M d, Y \a\t h:i A');
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
                font-family: 'Times New Roman', Times, serif;
                font-size: 12pt;
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
                margin: 0px;
                font-size: 11pt;
            }

            .applicant-table th {
                background-color: #B0E0E6;
                padding: 10px;
                text-align: left;
                font-weight: bold;
            }

            .applicant-table td {
                border: 1px solid #ddd;
                padding: 8px 10px;
            }

            .applicant-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .flight-table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                font-size: 10pt;
            }

            .flight-table th {
                background-color: #e8eaf6;
                color: #B0E0E6;
                padding: 8px;
                text-align: left;
                font-weight: bold;
                border: 1px solid #ddd;
            }

            .flight-table td {
                border: 1px solid #ddd;
                padding: 6px 8px;
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
                                <th>Name</th>
                                <th>Passport No.</th>
                                <th>Frequent Flyer Number</th>
                                <th>Ticket</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong><?php echo htmlspecialchars(($appData['givenName'] ?? '') . ' ' . ($appData['surName'] ?? '')); ?></strong></td>
                                <td><?php echo htmlspecialchars($appData['passportNo'] ?? ''); ?></td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                            <?php foreach ($appData['participants'] as $index => $participant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(($participant['givenName'] ?? '') . ' ' . ($participant['surName'] ?? '')); ?></td>
                                    <td><?php echo htmlspecialchars($participant['passportNo'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($participant[''] ?? ''); ?></td>
                                    <td><?php echo formatDate($participant[''] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <!-- Applicant Information Section -->
                <div class="section-title">Applicant Information</div>

                <table class="info-table">
                    <tr>
                        <th>Full Name</th>
                        <td><?php echo htmlspecialchars(($appData['givenName'] ?? '') . ' ' . ($appData['surName'] ?? '')); ?></td>
                    </tr>
                    <tr>
                        <th>Nationality</th>
                        <td><?php echo htmlspecialchars($appData['nationality'] ?? 'Bangladeshi'); ?></td>
                    </tr>
                    <tr>
                        <th>Passport Number</th>
                        <td><?php echo htmlspecialchars($appData['passportNo'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        <td><?php echo formatDate($appData['dob'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th>Mobile Number</th>
                        <td><?php echo htmlspecialchars($appData['mobile'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th>Email Address</th>
                        <td><?php echo htmlspecialchars($appData['email'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th>Permanent Address</th>
                        <td>
                            <?php
                            $address = [];
                            if (!empty($appData['permanentAddress1'])) $address[] = $appData['permanentAddress1'];
                            if (!empty($appData['permanentAddress2'])) $address[] = $appData['permanentAddress2'];
                            if (!empty($appData['permanentCity'])) $address[] = $appData['permanentCity'];
                            if (!empty($appData['permanentState'])) $address[] = $appData['permanentState'];
                            if (!empty($appData['permanentZip'])) $address[] = $appData['permanentZip'];
                            echo htmlspecialchars(implode(', ', $address));
                            ?>
                        </td>
                    </tr>
                </table>

                <!-- Profession Information -->
                <?php if (!empty($appData['profession'])): ?>
                    <div class="section-title">Professional Information</div>

                    <table class="info-table">
                        <tr>
                            <th>Profession</th>
                            <td><?php echo htmlspecialchars($appData['profession']); ?></td>
                        </tr>

                        <?php if ($appData['profession'] === 'Employee'): ?>
                            <tr>
                                <th>Job Title</th>
                                <td><?php echo htmlspecialchars($appData['jobTitle'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Employer Name</th>
                                <td><?php echo htmlspecialchars($appData['employerName'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Employment Since</th>
                                <td><?php echo formatDate($appData['employmentStart'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Office Location</th>
                                <td><?php echo htmlspecialchars($appData['officeLocation'] ?? ''); ?></td>
                            </tr>
                        <?php elseif ($appData['profession'] === 'Business'): ?>
                            <tr>
                                <th>Business Role</th>
                                <td><?php echo htmlspecialchars($appData['businessRole'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Business Name</th>
                                <td><?php echo htmlspecialchars($appData['businessName'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Nature of Business</th>
                                <td><?php echo htmlspecialchars($appData['businessNature'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Business Since</th>
                                <td><?php echo htmlspecialchars($appData['businessStartYear'] ?? ''); ?></td>
                            </tr>
                        <?php elseif ($appData['profession'] === 'Doctor'): ?>
                            <tr>
                                <th>BMDC Registration No.</th>
                                <td><?php echo htmlspecialchars($appData['doctorRegNo'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Position</th>
                                <td><?php echo htmlspecialchars($appData['doctorPosition'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Hospital Name</th>
                                <td><?php echo htmlspecialchars($appData['hospitalName'] ?? ''); ?></td>
                            </tr>
                        <?php elseif ($appData['profession'] === 'Lawyer'): ?>
                            <tr>
                                <th>Bar Council Enrollment No.</th>
                                <td><?php echo htmlspecialchars($appData['lawyerEnrollNo'] ?? ''); ?></td>
                            </tr>
                            <tr>
                                <th>Law Firm Name</th>
                                <td><?php echo htmlspecialchars($appData['lawFirmName'] ?? ''); ?></td>
                            </tr>
                        <?php elseif ($appData['profession'] === 'Other'): ?>
                            <tr>
                                <th>Profession Details</th>
                                <td><?php echo htmlspecialchars($appData['professionOther'] ?? ''); ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                <?php endif; ?>

                <!-- Travel Information -->
                <div class="section-title">Travel Information</div>

                <table class="info-table">
                    <tr>
                        <th>Visa Type</th>
                        <td><?php echo htmlspecialchars($appData['visaType'] ?? 'Tourist Visa'); ?></td>
                    </tr>
                    <tr>
                        <th>Destination Country</th>
                        <td><?php echo htmlspecialchars($appData['country'] ?? 'Thailand'); ?></td>
                    </tr>
                    <tr>
                        <th>Travel Dates</th>
                        <td><?php echo formatDate($appData['travelStart'] ?? ''); ?> to <?php echo formatDate($appData['travelEnd'] ?? ''); ?></td>
                    </tr>
                    <tr>
                        <th>Departure City</th>
                        <td><?php echo htmlspecialchars($appData['departureCity'] ?? 'Dhaka'); ?></td>
                    </tr>
                    <tr>
                        <th>Destination City</th>
                        <td><?php echo htmlspecialchars($appData['destinationCity'] ?? 'Bangkok'); ?></td>
                    </tr>
                    <tr>
                        <th>Accommodation</th>
                        <td>
                            <?php if (!empty($appData['hotelName'])): ?>
                                <strong><?php echo htmlspecialchars($appData['hotelName']); ?></strong><br>
                                <?php echo htmlspecialchars($appData['hotelAddress'] ?? ''); ?>
                            <?php else: ?>
                                To be arranged upon arrival
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <!-- Flight Information -->
                <?php if (isset($appData['flightItineraries']) && !empty($appData['flightItineraries'])): ?>
                    <div class="section-title">Flight Itinerary</div>

                    <table class="flight-table">
                        <thead>
                            <tr>
                                <th>Airline</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Departure</th>
                                <th>Arrival</th>
                                <th>Class</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appData['flightItineraries'] as $flight): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($flight['airline'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($flight['from'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($flight['to'] ?? ''); ?></td>
                                    <td><?php echo formatDateTime($flight['depart'] ?? ''); ?></td>
                                    <td><?php echo formatDateTime($flight['arrive'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($flight['class'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($flight['status'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <p><strong>Airline PNR:</strong> <?php echo htmlspecialchars($appData['airlinePNR'] ?? ''); ?> |
                        <strong>Galileo PNR:</strong> <?php echo htmlspecialchars($appData['galileoPNR'] ?? ''); ?> |
                        <strong>Date of Issue:</strong> <?php echo formatDate($appData['dateOfIssue'] ?? ''); ?>
                    </p>
                <?php endif; ?>

                <!-- Travel History -->
                <div class="section-title">Travel History</div>

                <table class="info-table">
                    <tr>
                        <th>Previous International Travel</th>
                        <td>
                            <?php if (!empty($appData['travelHistoryList'])): ?>
                                <?php echo htmlspecialchars($appData['travelHistoryList']); ?>
                            <?php else: ?>
                                First international trip
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if (!empty($appData['visitedCountryBefore']) && $appData['visitedCountryBefore']): ?>
                        <tr>
                            <th>Previously Visited <?php echo htmlspecialchars($appData['country'] ?? 'Thailand'); ?></th>
                            <td>Yes</td>
                        </tr>
                        <tr>
                            <th>Last Visit Date</th>
                            <td><?php echo formatDate($appData['lastCountryVisit'] ?? ''); ?></td>
                        </tr>
                        <tr>
                            <th>Previous Visa Number</th>
                            <td><?php echo htmlspecialchars($appData['previousVisaNo'] ?? ''); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <th>Previously Visited <?php echo htmlspecialchars($appData['country'] ?? 'Thailand'); ?></th>
                            <td>No, first visit</td>
                        </tr>
                    <?php endif; ?>
                </table>

                <!-- Declaration -->
                <div class="section-title">Declaration</div>

                <p>I hereby declare that all the information provided in this application is true, complete, and correct to the best of my knowledge. I understand that any false or misleading information may result in the refusal of my visa application.</p>

                <p>I undertake to abide by the laws and regulations of <?php echo htmlspecialchars($appData['country'] ?? 'Thailand'); ?> during my stay and to depart before the expiry of my authorized period of stay.</p>

                <p>I kindly request you to review my application and grant me the requested visa. Should you require any further information or documentation, please do not hesitate to contact me.</p>
            </div>

            <!-- Signature -->
            <div class="signature">
                <p>Thank you for your consideration.</p>

                <p>Sincerely,</p>

                <div class="signature-line"></div>

                <p class="applicant-info"><?php echo htmlspecialchars(($appData['givenName'] ?? '') . ' ' . ($appData['surName'] ?? '')); ?></p>

                <?php if (!empty($appData['profession'])): ?>
                    <p class="applicant-info">
                        <?php if ($appData['profession'] === 'Employee'): ?>
                            <?php echo htmlspecialchars($appData['jobTitle'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($appData['employerName'] ?? ''); ?>
                        <?php elseif ($appData['profession'] === 'Business'): ?>
                            <?php echo htmlspecialchars($appData['businessRole'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($appData['businessName'] ?? ''); ?>
                        <?php elseif ($appData['profession'] === 'Doctor'): ?>
                            <?php echo htmlspecialchars($appData['doctorPosition'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($appData['hospitalName'] ?? ''); ?>
                        <?php elseif ($appData['profession'] === 'Lawyer'): ?>
                            Advocate<br>
                            <?php echo htmlspecialchars($appData['lawFirmName'] ?? ''); ?>
                        <?php else: ?>
                            <?php echo htmlspecialchars($appData['profession']); ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>

                <p class="applicant-info">
                    Mobile: <?php echo htmlspecialchars($appData['mobile'] ?? ''); ?><br>
                    Email: <?php echo htmlspecialchars($appData['email'] ?? ''); ?>
                </p>
            </div>

            <!-- Stamp Area for Embassy -->
            <div class="stamp-area">
                For Official Use Only<br>
                (Embassy Stamp & Signature)
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>--- This is a computer-generated cover letter. Original documents and passport must be submitted with the application. ---</p>
                <p>Application ID: <?php echo htmlspecialchars($uuid); ?> | Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>

        </div>
    </body>

    </html>

<?php
    // ৫. HTML কন্টেন্ট ক্যাপচার
    $html = ob_get_clean();

    // ৬. mPDF ইনিশিয়ালাইজ
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'tempDir' => __DIR__ . '/tmp',
        'margin_top' => 20,
        'margin_bottom' => 20,
        'margin_left' => 15,
        'margin_right' => 15,

        'fontDir' => array_merge((new Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'], [
            __DIR__ . '/fonts',
        ]),

        'fontdata' => array_merge((new Mpdf\Config\FontVariables())->getDefaults()['fontdata'], [
            'poppins' => [
                'R' => 'Poppins-Regular.ttf',      // Regular
                'B' => 'Poppins-SemiBold.ttf',     // Bold
                'I' => 'Poppins-Italic.ttf',       // Italic (optional)
                'BI' => 'Poppins-BoldItalic.ttf',  // Bold Italic (optional)
            ]
        ]),

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