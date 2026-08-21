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
    if ($errCode === 'not_found' || ($searchValue !== '' && !$verified)) {
        $errorMessage = 'Not found visa: No Jordan visa found for this Passport Number or Visa Number.';
    } elseif ($errCode === 'empty') {
        $errorMessage = 'Please enter a Passport Number or Visa Number.';
    } elseif ($errCode === 'server') {
        $errorMessage = 'Server error. Please try again.';
        $consoleError = trim((string) ($_GET['msg'] ?? ''));
    }
}

$selfPath = (string) ($_SERVER['PHP_SELF'] ?? 'visa-status.php');
$jordanUrl = rtrim(jordan_env('JORDAN_URL', 'http://localhost/evisa/jordan'), '/');
$verifyUrl = $jordanUrl . '/visa-status.php?verified=1&q=' . rawurlencode((string) ($record['passport_number'] ?? $searchValue));
$qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&margin=0&data=' . rawurlencode($verifyUrl);

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($verified && is_array($record)): ?>
    <!-- ================= VERIFIED VISA DISPLAY MODE ================= -->
    <div class="verified-header-bar">
        <span style="font-size: 22px;">&#10004;</span> Official Jordan E-Visa Verified & Valid
    </div>

    <div class="action-bar">
        <a href="visa-status.php" class="btn-action">&larr; Back to Search</a>
        <button onclick="window.print()" class="btn-action">Print Official Document</button>
    </div>

    <!-- PAGE 1 -->
    <div class="page-container">
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

        <div class="main-title">
            تأشيرة إلكترونية &ndash; <span>e-VISA</span>
        </div>

        <div class="welcome-bar">
            <div class="welcome-en">Welcome to Jordan</div>
            <div class="welcome-ar">اهلا وسهلا بكم في الأردن</div>
        </div>

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

    <!-- PAGE 2 -->
    <div class="page-container">
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

        <div class="pdf-footer" style="margin-top: 60px;">
            <div class="qr-box">
                <img src="<?php echo htmlspecialchars($qrImageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code">
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ================= SEARCH APPLICATIONS FORM PAGE (EXACT MATCH TO IMAGE 3) ================= -->
    <main class="search-app-container">
        <h2 class="search-app-title">Search Applications</h2>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert-not-found">
                <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="search-app-radio-group">
            <input type="radio" id="searchType" name="searchType" checked readonly>
            <label for="searchType">Search by Passport / Visa Number</label>
        </div>

        <form method="POST" action="visa-status.php">
            <div class="search-form-grid">
                <div class="form-group-item">
                    <label for="passport_num">Passport / Visa Number</label>
                    <input type="text" id="passport_num" name="keyword" class="form-control-input" placeholder="Enter passport or visa number" value="<?php echo htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="form-group-item">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" class="form-control-input" placeholder="dd/mm/yyyy">
                </div>

                <div class="form-group-item">
                    <div class="captcha-box-display">6 Y 6 N 4 S</div>
                </div>

                <div class="form-group-item">
                    <label for="captcha">* Confirmation Code</label>
                    <input type="text" id="captcha" name="captcha" class="form-control-input" placeholder="Enter code above">
                </div>
            </div>

            <div class="search-btn-actions">
                <button type="submit" class="btn-red-search">Search</button>
                <a href="index.php" class="btn-outline-back">Back to Home Page</a>
            </div>
        </form>
    </main>
<?php endif; ?>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
