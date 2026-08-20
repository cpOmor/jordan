<?php
require_once __DIR__ . '/api/config.php';

function jordan_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        jordan_mysql_host(),
        jordan_mysql_port(),
        jordan_mysql_db(),
        jordan_mysql_charset()
    );

    $pdo = new PDO($dsn, jordan_mysql_user(), jordan_mysql_pass(), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function jordan_find_by_keyword(string $keyword): ?array
{
    $keyword = strtoupper(trim($keyword));
    if ($keyword === '') {
        return null;
    }

    $table = jordan_mysql_table();
    $pdo   = jordan_db();

    // 1. Exact match on passport, visa number, epayment no, or ID
    $sql = "SELECT id, visa_number, visa_type, date_of_issue, visa_period, visa_purpose, valid_until,
                   full_name, nationality, birth_date, passport_number, visa_fees, epayment_no,
                   payment_type, applicant_image, status, created_at
            FROM `{$table}`
            WHERE UPPER(passport_number) = :kw 
               OR UPPER(visa_number)     = :kw 
               OR UPPER(epayment_no)     = :kw
               OR id                     = :kw_int
            ORDER BY id DESC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':kw'     => $keyword,
        ':kw_int' => ctype_digit($keyword) ? (int) $keyword : -1
    ]);
    $row = $stmt->fetch();
    if (is_array($row)) {
        return $row;
    }

    // 2. Cleaned match (ignoring spaces or dashes)
    $cleanKw = preg_replace('/[^A-Z0-9]/', '', $keyword);
    if ($cleanKw !== '' && $cleanKw !== $keyword) {
        $sqlClean = "SELECT id, visa_number, visa_type, date_of_issue, visa_period, visa_purpose, valid_until,
                            full_name, nationality, birth_date, passport_number, visa_fees, epayment_no,
                            payment_type, applicant_image, status, created_at
                     FROM `{$table}`
                     WHERE REPLACE(REPLACE(UPPER(passport_number), ' ', ''), '-', '') = :clean_kw 
                        OR REPLACE(REPLACE(UPPER(visa_number), ' ', ''), '-', '')     = :clean_kw 
                        OR REPLACE(REPLACE(UPPER(epayment_no), ' ', ''), '-', '')     = :clean_kw
                     ORDER BY id DESC
                     LIMIT 1";
        $stmtClean = $pdo->prepare($sqlClean);
        $stmtClean->execute([':clean_kw' => $cleanKw]);
        $rowClean = $stmtClean->fetch();
        if (is_array($rowClean)) {
            return $rowClean;
        }
    }

    // 3. Fuzzy search on passport, visa number, epayment no, or full name
    $sqlLike = "SELECT id, visa_number, visa_type, date_of_issue, visa_period, visa_purpose, valid_until,
                       full_name, nationality, birth_date, passport_number, visa_fees, epayment_no,
                       payment_type, applicant_image, status, created_at
                FROM `{$table}`
                WHERE UPPER(passport_number) LIKE :kw_like 
                   OR UPPER(visa_number)     LIKE :kw_like 
                   OR UPPER(epayment_no)     LIKE :kw_like
                   OR UPPER(full_name)       LIKE :kw_like
                ORDER BY id DESC
                LIMIT 1";
    $stmtLike = $pdo->prepare($sqlLike);
    $stmtLike->execute([':kw_like' => '%' . $keyword . '%']);
    $rowLike = $stmtLike->fetch();

    return is_array($rowLike) ? $rowLike : null;
}

function jordan_fmt_date(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '-';
    }
    $ts = strtotime($value);
    return $ts !== false ? date('d/m/Y', $ts) : $value;
}

