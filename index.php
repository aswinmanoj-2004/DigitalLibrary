<?php
require('config/autoload.php');
$dao = new DataAccess();

// Existing queries
$recentBooks = $dao->query("SELECT b.bid, b.bname, b.AuthorName, b.CoverImage, b.is_paid, b.price FROM books b WHERE b.status=1 ORDER BY b.AddedDate DESC LIMIT 8");
$categories  = $dao->query("SELECT * FROM category WHERE status=1 ORDER BY cname ASC");

// Trending books based on download count
$trendingBooks = $dao->query("SELECT b.bid, b.bname, b.AuthorName, b.CoverImage, b.is_paid, b.price, COUNT(d.downloadid) as download_count FROM books b LEFT JOIN downloads d ON b.bid = d.bid WHERE b.status=1 GROUP BY b.bid ORDER BY download_count DESC LIMIT 4");

// Recent reader reviews
$reviews = $dao->query("SELECT r.review_text, r.rating, r.review_date, u.username FROM reviews r JOIN readerregistration u ON r.userId = u.userId WHERE r.status=1 ORDER BY r.review_date DESC LIMIT 3");

// Count total books
$totalBooks = $dao->query("SELECT COUNT(*) as count FROM books WHERE status=1");
$bookCount = $totalBooks ? $totalBooks[0]['count'] : 0;

// Count total authors (distinct authors from books)
$totalAuthors = $dao->query("SELECT COUNT(DISTINCT AuthorName) as count FROM books WHERE status=1");
$authorCount = $totalAuthors ? $totalAuthors[0]['count'] : 0;

// Count total readers
$totalReaders = $dao->query("SELECT COUNT(*) as count FROM readerregistration WHERE status=1");
$readerCount = $totalReaders ? $totalReaders[0]['count'] : 0;

// Get free book count
$freeBooks = $dao->query("SELECT COUNT(*) as count FROM books WHERE status=1 AND is_paid='free'");
$freeBookCount = $freeBooks ? $freeBooks[0]['count'] : 0;

// Get paid book count
$paidBooks = $dao->query("SELECT COUNT(*) as count FROM books WHERE status=1 AND is_paid='paid'");
$paidBookCount = $paidBooks ? $paidBooks[0]['count'] : 0;

// Get a featured free book
$featuredFreeBook = $dao->query("SELECT b.bid, b.bname, b.AuthorName, b.CoverImage, b.is_paid, b.price, c.cname as category, b.Description FROM books b LEFT JOIN category c ON b.cid = c.cid WHERE b.status=1 AND b.is_paid='free' ORDER BY b.AddedDate DESC LIMIT 1");

// ====== GRAPH DATA QUERIES ======

// 1. Books by Category (for bar chart)
$booksByCategory = $dao->query("
    SELECT c.cname, COUNT(b.bid) as count 
    FROM category c 
    LEFT JOIN books b ON c.cid = b.cid AND b.status=1 
    WHERE c.status=1 
    GROUP BY c.cid 
    ORDER BY count DESC
");

// 2. Books by Price Range
$priceRanges = [
    'Free' => 0,
    '₹1-₹99' => 0,
    '₹100-₹199' => 0,
    '₹200-₹299' => 0,
    '₹300+' => 0
];

$allBooks = $dao->query("SELECT price, is_paid FROM books WHERE status=1");
if ($allBooks) {
    foreach ($allBooks as $b) {
        if ($b['is_paid'] == 'free') {
            $priceRanges['Free']++;
        } else {
            $price = (int)$b['price'];
            if ($price <= 99) $priceRanges['₹1-₹99']++;
            elseif ($price <= 199) $priceRanges['₹100-₹199']++;
            elseif ($price <= 299) $priceRanges['₹200-₹299']++;
            else $priceRanges['₹300+']++;
        }
    }
}

// 3. Monthly additions (last 6 months)
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthName = date('M', strtotime("-$i months"));
    $count = $dao->query("SELECT COUNT(*) as count FROM books WHERE status=1 AND DATE_FORMAT(AddedDate, '%Y-%m') = '$month'");
    $monthlyData[$monthName] = $count ? $count[0]['count'] : 0;
}

// 4. Book counts by author
$authorBookCounts = $dao->query("SELECT AuthorName, COUNT(*) as count FROM books WHERE status=1 GROUP BY AuthorName ORDER BY count DESC LIMIT 5");

