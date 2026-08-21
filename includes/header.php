<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hashemite Kingdom of Jordan - E-Visa Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Great+Vibes&family=Open+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Open Sans', Arial, sans-serif;
            background-color: #ffffff;
            color: #334155;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1;
        }

        /* Top Header Navbar */
        .site-nav-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .nav-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-logo-group {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }
        .nav-logo-group img.brand-logo {
            height: 48px;
            object-fit: contain;
        }
        .nav-logo-group img.emblem-logo {
            height: 42px;
            object-fit: contain;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 22px;
            list-style: none;
        }
        .nav-links a {
            color: #1e293b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        .nav-links a:hover,
        .nav-links a.active {
            color: #2a9d68;
        }
        .search-icon-btn {
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            color: #1e293b;
            padding: 4px 8px;
            display: flex;
            align-items: center;
            font-weight: bold;
            text-decoration: none;
        }
        .btn-apply-visa {
            background-color: #2a9d68;
            color: #ffffff !important;
            padding: 10px 22px;
            border-radius: 22px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: background-color 0.2s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        .btn-apply-visa:hover {
            background-color: #238457;
        }

        /* Mobile Hamburger & Mobile Header */
        .mobile-header-bar {
            display: none;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 10px 16px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }
        .mobile-hamburger {
            background: none;
            border: none;
            font-size: 24px;
            color: #1e293b;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
        }
        .mobile-logo-center {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .mobile-logo-center img {
            height: 38px;
        }
        .mobile-search-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: #1e293b;
            cursor: pointer;
            padding: 4px;
            text-decoration: none;
        }
        .mobile-nav-drawer {
            display: none;
            flex-direction: column;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 20px;
            gap: 12px;
        }
        .mobile-nav-drawer.active {
            display: flex;
        }
        .mobile-nav-drawer a {
            color: #1e293b;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Hero Banner */
        .hero-banner {
            position: relative;
            width: 100%;
            height: 380px;
            background: url('./images/air.png') center center / cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 40px 60px 24px 60px;
            color: #ffffff;
        }
        .hero-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 50%, rgba(0,0,0,0.65) 100%);
            z-index: 1;
        }
        .hero-content-top {
            position: relative;
            z-index: 2;
        }
        .hero-content-top h1 {
            font-size: 40px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .hero-content-bottom {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #f1f5f9;
            font-weight: 500;
        }
        .hero-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .hero-share {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        /* Main Section Container */
        .main-content {
            max-width: 1140px;
            margin: 40px auto;
            padding: 0 24px;
        }

        /* Section 1: Top Green Heading & Card Box */
        .page-title {
            color: #2a9d68;
            font-size: 30px;
            font-weight: 700;
            margin-bottom: 24px;
        }
        .title-card-row {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 50px;
        }
        .title-card {
            background-color: #f1f4f6;
            border-radius: 12px;
            padding: 35px 40px;
            flex: 1;
        }
        .title-card h2 {
            font-size: 26px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.35;
        }
        .btn-visa-status {
            background-color: #2a9d68;
            color: #ffffff !important;
            border: none;
            padding: 12px 32px;
            border-radius: 25px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease;
        }
        .btn-visa-status:hover {
            background-color: #238457;
        }

        /* Alternating Content Rows */
        .content-row {
            display: flex;
            align-items: center;
            gap: 50px;
            margin-bottom: 60px;
        }
        .content-row.reverse {
            flex-direction: row-reverse;
        }
        .content-text {
            flex: 1;
        }
        .content-text h3 {
            color: #2a9d68;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.35;
        }
        .content-text p {
            color: #475569;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 14px;
        }
        .content-text h4 {
            color: #2a9d68;
            font-size: 15px;
            font-weight: 700;
            margin-top: 14px;
            margin-bottom: 6px;
        }
        .content-text ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .content-text ul li {
            color: #475569;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 8px;
        }
        .content-image {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        .content-image img {
            width: 100%;
            height: 270px;
            object-fit: cover;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        .img-curve-right {
            border-radius: 0 90px 90px 0;
        }
        .img-curve-left {
            border-radius: 90px 0 0 90px;
        }

        /* Important Tips Section */
        .tips-section {
            margin-top: 60px;
            margin-bottom: 40px;
        }
        .tips-section h3 {
            color: #2a9d68;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .tip-item {
            margin-bottom: 16px;
        }
        .tip-item h4 {
            color: #2a9d68;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .tip-item p {
            color: #475569;
            font-size: 14px;
            line-height: 1.65;
        }

        /* Rating Divider */
        .rating-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .rating-text {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
        }
        .stars {
            color: #f59e0b;
            margin-left: 8px;
            font-size: 16px;
        }

        /* Search Applications Page Styles (Image 3) */
        .search-app-container {
            max-width: 960px;
            margin: 50px auto 70px auto;
            padding: 0 24px;
        }
        .search-app-title {
            color: #8b1c20;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .search-app-radio-group {
            margin-bottom: 24px;
            font-size: 14px;
            color: #334155;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-app-radio-group input[type="radio"] {
            accent-color: #8b1c20;
            width: 18px;
            height: 18px;
        }
        .search-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px 30px;
            margin-bottom: 35px;
        }
        .form-group-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .form-group-item label {
            font-size: 14px;
            color: #475569;
            font-weight: 500;
        }
        .form-control-input {
            width: 100%;
            padding: 13px 16px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            color: #1e293b;
        }
        input[type="date"].form-control-input {
            cursor: pointer;
        }
        .form-control-input:focus {
            background-color: #ffffff;
            border-color: #8b1c20;
            box-shadow: 0 0 0 3px rgba(139, 28, 32, 0.1);
        }
        .captcha-box-display {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 10px 20px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: 'Courier New', Courier, monospace;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 6px;
            color: #8b1c20;
            width: fit-content;
            user-select: none;
            background-image: repeating-linear-gradient(45deg, #f1f5f9, #f1f5f9 10px, #ffffff 10px, #ffffff 20px);
        }
        .search-btn-actions {
            display: flex;
            gap: 16px;
            margin-top: 10px;
        }
        .btn-red-search {
            background-color: #8b1c20;
            color: #ffffff;
            border: none;
            padding: 12px 36px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .btn-red-search:hover {
            background-color: #721619;
        }
        .btn-outline-back {
            background-color: #ffffff;
            color: #8b1c20;
            border: 1px solid #8b1c20;
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }
        .btn-outline-back:hover {
            background-color: #fef2f2;
        }

        .alert-not-found {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
        }

        /* Floating Back To Top Button (Image 2) */
        .back-to-top-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 44px;
            height: 44px;
            background-color: #2a9d68;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 20px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease;
            border: none;
        }
        .back-to-top-btn:hover {
            background-color: #238457;
            transform: translateY(-3px);
        }

        /* Footer */
        .site-footer {
            background-color: #1e2229;
            color: #94a3b8;
            padding: 50px 0 25px 0;
            font-size: 13px;
            margin-top: auto;
        }
        .footer-container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .footer-col h4 {
            color: #ffffff;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .footer-col p {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .footer-logo {
            height: 55px;
            margin-bottom: 14px;
            object-fit: contain;
        }
        .app-badges {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .app-badges img {
            height: 38px;
            border-radius: 6px;
            transition: opacity 0.2s ease;
        }
        .app-badges img:hover {
            opacity: 0.85;
        }
        .footer-bottom {
            border-top: 1px solid #334155;
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            margin-right: 18px;
            transition: color 0.2s ease;
        }
        .footer-links a:hover {
            color: #ffffff;
        }
        .social-links {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .social-links a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        /* Verified Printable PDF Layout Styles */
        .verified-header-bar {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 16px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 900px;
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
            max-width: 900px;
            margin: 0 auto 20px auto;
            padding: 0 16px;
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
        .pdf-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .logo-left { display: flex; align-items: center; gap: 10px; }
        .logo-left img { height: 58px; }
        .logo-right { display: flex; align-items: center; gap: 10px; text-align: right; }
        .logo-right img { height: 62px; }
        .govt-title-ar { font-family: 'Amiri', serif; font-size: 16px; font-weight: 700; color: #111; direction: rtl; line-height: 1.2; }
        .govt-title-en { font-size: 12px; font-weight: 700; color: #222; line-height: 1.2; }
        
        .main-title { text-align: center; font-family: 'Amiri', serif; font-size: 24px; font-weight: 700; color: #000; margin: 12px 0 8px; direction: rtl; }
        .main-title span { font-family: 'Open Sans', sans-serif; font-size: 20px; font-weight: 800; direction: ltr; }

        .welcome-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding: 0 5px; }
        .welcome-en { font-family: 'Great Vibes', cursive; font-size: 28px; color: #c06c54; }
        .welcome-ar { font-family: 'Amiri', serif; font-size: 26px; font-weight: 700; color: #c06c54; direction: rtl; }

        .section-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; background-color: #ffffff; }
        .section-header { background-color: #737373; color: #ffffff; font-size: 15px; font-weight: 700; text-align: center; padding: 7px 10px; font-family: 'Open Sans', 'Amiri', sans-serif; border: 2px solid #ffffff; }
        .section-table td { padding: 6px 12px; font-size: 13px; background-color: #ebebeb; border: 2px solid #ffffff; vertical-align: middle; }
        .section-table tr td { background-color: #ebebeb; }
        .col-en-label { width: 25%; font-weight: 700; color: #000000; text-align: left; }
        .col-value { width: 50%; font-weight: 600; color: #000000; text-align: center; }
        .col-ar-label { width: 25%; font-weight: 700; color: #000000; text-align: right; font-family: 'Amiri', serif; font-size: 14px; direction: rtl; }

        .notes-container { display: flex; justify-content: space-between; gap: 20px; margin-top: 10px; margin-bottom: 10px; padding: 0 5px; }
        .notes-col-en { width: 48%; font-size: 10px; line-height: 1.45; color: #333333; }
        .notes-col-en h4 { margin: 0 0 4px 0; font-size: 11px; font-weight: 800; color: #111; }
        .notes-col-ar { width: 48%; font-size: 11px; line-height: 1.45; color: #333333; text-align: right; direction: rtl; font-family: 'Amiri', serif; }
        .notes-col-ar h4 { margin: 0 0 4px 0; font-size: 12px; font-weight: 700; color: #111; }

        .pdf-footer { margin-top: 15px; display: flex; justify-content: space-between; align-items: flex-start; padding: 0 5px; }
        .footer-left-col { display: flex; flex-direction: column; align-items: flex-start; gap: 8px; }
        .footer-right-col { text-align: right; direction: rtl; }
        .footer-en-link { font-size: 11px; font-weight: 700; color: #000000; }
        .footer-ar-link { font-size: 12px; font-weight: 700; color: #000000; font-family: 'Amiri', serif; direction: rtl; }
        .qr-box { width: 125px; height: 125px; }
        .qr-box img { width: 100%; height: 100%; object-fit: contain; }

        .p2-sub-bar { margin-bottom: 25px; }
        .p2-sub-bar .welcome-en { font-size: 26px; }
        .p2-sub-bar .p2-wish-en { font-size: 18px; color: #c06c54; font-style: italic; margin-top: 4px; }
        .p2-sub-bar .p2-wish-ar { font-family: 'Amiri', serif; font-size: 20px; font-weight: 700; color: #c06c54; margin-top: 4px; direction: rtl; }
        .p2-center-graphic { display: flex; justify-content: center; align-items: center; text-align: center; margin: 20px 0 15px; width: 100%; }
        .p2-center-graphic img { display: block; max-width: 100%; max-height: 110px; height: auto; object-fit: contain; margin: 0 auto; }
        .p2-text-block { display: flex; justify-content: space-between; gap: 25px; margin-bottom: 35px; padding: 0 10px; }
        .p2-en-text { width: 48%; font-size: 12px; line-height: 1.55; color: #222; }
        .p2-ar-text { width: 48%; font-size: 13px; line-height: 1.65; color: #222; text-align: right; direction: rtl; font-family: 'Amiri', serif; }

        /* Responsive Media Queries (Mobile Phone Support) */
        @media screen and (max-width: 868px) {
            .nav-container { display: none; }
            .mobile-header-bar { display: flex; }
            .hero-banner {
                height: 260px;
                padding: 30px 20px 16px 20px;
            }
            .hero-content-top h1 { font-size: 28px; }
            .hero-content-bottom { font-size: 12px; }
            .main-content { padding: 0 16px; margin: 24px auto; }
            .title-card-row {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
                margin-bottom: 30px;
            }
            .title-card { padding: 24px 20px; }
            .title-card h2 { font-size: 20px; }
            .btn-visa-status { text-align: center; width: 100%; }
            .content-row, .content-row.reverse {
                flex-direction: column;
                gap: 24px;
                margin-bottom: 40px;
            }
            .content-image img { height: 220px; }
            .img-curve-right, .img-curve-left {
                border-radius: 0 60px 60px 0;
            }
            .search-form-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }
            .search-btn-actions {
                flex-direction: column;
            }
            .btn-red-search, .btn-outline-back {
                width: 100%;
                text-align: center;
            }
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            .footer-bottom {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }

        @media print {
            @page { size: A4 portrait; margin: 0mm; }
            html, body { width: 210mm !important; margin: 0 !important; padding: 0 !important; background: #ffffff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .site-nav-header, .mobile-header-bar, .mobile-nav-drawer, .hero-banner, .main-content, .site-footer, .back-to-top-btn, .verified-header-bar, .action-bar, .search-app-container { display: none !important; }
            .page-container { width: 250mm !important; max-width: 250mm !important; min-height: 297mm !important; margin: 0 auto !important; padding: 16mm 16mm 12mm 16mm !important; box-shadow: none !important; border: none !important; page-break-after: always !important; page-break-inside: avoid !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

<!-- Desktop Header Navbar -->
<header class="site-nav-header">
    <div class="nav-container">
        <a href="index.php" class="nav-logo-group">
            <img src="./images/logo.png" alt="Jordan eVisa Logo" class="brand-logo">
            <img src="./images/logo2.png" alt="Jordan Coat of Arms" class="emblem-logo">
        </a>
        <ul class="nav-links">
            <li><a href="index.php">About Jordan</a></li>
            <li><a href="index.php">Government Entities</a></li>
            <li><a href="index.php">Services</a></li>
            <li><a href="visa-status.php">Visa Status</a></li>
            <li><a href="index.php">Glossary, FAQ</a></li>
            <li><a href="index.php">Home</a></li>
            <li>
                <a href="visa-status.php" class="search-icon-btn" title="Search Applications">
                    &#128065;
                </a>
            </li>
            <li>
                <a href="visa-status.php" class="btn-apply-visa">Apply for e-Visa</a>
            </li>
        </ul>
    </div>

    <!-- Mobile Header Bar (Matches Phone Image 1) -->
    <div class="mobile-header-bar">
        <button type="button" class="mobile-hamburger" onclick="toggleMobileNav()" title="Menu">
            &#9776;
        </button>
        <a href="index.php" class="mobile-logo-center">
            <img src="./images/logo.png" alt="Jordan eVisa Logo" style="height: 32px;">
            <img src="./images/logo2.png" alt="Emblem" style="height: 36px;">
        </a>
        <a href="visa-status.php" class="mobile-search-btn" title="Search">
            &#128065;
        </a>
    </div>
    
    <!-- Mobile Drawer Menu -->
    <div class="mobile-nav-drawer" id="mobileNavDrawer">
        <a href="index.php">Home</a>
        <a href="index.php">About Jordan</a>
        <a href="index.php">Government Entities</a>
        <a href="index.php">Services</a>
        <a href="visa-status.php" style="color: #2a9d68; font-weight: 700;">Visa Status</a>
        <a href="index.php">Glossary, FAQ</a>
        <a href="visa-status.php" class="btn-apply-visa" style="text-align: center; margin-top: 8px;">Apply for e-Visa</a>
    </div>
</header>

<script>
    function toggleMobileNav() {
        var drawer = document.getElementById('mobileNavDrawer');
        drawer.classList.toggle('active');
    }
</script>