$searchValue  = '';
$errorMessage = '';
$record       = null;
$consoleError = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $searchValue = trim((string) ($_POST['keyword'] ?? $_POST['visa_number'] ?? ''));

    if ($searchValue === '') {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?err=empty');
        exit;
    }

    try {
        $found = jordan_find_by_keyword($searchValue);
        if (is_array($found)) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?verified=1&q=' . urlencode($searchValue));
            exit;
        }

        header('Location: ' . $_SERVER['PHP_SELF'] . '?err=not_found&q=' . urlencode($searchValue));
        exit;
    } catch (Throwable $error) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?err=server&msg=' . urlencode($error->getMessage()));
        exit;
    }
}

$verified    = ((string) ($_GET['verified'] ?? '') === '1');
$searchValue = trim((string) ($_GET['q'] ?? ''));

if ($searchValue !== '') {
    try {
        $record = jordan_find_by_keyword($searchValue);
        if (is_array($record)) {
            $verified = true;
        } else {
            $errCode = trim((string) ($_GET['err'] ?? ''));
            if ($errCode === '') {
                $errCode = 'not_found';
            }
        }
    } catch (Throwable $error) {
        $errorMessage = 'Server error. Please try again.';
        $consoleError = $error->getMessage();
    }
}

if ($errorMessage === '') {
    $errCode = trim((string) ($_GET['err'] ?? ''));
    if ($errCode === 'not_found') {
        $errorMessage = 'No Jordan visa found for this Passport Number or Visa Number.';
    } elseif ($errCode === 'server') {
        $errorMessage = 'Server error. Please try again.';
        $consoleError = trim((string) ($_GET['msg'] ?? ''));
    }
}