// 5. Total downloads per book (for popularity)
$popularBooks = $dao->query("
    SELECT b.bname, COUNT(d.downloadid) as downloads 
    FROM books b 
    LEFT JOIN downloads d ON b.bid = d.bid 
    WHERE b.status=1 
    GROUP BY b.bid 
    ORDER BY downloads DESC 
    LIMIT 5
");

// 6. Category icons mapping
$categoryIcons = [
    'Manga' => 'auto_stories',
    'Romance' => 'favorite',
    'Thriller' => 'bolt',
    'Mystery' => 'search',
    'Fantasy' => 'magic_button',
    'SciFi' => 'rocket_launch',
    'Action' => 'sports_martial_arts',
    'Drama' => 'theater_comedy'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ready 2 Read · Digital Library</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script>
    // Apply dark class immediately if preferred
    if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
  <style>
    /* ══════════════════════════════════
       THEME VARIABLES
       ══════════════════════════════════ */
    :root {
      --bg-main: #f8fafc;
      --text-main: #1e1b2e;
      --text-heading: #1e293b;
      --text-muted: #64748b;
      --glass-bg: rgba(255, 255, 255, 0.55);
      --glass-border: rgba(255, 255, 255, 0.5);
      --glass-shadow: rgba(0, 0, 0, 0.08);
      --card-bg: #ffffff;
      --card-border: rgba(124, 58, 237, 0.08);
      --card-shadow: rgba(0, 0, 0, 0.04);
      --card-hover-shadow: rgba(124, 58, 237, 0.08);
      --section-sep: linear-gradient(90deg, transparent, rgba(124, 58, 237, 0.2), transparent);
      --chart-line: rgba(226, 232, 240, 0.6);
    }

    html.dark {
      --bg-main: #07050f; /* Deep galactic night background */
      --text-main: #cbd5e1; /* slate-300 */
      --text-heading: #f8fafc; /* slate-50 */
      --text-muted: #94a3b8; /* slate-400 */
      --glass-bg: rgba(15, 12, 27, 0.6);
      --glass-border: rgba(124, 58, 237, 0.2);
      --glass-shadow: rgba(0, 0, 0, 0.3);
      --card-bg: rgba(20, 18, 35, 0.7);
      --card-border: rgba(167, 139, 250, 0.15);
      --card-shadow: rgba(0, 0, 0, 0.25);
      --card-hover-shadow: rgba(124, 58, 237, 0.25);
      --section-sep: linear-gradient(90deg, transparent, rgba(167, 139, 250, 0.3), transparent);
      --chart-line: rgba(255, 255, 255, 0.08);
    }
    /* ══════════════════════════════════
       MODERN CINEMATIC INTRO LOADER
    ══════════════════════════════════ */
    .loader {
        position: fixed; inset: 0; z-index: 10000;
        background: #07050f;
        display: flex; align-items: center; justify-content: center;
        flex-direction: column;
        overflow: hidden;
    }

    /* Deep starfield bg */
    .loader-bg {
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 80% 60% at 50% 0%, rgba(124,58,237,0.18) 0%, transparent 60%),
            radial-gradient(ellipse 60% 40% at 20% 100%, rgba(37,99,235,0.12) 0%, transparent 55%),
            radial-gradient(ellipse 50% 35% at 80% 80%, rgba(16,185,129,0.07) 0%, transparent 50%),
            #07050f;
    }

    /* Floating particle pages */
    .loader-particles { position: absolute; inset: 0; pointer-events: none; }
    .particle {
        position: absolute;
        width: 4px; height: 5px;
        background: rgba(167,139,250,0.35);
        border-radius: 1px;
        animation: particleDrift var(--dur, 4s) ease-in-out infinite var(--delay, 0s);
        left: var(--x, 50%); bottom: -10px;
    }
    @keyframes particleDrift {
        0%   { transform: translateY(0) translateX(0) rotate(0deg); opacity: 0; }
        10%  { opacity: 1; }
        90%  { opacity: 0.6; }
        100% { transform: translateY(-110vh) translateX(var(--drift, 30px)) rotate(540deg); opacity: 0; }
    }

    /* Central book icon */
    .loader-book {
        position: relative; z-index: 2;
        width: 80px; height: 80px;
        margin-bottom: 32px;
        animation: bookFloat 3s ease-in-out infinite;
    }
    @keyframes bookFloat {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-10px); }
    }
    .loader-book svg { width: 100%; height: 100%; filter: drop-shadow(0 0 24px rgba(124,58,237,0.7)); }

    /* Glowing ring around book */
    .loader-ring {
        position: absolute; inset: -16px;
        border-radius: 50%;
        border: 1.5px solid rgba(124,58,237,0.3);
        animation: ringPulse 2s ease-in-out infinite;
    }
    .loader-ring2 {
        position: absolute; inset: -28px;
        border-radius: 50%;
        border: 1px solid rgba(124,58,237,0.15);
        animation: ringPulse 2s ease-in-out infinite 0.4s;
    }
    @keyframes ringPulse {
        0%, 100% { transform: scale(1);   opacity: 1; }
        50%       { transform: scale(1.12); opacity: 0.4; }
    }

    /* Logo text block */
    .loader-logo {
        position: relative; z-index: 2;
        display: flex; flex-direction: column;
        align-items: center; gap: 10px;
    }

    /* Letter-by-letter reveal */
    .loader-title {
        display: flex; gap: 0; align-items: baseline;
        font-family: 'Manrope', sans-serif;
        font-size: clamp(38px, 7vw, 72px);
        font-weight: 800;
        letter-spacing: 6px;
        line-height: 1;
        color: #fff;
    }
    .loader-title .lt {
        display: inline-block;
        opacity: 0;
        transform: translateY(30px);
        animation: letterPop 0.5s cubic-bezier(0.34,1.56,0.64,1) forwards;
    }
    .loader-title .lt.accent { color: #a78bfa; }
    .loader-title .lt.gap   { width: 0.35em; }

    /* Letter stagger delays */
    .lt:nth-child(1)  { animation-delay: 0.05s; }
    .lt:nth-child(2)  { animation-delay: 0.12s; }
    .lt:nth-child(3)  { animation-delay: 0.19s; }
    .lt:nth-child(4)  { animation-delay: 0.26s; }
    .lt:nth-child(5)  { animation-delay: 0.33s; }
    .lt:nth-child(6)  { animation-delay: 0.40s; }
    .lt:nth-child(7)  { animation-delay: 0.47s; }
    .lt:nth-child(8)  { animation-delay: 0.54s; }
    .lt:nth-child(9)  { animation-delay: 0.61s; }
    .lt:nth-child(10) { animation-delay: 0.68s; }
    .lt:nth-child(11) { animation-delay: 0.75s; }
    .lt:nth-child(12) { animation-delay: 0.82s; }
    @keyframes letterPop {
        to { opacity: 1; transform: translateY(0); }
    }

    /* Tagline */
    .loader-tagline {
        font-family: 'Inter', sans-serif;
        font-size: 11px;
        letter-spacing: 6px;
        text-transform: uppercase;
        color: rgba(148,163,184,0.7);
        opacity: 0;
        animation: fadeUp 0.7s ease forwards 1.1s;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Shimmer divider line */
    .loader-line {
        position: relative; z-index: 2;
        width: 0; height: 1px;
        background: linear-gradient(90deg, transparent, #7c3aed, #a78bfa, transparent);
        border-radius: 2px;
        margin-top: 24px;
        animation: expandLine 0.8s ease forwards 1.0s;
        box-shadow: 0 0 12px rgba(124,58,237,0.6);
    }
    @keyframes expandLine { to { width: min(360px, 55vw); } }

    /* Genre pills appearing */
    .loader-genres {
        position: relative; z-index: 2;
        display: flex; flex-wrap: wrap; justify-content: center;
        gap: 8px; margin-top: 20px;
        opacity: 0;
        animation: fadeUp 0.6s ease forwards 1.3s;
    }
    .genre-pill {
        font-size: 10px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: rgba(167,139,250,0.8);
        padding: 4px 12px;
        border: 1px solid rgba(124,58,237,0.3);
        border-radius: 40px;
        background: rgba(124,58,237,0.08);
        backdrop-filter: blur(8px);
    }

    /* Bottom progress bar */
    .loader-progress {
        position: absolute; bottom: 0; left: 0; right: 0;
        height: 2px;
        background: rgba(255,255,255,0.04);
    }
    .loader-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #7c3aed, #a78bfa, #6366f1);
        width: 0;
        animation: progressFill 1.8s cubic-bezier(.4,0,.2,1) forwards 0.15s;
        box-shadow: 0 0 10px rgba(124,58,237,0.8);
    }
    @keyframes progressFill { to { width: 100%; } }

    /* Counter */
    .loader-counter {
        position: absolute; bottom: 12px; right: 20px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px; letter-spacing: 2px;
        color: rgba(148,163,184,0.5); z-index: 2;
    }

    /* Exit animation */
    .loader.hide {
        animation: loaderFadeOut 0.9s cubic-bezier(.77,0,.18,1) forwards;
    }
    @keyframes loaderFadeOut {
        0%   { opacity: 1; transform: scale(1); }
        100% { opacity: 0; transform: scale(1.04); pointer-events: none; }
    }

    /* MAIN SITE STYLES */
    body {
      background: var(--bg-main);
      color: var(--text-main);
      font-family: 'Inter', sans-serif;
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    .glass-dock {
      background: var(--glass-bg);
      backdrop-filter: blur(16px) saturate(180%);
      -webkit-backdrop-filter: blur(16px) saturate(180%);
      border: 1px solid var(--glass-border);
      box-shadow: 0 8px 32px var(--glass-shadow);
      border-radius: 40px;
      padding: 8px 20px;
      transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .glass-dock .nav-link {
      color: var(--text-main);
      font-weight: 500;
      padding: 8px 16px;
      border-radius: 100px;
      transition: all 0.2s;
      background: transparent;
      font-size: 0.95rem;
    }
    .glass-dock .nav-link:hover {
      background: rgba(124, 58, 237, 0.10);
      color: #4f2b9e;
    }
    html.dark .glass-dock .nav-link:hover {
      background: rgba(167, 139, 250, 0.15);
      color: #c084fc;
    }
    .btn-violet {
      background: #7c3aed;
      color: white;
      font-weight: 600;
      padding: 8px 20px;
      border-radius: 100px;
      transition: all 0.2s;
      box-shadow: 0 2px 8px rgba(124, 58, 237, 0.25);
    }
    .btn-violet:hover {
      background: #6d28d9;
      transform: scale(1.02);
      box-shadow: 0 4px 16px rgba(124, 58, 237, 0.4);
    }
    .btn-outline-violet {
      background: transparent;
      color: #5b21b6;
      font-weight: 600;
      padding: 8px 20px;
      border-radius: 100px;
      border: 1.5px solid #7c3aed;
      transition: all 0.2s;
    }
    .btn-outline-violet:hover {
      background: #7c3aed10;
      border-color: #6d28d9;
      transform: scale(1.02);
    }
    html.dark .btn-outline-violet {
      color: #a78bfa;
      border-color: #7c3aed;
    }
    html.dark .btn-outline-violet:hover {
      background: rgba(124, 58, 237, 0.15);
      border-color: #a78bfa;
    }
    .card-white {
      background: var(--card-bg);
      border-radius: 24px;
      box-shadow: 0 8px 30px var(--card-shadow);
      transition: all 0.25s;
      border: 1px solid var(--card-border);
    }
    .card-white:hover {
      box-shadow: 0 16px 48px var(--card-hover-shadow);
      transform: translateY(-3px);
    }
    .badge-free-white {
      background: #e9f5ee;
      color: #0b6e4f;
      font-size: 10px;
      padding: 2px 12px;
      border-radius: 40px;
      font-weight: 600;
      letter-spacing: 0.03em;
    }
    html.dark .badge-free-white {
      background: rgba(16, 185, 129, 0.15);
      color: #34d399;
    }
    .badge-paid-white {
      background: #ede9fe;
      color: #5b21b6;
      font-size: 10px;
      padding: 2px 12px;
      border-radius: 40px;
      font-weight: 600;
      letter-spacing: 0.03em;
    }
    html.dark .badge-paid-white {
      background: rgba(124, 58, 237, 0.2);
      color: #c084fc;
    }
    .text-violet { color: #5b21b6; }
    html.dark .text-violet { color: #a78bfa; }
    .bg-violet-soft { background: #f5f0ff; }
    html.dark .bg-violet-soft { background: rgba(124, 58, 237, 0.15); }
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
    .star-gold { color: #f59e0b; }
    .book-cover-placeholder {
      background: linear-gradient(145deg, #ffffff, #f1f0f5);
      border: 1px solid #e9e5f0;
    }
    .book-thumb {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 12px;
      background: #f1f0f5;
    }
    .line-clamp-2 {
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .main-content {
      opacity: 0;
      animation: fadeInContent 0.8s ease forwards 1.8s;
    }
    @keyframes fadeInContent {
      to { opacity: 1; }
    }

    /* ===== MODERN COLUMN CHART ===== */
    .col-chart-wrap {
      display: flex;
      gap: 0;
      align-items: flex-end;
      min-height: 220px;
      position: relative;
    }
    .col-yaxis {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 200px;
      width: 40px;
      flex-shrink: 0;
      align-items: flex-end;
      padding-right: 10px;
    }
    .col-yaxis span {
      font-size: 11px;
      color: var(--text-muted);
      font-variant-numeric: tabular-nums;
      transition: color 0.3s ease;
    }
    .col-chart {
      display: flex;
      align-items: flex-end;
      gap: 16px;
      height: 200px;
      flex: 1;
      padding: 0 8px;
      position: relative;
    }
    .col-chart::before {
      content: '';
      position: absolute;
      left: 0; right: 0; top: 0; bottom: 0;
      background-image: repeating-linear-gradient(
        to bottom,
        transparent,
        transparent calc(25% - 1px),
        var(--chart-line) calc(25% - 1px),
        var(--chart-line) 25%
      );
      pointer-events: none;
    }
    .col-bar-group {
      display: flex;
      flex-direction: column;
      align-items: center;
      flex: 1;
      min-width: 48px;
      max-width: 90px;
      gap: 4px;
      height: 100%;
      justify-content: flex-end;
      position: relative;
    }
    .col-count {
      font-size: 13px;
      font-weight: 700;
      font-variant-numeric: tabular-nums;
      opacity: 0;
      transition: opacity 0.4s ease 1.2s;
    }
    .col-count.visible { opacity: 1; }
    .col-bar-track {
      width: 100%;
      max-width: 54px;
      height: 100%;
      display: flex;
      align-items: flex-end;
      border-radius: 8px 8px 0 0;
      overflow: hidden;
    }
    .col-bar-fill {
      width: 100%;
      height: 0%;
      border-radius: 8px 8px 0 0;
      transition: height 1.1s cubic-bezier(0.34, 1.2, 0.64, 1);
      position: relative;
    }
    .col-bar-fill::after {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 30%;
      background: rgba(255,255,255,0.25);
      border-radius: 8px 8px 0 0;
    }
    .col-icon {
      font-size: 18px !important;
      margin-top: 6px;
    }
    .col-label {
      font-size: 10px;
      font-weight: 600;
      color: var(--text-muted);
      text-align: center;
      letter-spacing: 0.02em;
      line-height: 1.2;
      margin-top: 2px;
      transition: color 0.3s ease;
    }
    @media (max-width: 640px) {
      .col-chart { gap: 8px; }
      .col-bar-group { min-width: 32px; }
      .col-label { font-size: 8px; }
      .col-icon { font-size: 14px !important; }
    }

    /* ===== GRAPH STYLES ===== */
    .chart-container {
      background: var(--card-bg);
      border-radius: 20px;
      padding: 24px;
      border: 1px solid var(--card-border);
      box-shadow: 0 4px 20px var(--card-shadow);
      height: 100%;
      transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .chart-container h3 {
      font-size: 16px;
      font-weight: 600;
      color: var(--text-heading);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .bar-wrapper {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .bar-row {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .bar-label {
      width: 80px;
      font-size: 13px;
      color: var(--text-muted);
      font-weight: 500;
      text-align: right;
      flex-shrink: 0;
    }
    .bar-track {
      flex: 1;
      height: 28px;
      background: #f1f5f9;
      border-radius: 14px;
      overflow: hidden;
      position: relative;
    }
    .bar-fill {
      height: 100%;
      border-radius: 14px;
      background: linear-gradient(90deg, #7c3aed, #a78bfa);
      transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
      width: 0%;
      position: relative;
    }
    .bar-fill .bar-value {
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 12px;
      font-weight: 600;
      color: white;
    }
    .bar-fill.animate {
      width: var(--target-width);
    }

    /* Donut chart */
    .donut-container {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 40px;
      flex-wrap: wrap;
    }
    .donut-wrapper {
      position: relative;
      width: 160px;
      height: 160px;
    }
    .donut-wrapper canvas {
      width: 100%;
      height: 100%;
    }
    .donut-center {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 24px;
      color: var(--text-heading);
    }
    .donut-center span {
      font-size: 12px;
      font-weight: 400;
      color: var(--text-muted);
    }
    .donut-legend {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .legend-item {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 13px;
      color: var(--text-main);
    }
    .legend-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    /* Stats cards animation */
    .stat-card {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 20px 24px;
      border: 1px solid var(--card-border);
      text-align: center;
      transition: all 0.3s ease;
    }
    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px var(--card-hover-shadow);
    }
    .stat-number {
      font-size: 32px;
      font-weight: 800;
      color: #5b21b6;
      transition: color 0.3s ease;
    }
    html.dark .stat-number {
      color: #a78bfa;
    }
    .stat-label {
      font-size: 14px;
      color: var(--text-muted);
      margin-top: 4px;
      transition: color 0.3s ease;
    }

    /* Horizontal scroll for graphs section */
    .graph-scroll {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
      gap: 24px;
    }
    @media (max-width: 768px) {
      .graph-scroll {
        grid-template-columns: 1fr;
      }
      .bar-label {
        width: 60px;
        font-size: 11px;
      }
    }

    /* Trending badge pulse */
    .trending-pulse {
      animation: pulse 2s ease-in-out infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    /* Colorful bar variants */
    .bar-fill.purple { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
    .bar-fill.blue { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .bar-fill.green { background: linear-gradient(90deg, #10b981, #34d399); }
    .bar-fill.orange { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .bar-fill.pink { background: linear-gradient(90deg, #ec4899, #f472b6); }
    .bar-fill.cyan { background: linear-gradient(90deg, #06b6d4, #22d3ee); }
    .bar-fill.red { background: linear-gradient(90deg, #ef4444, #f87171); }
    .bar-fill.indigo { background: linear-gradient(90deg, #4f46e5, #818cf8); }

    /* Counter animation for stats */
    .counter-value {
      display: inline-block;
    }

    /* Section separator */
    .section-divider {
      height: 2px;
      background: var(--section-sep);
      margin: 40px 0;
      transition: background 0.3s ease;
    }

    /* ══════════════════════════════════
       DARK MODE ADAPTATIONS
       ══════════════════════════════════ */
    html.dark .text-slate-800,
    html.dark h1,
    html.dark h2,
    html.dark h3,
    html.dark h4 {
      color: var(--text-heading) !important;
    }
    html.dark .text-slate-700 {
      color: var(--text-main) !important;
    }
    html.dark .text-slate-600,
    html.dark .text-slate-500,
    html.dark .text-slate-400 {
      color: var(--text-muted) !important;
    }
    html.dark .bg-white {
      background-color: var(--card-bg) !important;
    }
    html.dark .bg-white\/60,
    html.dark .bg-white\/70,
    html.dark .bg-white\/50 {
      background-color: var(--glass-bg) !important;
      border-color: var(--glass-border) !important;
    }
    html.dark .border-slate-200,
    html.dark .border-slate-200\/60,
    html.dark .border-slate-200\/70,
    html.dark .border-slate-200\/80,
    html.dark .border-slate-100 {
      border-color: var(--card-border) !important;
    }
    html.dark .bg-slate-100 {
      background-color: rgba(255, 255, 255, 0.08) !important;
      color: var(--text-main) !important;
    }
    html.dark .bg-slate-50 {
      background-color: rgba(255, 255, 255, 0.04) !important;
      border-color: rgba(255, 255, 255, 0.1) !important;
    }
    html.dark footer {
      background-color: rgba(15, 12, 27, 0.5) !important;
      border-top-color: rgba(167, 139, 250, 0.15) !important;
    }
    html.dark .bar-track {
      background: rgba(255, 255, 255, 0.06) !important;
    }
    html.dark img[src="logo.png"] {
      filter: invert(1) hue-rotate(180deg) brightness(1.2);
    }
    #themeToggle:hover #themeIcon {
      transform: scale(1.15) rotate(15deg);
    }
    #themeIcon {
      transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.3s ease;
    }
  </style>
</head>
<body>

<!-- ══ CINEMATIC INTRO LOADER ══ -->
<div class="loader" id="loader">
    <div class="loader-bg"></div>

    <!-- Floating page particles -->
    <div class="loader-particles" id="loaderParticles"></div>

    <!-- Central glowing book icon -->
    <div class="loader-book">
       
            <defs>
                <linearGradient id="bkGrad" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#a78bfa"/>
                    <stop offset="100%" stop-color="#6366f1"/>
                </linearGradient>
                <filter id="glow">
                    <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                    <feMerge><feMergeNode in="coloredBlur"/><feMergeNode in="SourceGraphic"/></feMerge>
                </filter>
            </defs>
            <!-- Book spine -->
            <rect x="12" y="10" width="8" height="60" rx="3" fill="url(#bkGrad)" opacity="0.9" filter="url(#glow)"/>
            <!-- Book cover -->
            <rect x="20" y="10" width="46" height="60" rx="4" fill="url(#bkGrad)" opacity="0.7"/>
            <!-- Pages shimmer -->
            <rect x="22" y="14" width="40" height="52" rx="2" fill="rgba(255,255,255,0.08)"/>
            <!-- Lines on book -->
            <line x1="27" y1="28" x2="56" y2="28" stroke="rgba(255,255,255,0.35)" stroke-width="2" stroke-linecap="round"/>
            <line x1="27" y1="36" x2="56" y2="36" stroke="rgba(255,255,255,0.25)" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="27" y1="43" x2="48" y2="43" stroke="rgba(255,255,255,0.2)" stroke-width="1.5" stroke-linecap="round"/>
            <line x1="27" y1="50" x2="52" y2="50" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
    </div>

    <!-- Title with letter-by-letter animation -->
    <div class="loader-logo">
        <div class="loader-title">
            <span class="lt">R</span><span class="lt">E</span><span class="lt">A</span><span class="lt">D</span><span class="lt">Y</span>
            <span class="lt gap"> </span>
            <span class="lt accent">2</span>
            <span class="lt gap"> </span>
            <span class="lt">R</span><span class="lt">E</span><span class="lt">A</span><span class="lt">D</span>
        </div>
        <div class="loader-tagline">The Premium Digital Library</div>
    </div>

    <div class="loader-line"></div>

    <!-- Genre pills from database categories -->
    <div class="loader-genres">
        <span class="genre-pill">Manga</span>
        <span class="genre-pill">Romance</span>
        <span class="genre-pill">SciFi</span>
        <span class="genre-pill">Fantasy</span>
        <span class="genre-pill">Drama</span>
        <span class="genre-pill">Mystery</span>
    </div>

    <div class="loader-progress">
        <div class="loader-progress-fill"></div>
    </div>
    <div class="loader-counter" id="loaderCounter">0%</div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

<!-- MAC OS GLASS DOCK NAVBAR -->
<header class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-auto max-w-[95%]">
  <nav class="glass-dock flex items-center gap-1 md:gap-4 flex-wrap justify-center">
    <a href="index.php" class="flex items-center mr-1 md:mr-2">
      <img src="logo.png" width=150px; heigth=80px; />
    </a>

    <a href="author/login.php" class="nav-link text-sm md:text-base font-semibold text-violet">Author Signup</a>
    <a href="reader/readerRegister.php" class="nav-link text-sm md:text-base font-semibold text-violet">Reader Signup</a>
    <a href="reader/readerLogin.php" class="btn-violet text-sm md:text-base px-4 py-1.5">Sign In</a>
    <button id="themeToggle" class="nav-link flex items-center justify-center p-2 rounded-full hover:bg-violet-soft text-violet transition-all" style="width: 38px; height: 38px;" aria-label="Toggle Night Mode">
      <span class="material-symbols-outlined !text-xl" id="themeIcon">dark_mode</span>
    </button>
  </nav>
</header>

<!-- main content -->
<main class="pt-28 pb-8 px-4 max-w-7xl mx-auto">

  <!-- ===== HERO SECTION ===== -->
  <section class="text-center py-8 md:py-16">
    <div class="inline-block bg-violet-soft px-5 py-1.5 rounded-full text-violet font-semibold text-sm mb-4">
       <?= $bookCount ?> books · <?= count($categories) ?> genres
    </div>
    <h1 class="text-4xl md:text-6xl font-extrabold leading-tight text-slate-800 max-w-3xl mx-auto">
      Your Library, <br class="sm:hidden" />
      <span class="text-violet">Everywhere</span>
    </h1>
    <p class="text-base md:text-lg text-slate-600 max-w-xl mx-auto mt-4">
      From manga to fantasy, romance to sci-fi. <?= $freeBookCount ?> free titles available right now.
    </p>
    <div class="flex flex-wrap justify-center gap-3 mt-6">
      <a href="reader/readerRegister.php" class="btn-violet px-8 py-3 text-base">Start Reading</a>
      <a href="reader/readerlogin.php" class="btn-outline-violet px-8 py-3 text-base">Browse Free Books</a>
    </div>
    <!-- quick stats -->
    <div class="flex flex-wrap justify-center gap-4 mt-8 text-sm">
      <div class="bg-white/70 backdrop-blur-sm px-4 py-2 rounded-2xl shadow-sm border border-white/50">
        <span class="font-bold text-violet"><?= $bookCount ?></span> Books
      </div>
      <div class="bg-white/70 backdrop-blur-sm px-4 py-2 rounded-2xl shadow-sm border border-white/50">
        <span class="font-bold text-violet"><?= count($categories) ?></span> Genres
      </div>
      <div class="bg-white/70 backdrop-blur-sm px-4 py-2 rounded-2xl shadow-sm border border-white/50">
        <span class="font-bold text-violet"><?= $readerCount ?></span> Readers
      </div>
      <div class="bg-white/70 backdrop-blur-sm px-4 py-2 rounded-2xl shadow-sm border border-white/50">
        <span class="font-bold text-violet"><?= $freeBookCount ?></span> Free Books
      </div>
    </div>
  </section>

  <!-- ===== STATS CARDS SECTION ===== -->
  <section class="mt-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="stat-card">
        <div class="stat-number"><span class="counter-value" data-target="<?= $bookCount ?>">0</span></div>
        <div class="stat-label">Total Books</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><span class="counter-value" data-target="<?= $authorCount ?>">0</span></div>
        <div class="stat-label">Authors</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><span class="counter-value" data-target="<?= $readerCount ?>">0</span></div>
        <div class="stat-label">Readers</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><span class="counter-value" data-target="<?= $freeBookCount ?>">0</span></div>
        <div class="stat-label">Free Books</div>
      </div>
    </div>
  </section>

  <div class="section-divider"></div>

  <!-- ===== GRAPHS SECTION ===== -->
  <section class="mt-8">
    <div class="flex items-center gap-3 mb-6">
      <span class="material-symbols-outlined text-violet text-3xl">insights</span>
      <div>
        <h2 class="text-2xl md:text-3xl font-bold text-slate-800">Library Analytics</h2>
        <p class="text-slate-500 text-sm">Books distributed across all genres</p>
      </div>
    </div>

    <!-- Books by Category — modern column chart card -->
    <div class="chart-container" style="padding: 32px 36px;">
      <div class="flex items-center justify-between mb-8 flex-wrap gap-3">
        <h3 style="margin-bottom:0;">
          <span class="material-symbols-outlined text-violet">bar_chart</span>
          Books by Category
        </h3>
        <span class="text-xs font-semibold uppercase tracking-widest text-slate-400 bg-slate-50 px-3 py-1 rounded-full border border-slate-100">
          <?= $bookCount ?> total books
        </span>
      </div>

      <?php
        $maxCount = 0;
        if ($booksByCategory) {
          foreach ($booksByCategory as $cat) {
            if ($cat['count'] > $maxCount) $maxCount = $cat['count'];
          }
        }
        $barGradDefs = [
          ['#7c3aed','#a78bfa'],
          ['#3b82f6','#60a5fa'],
          ['#10b981','#34d399'],
          ['#f59e0b','#fbbf24'],
          ['#ec4899','#f472b6'],
          ['#06b6d4','#22d3ee'],
          ['#ef4444','#f87171'],
          ['#4f46e5','#818cf8'],
        ];
        $ci = 0;
      ?>

      <div class="col-chart-wrap">
        <!-- Y-axis labels -->
        <div class="col-yaxis">
          <?php for ($y = $maxCount; $y >= 0; $y -= max(1, ceil($maxCount/4))): ?>
            <span><?= $y ?></span>
          <?php endfor; ?>
          <span>0</span>
        </div>
        <!-- Columns -->
        <div class="col-chart" id="colChart">
          <?php if ($booksByCategory): foreach ($booksByCategory as $cat):
            $percent = $maxCount > 0 ? ($cat['count'] / $maxCount) * 100 : 0;
            $c = $barGradDefs[$ci % count($barGradDefs)];
            $icon = $categoryIcons[$cat['cname']] ?? 'auto_stories';
            $ci++;
          ?>
            <div class="col-bar-group">
              <div class="col-count" style="color:<?= $c[0] ?>;"><?= $cat['count'] ?></div>
              <div class="col-bar-track">
                <div class="col-bar-fill"
                     data-height="<?= $percent ?>"
                     style="background: linear-gradient(180deg, <?= $c[1] ?> 0%, <?= $c[0] ?> 100%);
                            box-shadow: 0 6px 20px <?= $c[0] ?>44;">
                </div>
              </div>
              <span class="material-symbols-outlined col-icon" style="color:<?= $c[0] ?>;"><?= $icon ?></span>
              <div class="col-label"><?= htmlspecialchars($cat['cname']) ?></div>
            </div>
          <?php endforeach; else: ?>
            <p class="text-slate-500 text-center py-8">No category data available</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Horizontal baseline -->
      <div style="height:1px; background: linear-gradient(90deg, transparent, #e2e8f0 10%, #e2e8f0 90%, transparent); margin: 0 0 0 48px;"></div>
    </div>
  </section>

  <div class="section-divider"></div>

  <!-- ===== NEW ARRIVALS ===== -->
  <section class="mt-8">
    <div class="flex justify-between items-end border-b border-slate-200 pb-3">
      <div>
        <span class="text-violet font-semibold text-xs uppercase tracking-wider">Discovery</span>
        <h2 class="text-2xl md:text-3xl font-bold text-slate-800">New Arrivals</h2>
      </div>
      <a href="#" class="text-violet font-medium flex items-center gap-1 text-sm hover:underline">
        View All <span class="material-symbols-outlined text-sm">arrow_forward</span>
      </a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-5 mt-6">
      <?php if ($recentBooks && count($recentBooks) > 0): 
        foreach ($recentBooks as $b): 
          $badgeClass = $b['is_paid'] == 'paid' ? 'badge-paid-white' : 'badge-free-white';
          $badgeText = $b['is_paid'] == 'paid' ? '₹' . htmlspecialchars($b['price']) : 'FREE';
      ?>
        <div class="card-white p-3">
          <div class="aspect-[3/4] rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center relative">
            <?php if ($b['CoverImage'] && file_exists('uploads/' . $b['CoverImage'])): ?>
              <img src="uploads/<?= htmlspecialchars($b['CoverImage']) ?>" alt="<?= htmlspecialchars($b['bname']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
              <span class="material-symbols-outlined text-5xl text-violet/60">menu_book</span>
            <?php endif; ?>
          </div>
          <div class="flex justify-between items-center mt-2">
            <h3 class="font-bold text-slate-800 text-sm truncate"><?= htmlspecialchars($b['bname']) ?></h3>
            <span class="<?= $badgeClass ?>"><?= $badgeText ?></span>
          </div>
          <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($b['AuthorName']) ?></p>
        </div>
      <?php endforeach; else: ?>
        <div class="col-span-full text-center py-8 text-slate-500">No books available yet. Check back soon!</div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ===== CATEGORIES ===== -->
  <section class="mt-16 bg-white/60 backdrop-blur-sm rounded-3xl p-6 md:p-10 border border-slate-200/60">
    <div class="text-center mb-8">
      <h2 class="text-2xl md:text-3xl font-bold text-slate-800">Navigate the Knowledge</h2>
      <p class="text-slate-500"><?= count($categories) ?> curated genres. Every story has a home.</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <?php if ($categories && count($categories) > 0): 
        foreach ($categories as $cat): 
          $catBookCount = $dao->query("SELECT COUNT(*) as count FROM books WHERE cid = " . $cat['cid'] . " AND status=1");
          $count = $catBookCount ? $catBookCount[0]['count'] : 0;
          $icon = $categoryIcons[$cat['cname']] ?? 'auto_stories';
      ?>
        <div class="flex items-center gap-3 bg-white p-3 rounded-xl shadow-sm border border-slate-100 hover:border-violet-200 transition">
          <span class="material-symbols-outlined text-violet"><?= $icon ?></span>
          <div>
            <h4 class="font-bold text-sm"><?= htmlspecialchars($cat['cname']) ?></h4>
            <p class="text-xs text-slate-400"><?= $count ?> Title<?= $count != 1 ? 's' : '' ?></p>
          </div>
        </div>
      <?php endforeach; else: ?>
        <div class="col-span-full text-center py-4 text-slate-500">No categories available.</div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ===== TRENDING BOOKS ===== -->
  <?php if ($trendingBooks && count($trendingBooks) > 0): ?>
  <section class="mt-16">
    <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-6">Most Wanted</h2>
    <div class="grid lg:grid-cols-2 gap-8">
      <?php 
      $rank = 1;
      foreach ($trendingBooks as $b): 
        $badgeClass = $b['is_paid'] == 'paid' ? 'badge-paid-white' : 'badge-free-white';
        $badgeText = $b['is_paid'] == 'paid' ? '₹' . htmlspecialchars($b['price']) : 'FREE';
      ?>
        <div class="card-white p-5 flex gap-4 items-start">
          <span class="text-3xl font-black text-violet/20"><?= str_pad($rank, 2, '0', STR_PAD_LEFT) ?></span>
          <div class="w-28 md:w-36 aspect-[3/4] rounded-xl overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center flex-shrink-0">
            <?php if ($b['CoverImage'] && file_exists('uploads/' . $b['CoverImage'])): ?>
              <img src="uploads/<?= htmlspecialchars($b['CoverImage']) ?>" alt="<?= htmlspecialchars($b['bname']) ?>" class="w-full h-full object-cover">
            <?php else: ?>
              <span class="material-symbols-outlined text-5xl text-violet/60">menu_book</span>
            <?php endif; ?>
          </div>
          <div>
            <div class="flex flex-wrap gap-1">
              <span class="<?= $badgeClass ?>"><?= $badgeText ?></span>
              <span class="text-xs bg-slate-100 px-2 py-0.5 rounded-full flex items-center gap-1">
                <span class="material-symbols-outlined text-xs">shopping_cart</span>
                <?= $b['download_count'] ?? 0 ?>
              </span>
            </div>
            <h3 class="font-bold text-lg text-slate-800"><?= htmlspecialchars($b['bname']) ?></h3>
            <p class="text-xs text-slate-500">By <?= htmlspecialchars($b['AuthorName']) ?></p>
            <a href="reader/readerLogin.php" class="text-violet font-medium text-sm flex items-center gap-1 mt-1">
              Read Excerpt <span class="material-symbols-outlined text-sm">trending_flat</span>
            </a>
          </div>
        </div>
      <?php 
        $rank++;
        endforeach; 
      ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===== FREE SPOTLIGHT ===== -->
  <?php if ($featuredFreeBook && count($featuredFreeBook) > 0): 
    $free = $featuredFreeBook[0];
  ?>
  <section class="mt-16 bg-white rounded-3xl p-6 md:p-10 border border-slate-200/70 shadow-sm flex flex-col md:flex-row gap-8 items-center">
    <div class="w-48 md:w-56 aspect-[3/4] rounded-2xl overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center flex-shrink-0">
      <?php if ($free['CoverImage'] && file_exists('uploads/' . $free['CoverImage'])): ?>
        <img src="uploads/<?= htmlspecialchars($free['CoverImage']) ?>" alt="<?= htmlspecialchars($free['bname']) ?>" class="w-full h-full object-cover">
      <?php else: ?>
        <span class="material-symbols-outlined text-6xl text-violet/40">menu_book</span>
      <?php endif; ?>
    </div>
    <div>
      <span class="text-emerald-600 font-bold text-xs uppercase tracking-wider bg-emerald-50 px-3 py-1 rounded-full">Free to Read</span>
      <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mt-2"><?= htmlspecialchars($free['bname']) ?></h2>
      <p class="text-slate-600 max-w-md mt-2 text-sm">
        <?= htmlspecialchars(substr($free['Description'] ?? 'A captivating story waiting for you.', 0, 150)) ?>
      </p>
      <div class="flex flex-wrap gap-2 mt-3">
        <span class="bg-slate-100 px-3 py-1 rounded-full text-xs"><?= htmlspecialchars($free['category'] ?? 'General') ?></span>
        <span class="bg-slate-100 px-3 py-1 rounded-full text-xs">By <?= htmlspecialchars($free['AuthorName']) ?></span>
        <span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-xs">No payment</span>
      </div>
      <a href="reader/readerLogin.php" class="btn-violet mt-4 px-8 py-2.5 inline-block">Read Now — Free</a>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===== READER REVIEWS ===== -->
  <?php if ($reviews && count($reviews) > 0): ?>
  <section class="mt-16">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
      <h2 class="text-2xl md:text-3xl font-bold text-slate-800">Reader Voices</h2>
      <p class="text-slate-400 text-sm">Honest words from our community</p>
    </div>
    <div class="grid md:grid-cols-2 gap-5">
      <?php foreach ($reviews as $rev): ?>
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm relative">
        <span class="material-symbols-outlined absolute right-4 top-4 text-5xl text-violet/10">format_quote</span>
        <div class="flex text-yellow-400 text-lg">
          <?php 
            $rating = round($rev['rating'] ?? 5);
            for ($i = 1; $i <= 5; $i++): 
              echo $i <= $rating ? '★' : '<span class="text-slate-300">☆</span>';
            endfor; 
          ?>
        </div>
        <p class="italic text-slate-700 mt-2">"<?= htmlspecialchars(substr($rev['review_text'], 0, 120)) ?>"</p>
        <div class="flex items-center gap-3 mt-4">
          <div class="w-10 h-10 rounded-full bg-violet-soft flex items-center justify-center text-violet font-bold">
            <?= strtoupper(substr(htmlspecialchars($rev['username']), 0, 1)) ?>
          </div>
          <div>
            <p class="font-semibold text-sm"><?= htmlspecialchars($rev['username']) ?></p>
            <p class="text-xs text-slate-400">Reader · <?= date('M d, Y', strtotime($rev['review_date'])) ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===== AUTHOR CTA ===== -->
  <section class="mt-16 bg-white rounded-3xl p-8 md:p-12 text-center border border-slate-200/60 shadow-sm relative overflow-hidden">
    <span class="text-violet font-semibold text-sm uppercase tracking-wider">For Authors</span>
    <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mt-1">Publish Your Story</h2>
    <p class="text-slate-600 max-w-lg mx-auto mt-2">
      Join <?= $authorCount ?> authors on our platform. Upload your manuscript, set your price — or make it free.
    </p>
    <div class="flex flex-wrap justify-center gap-3 mt-6">
      <a href="author/login.php" class="btn-violet px-8 py-3">Become an Author</a>
      <a href="#" class="btn-outline-violet px-8 py-3">Learn More</a>
    </div>
  </section>

</main>

<!-- ===== FOOTER ===== -->
<footer class="mt-12 border-t border-slate-200/80 py-8 px-4 text-center text-sm text-slate-500 bg-white/50 backdrop-blur-sm">
  <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-3">
    <span class="font-bold text-slate-700">Ready 2 Read</span>
    <div class="flex gap-6">
      <a href="#" class="hover:text-violet">Privacy</a>
      <a href="#" class="hover:text-violet">Terms</a>
      <a href="#" class="hover:text-violet">Support</a>
      <a href="author/login.php" class="hover:text-violet">For Authors</a>
    </div>
    <span>© 2026 Digital Library</span>
  </div>
</footer>

</div><!-- end main-content -->

<!-- ===== JAVASCRIPT ===== -->
<script>
// ===== LOADER =====
(function () {
    const loader  = document.getElementById('loader');
    const counter = document.getElementById('loaderCounter');
    document.body.style.overflow = 'hidden';

    // Generate floating page particles
    const particleContainer = document.getElementById('loaderParticles');
    if (particleContainer) {
        const count = 28;
        for (let i = 0; i < count; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.setProperty('--x',      Math.random() * 100 + 'vw');
            p.style.setProperty('--dur',    (3 + Math.random() * 5) + 's');
            p.style.setProperty('--delay',  (Math.random() * 4) + 's');
            p.style.setProperty('--drift',  (Math.random() * 80 - 40) + 'px');
            p.style.width  = (3 + Math.random() * 4) + 'px';
            p.style.height = (4 + Math.random() * 5) + 'px';
            p.style.opacity = 0.2 + Math.random() * 0.5;
            particleContainer.appendChild(p);
        }
    }

    // Counting progress
    let pct = 0;
    const interval = setInterval(() => {
        pct = Math.min(pct + Math.floor(Math.random() * 10) + 3, 99);
        counter.textContent = pct + '%';
    }, 55);

    const finishLoader = () => {
        clearInterval(interval);
        counter.textContent = '100%';
        setTimeout(() => {
            loader.classList.add('hide');
            document.body.style.overflow = '';
            setTimeout(() => loader.remove(), 950);
        }, 400);
    };

    Promise.all([
        new Promise(r => setTimeout(r, 2000)),
        new Promise(r => {
            if (document.readyState === 'complete') r();
            else window.addEventListener('load', r);
        })
    ]).then(finishLoader);
})();

// ===== COUNTER ANIMATION =====
function animateCounter(el, target) {
    let start = 0;
    const duration = 2000;
    const step = ts => {
        if (!start) start = ts;
        const progress = Math.min((ts - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.floor(eased * target).toLocaleString();
        if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
}

const counters = document.querySelectorAll('.counter-value');
let counted = false;
const counterObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting && !counted) {
            counted = true;
            counters.forEach(el => animateCounter(el, parseInt(el.dataset.target)));
        }
    });
}, { threshold: 0.3 });
if (counters[0]) counterObs.observe(counters[0].closest('.stat-card'));

// ===== COLUMN CHART ANIMATION =====
function animateColumns() {
    const fills = document.querySelectorAll('.col-bar-fill');
    fills.forEach((fill, i) => {
        const h = fill.dataset.height || 0;
        setTimeout(() => {
            fill.style.height = h + '%';
            // Show count label once bar appears
            const countEl = fill.closest('.col-bar-group')?.querySelector('.col-count');
            if (countEl) countEl.classList.add('visible');
        }, i * 80 + 200);
    });
}

const colChart = document.getElementById('colChart');
if (colChart) {
    const colObs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateColumns();
                colObs.disconnect();
            }
        });
    }, { threshold: 0.15 });
    colObs.observe(colChart);
}

// ===== SCROLL REVEAL FOR CARDS =====
document.querySelectorAll('.card-white, .stat-card, .testimonial-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    const obs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    obs.observe(el);
});

// ===== THEME TOGGLE & PERSISTENCE =====
(function() {
    const toggleBtn = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    const navbar = document.querySelector('.glass-dock');
    
    if (!toggleBtn || !themeIcon) return;

    function updateNavbarScroll() {
        if (!navbar) return;
        const isDark = document.documentElement.classList.contains('dark');
        if (window.scrollY > 50) {
            navbar.style.background = isDark ? 'rgba(15, 12, 27, 0.85)' : 'rgba(255, 255, 255, 0.85)';
            navbar.style.boxShadow = isDark ? '0 8px 32px rgba(0, 0, 0, 0.3)' : '0 8px 32px rgba(0, 0, 0, 0.1)';
            navbar.style.borderColor = isDark ? 'rgba(124, 58, 237, 0.25)' : 'rgba(255, 255, 255, 0.5)';
        } else {
            navbar.style.background = isDark ? 'rgba(15, 12, 27, 0.6)' : 'rgba(255, 255, 255, 0.55)';
            navbar.style.boxShadow = isDark ? '0 8px 32px rgba(0, 0, 0, 0.25)' : '0 8px 32px rgba(0, 0, 0, 0.08)';
            navbar.style.borderColor = isDark ? 'rgba(124, 58, 237, 0.2)' : 'rgba(255, 255, 255, 0.5)';
        }
    }

    function updateThemeUI(isDark) {
        if (isDark) {
            document.documentElement.classList.add('dark');
            themeIcon.textContent = 'light_mode';
            themeIcon.style.color = '#fbbf24'; // Premium golden sun color
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            themeIcon.textContent = 'dark_mode';
            themeIcon.style.color = ''; // Default brand color
            localStorage.setItem('theme', 'light');
        }
        updateNavbarScroll();
    }

    // Initial state sync
    const isDark = document.documentElement.classList.contains('dark');
    updateThemeUI(isDark);

    // Toggle click handler
    toggleBtn.addEventListener('click', () => {
        const currentlyDark = document.documentElement.classList.contains('dark');
        updateThemeUI(!currentlyDark);
    });

    // Window scroll handler
    window.addEventListener('scroll', updateNavbarScroll);
})();
</script>

</body>
</html>