$selfPath = (string) ($_SERVER['PHP_SELF'] ?? 'index.php');
$jordanUrl = rtrim(jordan_env('JORDAN_URL', 'http://localhost/evisa/jordan'), '/');
$verifyUrl = $jordanUrl . '/?q=' . rawurlencode((string) ($record['passport_number'] ?? $searchValue));
$qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=0&data=' . rawurlencode($verifyUrl);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jordan E-Visa Verification - المملكة الأردنية الهاشمية</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Great+Vibes&family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            background: #f1f5f9;
            font-family: 'Open Sans', Arial, sans-serif;
            color: #0f172a;
        }

        .site-header {
            background: #595353;
            color: #ffffff;
            padding: 20px 16px;
            text-align: center;
            border-bottom: 4px solid #ce1126;
        }
        .site-header img { height: 60px; margin-bottom: 8px; }
        .site-header h1 { margin: 0; font-size: 22px; font-family: 'Amiri', serif; }
        .site-header p { margin: 4px 0 0; font-size: 14px; opacity: 0.9; }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 16px;
        }

        .search-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .search-card h2 { margin-top: 0; font-size: 22px; color: #1e293b; }
        .search-card p { color: #64748b; font-size: 14px; margin-bottom: 24px; }
        .search-form { display: flex; gap: 12px; max-width: 600px; margin: 0 auto; }
        .search-input {
            flex: 1;
            padding: 14px 18px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
        }
        .search-btn {
            background: #ce1126;
            color: #fff;
            border: none;
            padding: 14px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
        }
        .search-btn:hover { background: #b00e1f; }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }

        .verified-banner {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 18px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn-action {
            background: #1e293b;
            color: #fff;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            border: none;
        }
        .btn-action:hover { background: #0f172a; }

        /* ================= PDF Layout Styles (100% Identical to jordan-evisa-view.php) ================= */
        .page-container {
            width: 250mm;
            min-height: 297mm;
            background: #ffffff;
            margin: 20px auto;
            padding: 16mm 16mm 12mm 16mm;
            position: relative;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            page-break-after: always;
        }
        /* Top Header */
        .pdf-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .logo-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-left img {
            height: 58px;
        }
        .logo-right {
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: right;
        }
        .logo-right img {
            height: 62px;
        }
        .govt-title-ar {
            font-family: 'Amiri', serif;
            font-size: 16px;
            font-weight: 700;
            color: #111;
            direction: rtl;
            line-height: 1.2;
        }
        .govt-title-en {
            font-size: 12px;
            font-weight: 700;
            color: #222;
            line-height: 1.2;
        }
        
        .main-title {
            text-align: center;
            font-family: 'Amiri', serif;
            font-size: 24px;
            font-weight: 700;
            color: #000;
            margin: 12px 0 8px;
            direction: rtl;
        }
        .main-title span {
            font-family: 'Open Sans', sans-serif;
            font-size: 20px;
            font-weight: 800;
            direction: ltr;
        }

        .welcome-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding: 0 5px;
        }
        .welcome-en {
            font-family: 'Great Vibes', cursive;
            font-size: 28px;
            color: #c06c54;
        }
        .welcome-ar {
            font-family: 'Amiri', serif;
            font-size: 26px;
            font-weight: 700;
            color: #c06c54;
            direction: rtl;
        }

        /* Section Tables */
        .section-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background-color: #ffffff;
        }
        .section-header {
            background-color: #737373;
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            padding: 7px 10px;
            font-family: 'Open Sans', 'Amiri', sans-serif;
            border: 2px solid #ffffff;
        }
        .section-table td {
            padding: 6px 12px;
            font-size: 13px;
            background-color: #ebebeb;
            border: 2px solid #ffffff;
            vertical-align: middle;
        }
        .section-table tr td {
            background-color: #ebebeb;
        }
        .col-en-label {
            width: 25%;
            font-weight: 700;
            color: #000000;
            text-align: left;
        }
        .col-value {
            width: 50%;
            font-weight: 600;
            color: #000000;
            text-align: center;
        }
        .col-ar-label {
            width: 25%;
            font-weight: 700;
            color: #000000;
            text-align: right;
            font-family: 'Amiri', serif;
            font-size: 14px;
            direction: rtl;
        }

        /* Notes Section */
        .notes-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 10px;
            margin-bottom: 10px;
            padding: 0 5px;
        }
        .notes-col-en {
            width: 48%;
            font-size: 10px;
            line-height: 1.45;
            color: #333333;
        }
        .notes-col-en h4 {
            margin: 0 0 4px 0;
            font-size: 11px;
            font-weight: 800;
            color: #111;
        }
        .notes-col-ar {
            width: 48%;
            font-size: 11px;
            line-height: 1.45;
            color: #333333;
            text-align: right;
            direction: rtl;
            font-family: 'Amiri', serif;
        }
        .notes-col-ar h4 {
            margin: 0 0 4px 0;
            font-size: 12px;
            font-weight: 700;
            color: #111;
        }

        /* Footer Info & QR Code */
        .pdf-footer {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0 5px;
        }
        .footer-left-col {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .footer-right-col {
            text-align: right;
            direction: rtl;
        }
        .footer-en-link {
            font-size: 11px;
            font-weight: 700;
            color: #000000;
        }
        .footer-ar-link {
            font-size: 12px;
            font-weight: 700;
            color: #000000;
            font-family: 'Amiri', serif;
            direction: rtl;
        }
        .qr-box {
            width: 125px;
            height: 125px;
        }
        .qr-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Page 2 Styles */
        .p2-sub-bar {
            margin-bottom: 25px;
        }
        .p2-sub-bar .welcome-en {
            font-size: 26px;
        }
        .p2-sub-bar .p2-wish-en {
            font-size: 18px;
            color: #c06c54;
            font-style: italic;
            margin-top: 4px;
        }
        .p2-sub-bar .p2-wish-ar {
            font-family: 'Amiri', serif;
            font-size: 20px;
            font-weight: 700;
            color: #c06c54;
            margin-top: 4px;
            direction: rtl;
        }
        .p2-center-graphic {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            margin: 20px 0 15px;
            width: 100%;
        }
        .p2-center-graphic img {
            display: block;
            max-width: 100%;
            max-height: 110px;
            height: auto;
            object-fit: contain;
            margin: 0 auto;
        }
        .p2-text-block {
            display: flex;
            justify-content: space-between;
            gap: 25px;
            margin-bottom: 35px;
            padding: 0 10px;
        }
        .p2-en-text {
            width: 48%;
            font-size: 12px;
            line-height: 1.55;
            color: #222;
        }
        .p2-ar-text {
            width: 48%;
            font-size: 13px;
            line-height: 1.65;
            color: #222;
            text-align: right;
            direction: rtl;
            font-family: 'Amiri', serif;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0mm;
            }
            html, body {
                width: 210mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .site-header, .search-card, .verified-banner, .action-bar, .alert-error {
                display: none !important;
            }
            .page-container {
                width: 250mm !important;
                max-width: 250mm !important;
                min-height: 297mm !important;
                margin: 0 auto !important;
                padding: 16mm 16mm 12mm 16mm !important;
                box-shadow: none !important;
                border: none !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<header class="site-header">
    <img src="./images/logo2.png" alt="Emblem">
    <h1>المملكة الأردنية الهاشمية - وزارة الداخلية</h1>
    <p>Hashemite Kingdom of Jordan - E-Visa Verification Portal</p>
</header>

<div class="container">
    <?php if ($errorMessage !== ''): ?>
        <div class="alert-error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if (!$verified || !is_array($record)): ?>
        <div class="search-card">
            <h2>Verify Jordan E-Visa</h2>
            <p>Enter Passport Number, Visa Number, or E-Payment Number to verify official Jordan visa status.</p>
            <form class="search-form" method="POST" action="<?php echo htmlspecialchars($selfPath, ENT_QUOTES, 'UTF-8'); ?>">
                <input class="search-input" type="text" name="keyword" value="<?php echo htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Passport No / Visa No / E-Payment No" required>
                <button class="search-btn" type="submit">Verify Visa</button>
            </form>
        </div>
    <?php else: ?>
        <div class="verified-banner">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            Official Jordan E-Visa Verified & Valid
        </div>

        <div class="action-bar">
            <a href="<?php echo htmlspecialchars($selfPath, ENT_QUOTES, 'UTF-8'); ?>" class="btn-action">&larr; New Search</a>
            <button onclick="window.print()" class="btn-action">Print Official Document</button>
        </div>

        <!-- ================= PAGE 1 ================= -->
        <div class="page-container">
            <!-- Header -->
            <div class="pdf-header">
                <div class="logo-left">
                    <img src="./images/logo.png" alt="eVISA Jordan Logo">
                </div>
                <div class="logo-right">
                    <div>
                        <div class="govt-title-ar">المملكة الأردنية الهاشمية &ndash; وزارة الداخلية</div>
                        <div class="govt-title-en">The Hashemite Kingdom of Jordan - Ministry of Interior</div>
                    </div>
                    <img src="./images/logo2.png" alt="Jordan Coat of Arms">
                </div>
            </div>

            <!-- Main Title -->
            <div class="main-title">
                تأشيرة إلكترونية &ndash; <span>e-VISA</span>
            </div>

            <!-- Welcome Sub-header -->
            <div class="welcome-bar">
                <div class="welcome-en">Welcome to Jordan</div>
                <div class="welcome-ar">اهلا وسهلا بكم في الأردن</div>
            </div>

            <!-- Table 1: Visa Information -->
            <table class="section-table">
                <thead>
                    <tr>
                        <th colspan="3" class="section-header">Visa Information - معلومات التأشيرة</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="col-en-label">Visa Number</td>
                        <td class="col-value"><?php echo htmlspecialchars((string) ($record['visa_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">رقم التأشيرة</td>
                    </tr>
                    <tr>
                        <td class="col-en-label">Visa Type</td>
                        <td class="col-value"><?php echo htmlspecialchars((string) ($record['visa_type'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">نوع التأشيرة</td>
                    </tr>
                    <tr>
                        <td class="col-en-label">Date of Issue</td>
                        <td class="col-value"><?php echo htmlspecialchars(jordan_fmt_date((string) ($record['date_of_issue'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">تاريخ الإصدار</td>
                    </tr>
                    <tr>
                        <td class="col-en-label">Visa Period</td>
                        <td class="col-value"><?php echo htmlspecialchars((string) ($record['visa_period'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">مدة التأشيرة</td>
                    </tr>
                    <tr>
                        <td class="col-en-label">Visa Purpose</td>
                        <td class="col-value"><?php echo htmlspecialchars((string) ($record['visa_purpose'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">الغاية من التأشيرة</td>
                    </tr>
                    <tr>
                        <td class="col-en-label">Valid Until</td>
                        <td class="col-value"><?php echo htmlspecialchars(jordan_fmt_date((string) ($record['valid_until'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">صالحة لغاية</td>
                    </tr>
                </tbody>
            </table>

            <!-- Table 2: Visitor Information -->
            <table class="section-table">
                <thead>
                    <tr>
                        <th colspan="3" class="section-header">Visitor Information - معلومات الزائر</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="col-en-label">Full Name</td>
                        <td class="col-value"><?php echo htmlspecialchars((string) ($record['full_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">الاسم الكامل</td>
                    </tr>
                    <tr>
                        <td class="col-en-label">Nationality</td>
                        <td class="col-value"><?php echo htmlspecialchars((string) ($record['nationality'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">الجنسية</td>
                    </tr>
                    <tr>
                        <td class="col-en-label">Birth Date</td>
                        <td class="col-value"><?php echo htmlspecialchars(jordan_fmt_date((string) ($record['birth_date'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">تاريخ الميلاد</td>
                    </tr>
                    <tr>
                        <td class="col-en-label">Passport Number</td>
                        <td class="col-value"><?php echo htmlspecialchars((string) ($record['passport_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">رقم جواز السفر</td>
                    </tr>
                </tbody>
            </table>

            <!-- Table 3: Payment Information -->
            <table class="section-table">
                <thead>
                    <tr>
                        <th colspan="3" class="section-header">Payment Information - معلومات الدفع</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="col-en-label">Visa Fees</td>
                        <td class="col-value"><?php echo htmlspecialchars((string) ($record['visa_fees'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">رسوم التأشيرة</td>
                    </tr>
                    <tr>
                        <td class="col-en-label">E-Payment No.</td>
                        <td class="col-value"><?php echo htmlspecialchars((string) ($record['epayment_no'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">رقم الدفع الإلكتروني</td>
                    </tr>
                    <tr>
                        <td class="col-en-label">Payment Type</td>
                        <td class="col-value"><?php echo htmlspecialchars((string) ($record['payment_type'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="col-ar-label">طريقة الدفع</td>
                    </tr>
                </tbody>
            </table>

            <!-- Notes Section -->
            <div class="notes-container">
                <div class="notes-col-en">
                    <h4>Notes</h4>
                    <div>1) This visa was issued based on the information you entered, and you are responsible for its validity. The Jordanian authorities reserve the right to cancel the visa if the data provided is inconsistent with the information on your passport</div>
                    <div style="margin-top: 4px;">2) This electronic visa or a copy of it shall be presented to the concerned officer at the border port</div>
                    <div style="margin-top: 4px;">3) This visa enables you to stay in Jordan for a period of 90 days from the date of entry, if you wish to extend your stay, please visit the nearest security center to your place of residence</div>
                </div>
                <div class="notes-col-ar">
                    <h4>ملاحظات</h4>
                    <div>1) تم منحك هذه التأشيرة بناء على المعلومات المدخلة من قبلك والتي تتحمل مسؤولية صحتها، ولالسلطات الأردنية الحق في إلغاء التأشيرة في حال عدم مطابقتها لمعلومات جواز السفر</div>
                    <div style="margin-top: 4px;">2) يجب إبراز هذه التأشيرة إلكترونياً أو صورة عنها للموظف المعني في المركز الحدودي</div>
                    <div style="margin-top: 4px;">3) تمكنك هذه التأشيرة من الإقامة في الأردن لمدة 90 يوماً من تاريخ الدخول، وفي حال رغبتك بتمديد فترة الإقامة يرجى مراجعة أقرب مركز أمني لمكان سكنك</div>
                </div>
            </div>

            <!-- Footer & QR -->
            <div class="pdf-footer">
                <div class="footer-left-col">
                    <div class="footer-en-link">For any information about E-Visa : moi-gov-joe-e-applications.info</div>
                    <div class="qr-box">
                        <img src="<?php echo htmlspecialchars($qrImageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code">
                    </div>
                </div>
                <div class="footer-right-col">
                    <div class="footer-ar-link">moi-gov-joe-e-applications.info للتواصل حول أي معلومات تتعلق بالتأشيرة الإلكترونية</div>
                </div>
            </div>
        </div>

        <!-- ================= PAGE 2 ================= -->
        <div class="page-container">
            <!-- Header -->
            <div class="pdf-header">
                <div class="logo-left">
                    <img src="./images/logo.png" alt="eVISA Jordan Logo">
                </div>
                <div class="logo-right">
                    <div>
                        <div class="govt-title-ar">المملكة الأردنية الهاشمية &ndash; وزارة الداخلية</div>
                        <div class="govt-title-en">The Hashemite Kingdom of Jordan - Ministry of Interior</div>
                    </div>
                    <img src="./images/logo2.png" alt="Jordan Coat of Arms">
                </div>
            </div>

            <!-- Sub-header -->
            <div class="welcome-bar p2-sub-bar">
                <div>
                    <div class="welcome-en">Welcome to Jordan</div>
                    <div class="p2-wish-en">We wish you a safe trip and a pleasant stay</div>
                </div>
                <div style="text-align: right;">
                    <div class="welcome-ar">اهلا وسهلا بكم في الأردن</div>
                    <div class="p2-wish-ar">نتمنى لكم رحلة موفقة وإقامة سعيدة</div>
                </div>
            </div>

            <!-- Section 1: Tourism -->
            <div class="p2-center-graphic">
                <img src="./images/log3.png" alt="Jordan Flag Graphic">
            </div>
            <div class="p2-text-block">
                <div class="p2-en-text">
                    Jordan is a charming and fascinating country with a rich history, diverse cultural heritage, as well as a generous and hospitable people. If you wish to learn more about Jordan, and explore various tourist and historical sites, please visit www.visitjordan.com and @VisitJordan on all social media pages.
                </div>
                <div class="p2-ar-text">
                    الأردن بلد جميل ومثير للاهتمام، مع تاريخ غني وتراث ثقافي متنوع، وشعب كريم ومضياف، إذا كنتم ترغبون بمعرفة المزيد من المعلومات واستكشاف المناطق السياحية والتاريخية في الأردن، قم بزيارة موقع www.visitjordan.com وصفحات وسائل التواصل الاجتماعي VisitJordan@
                </div>
            </div>

            <!-- Section 2: Amen 911 App -->
            <div class="p2-center-graphic">
                <img src="./images/logo4.png" alt="Amen 911 Icon">
            </div>
            <div class="p2-text-block">
                <div class="p2-en-text">
                    Dear visitor, you can download the "Amen 911" application from your smartphones app store to learn about emergency numbers, weather conditions, roads, as well as submitting any reports.For emergencies, dial 911.
                </div>
                <div class="p2-ar-text">
                    عزيزي الزائر، يمكنك تحميل تطبيق أمن 911 من متاجر التطبيقات للهواتف الذكية لمعرفة أرقام الطوارئ وحالة الطقس والطرق وتقديم البلاغات كما يمكنك الاتصال مباشرة على الرقم 911 لحالات الطوارئ
                </div>
            </div>

            <!-- Bottom Left QR Code -->
            <div class="pdf-footer" style="margin-top: 60px;">
                <div class="qr-box">
                    <img src="<?php echo htmlspecialchars($qrImageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code">
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($consoleError)): ?>
<script>
    console.error("Jordan Visa Verification DB Error:", <?php echo json_encode($consoleError); ?>);
</script>
<?php endif; ?>

</body>
</html>
