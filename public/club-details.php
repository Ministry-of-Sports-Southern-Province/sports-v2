<?php

/**
 * Club Details Page
 * Displays detailed information about a specific sports club
 */

// Page configuration
$pageTitle = 'page.club_details_title';
$pageHeading = 'page.club_details_title';
$activePage = 'club-details';

// Custom styles for this page
$customStyles = '
        .detail-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .detail-card h2 {
            font-size: 1.25rem;
            font-weight: bold;
            color: #1f2937;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .info-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 0.75rem;
        }
        
        .info-row .full-width {
            grid-column: 1 / -1;
        }
        
        .info-row label {
            font-weight: 600;
            color: #4b5563;
            font-size: 0.875rem;
            display: block;
            margin-bottom: 0.25rem;
        }
        
        .info-row p {
            color: #1f2937;
            font-size: 1rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
        }
        
        table th, table td {
            border: 1px solid #d1d5db;
            padding: 0.5rem;
            text-align: left;
        }
        
        table th {
            background: #f3f4f6;
            font-weight: 600;
            color: #374151;
        }
        
        @media print {
            @page {
                margin: 1cm;
                size: A4;
            }
            
            body {
                background: white;
                font-size: 9pt;
                line-height: 1.3;
            }
            
            .no-print, .gov-header, footer {
                display: none !important;
            }
            
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 3px double #1e3a8a;
            }
            
            .print-header h1 {
                font-size: 12pt;
                font-weight: bold;
                color: #1e3a8a;
                margin-bottom: 3px;
            }
            
            .print-header .subtitle {
                font-size: 8pt;
                color: #374151;
            }
            
            .print-header .doc-title {
                font-size: 10pt;
                font-weight: bold;
                color: #1e3a8a;
                margin-top: 5px;
                text-decoration: underline;
            }
            
            .detail-card {
                box-shadow: none;
                border: 1px solid #374151;
                page-break-inside: avoid;
                margin-bottom: 8px;
                padding: 8px;
                background: #fff;
            }
            
            .detail-card h2 {
                font-size: 10pt;
                font-weight: bold;
                color: #1e3a8a;
                background: #e5e7eb;
                padding: 3px 5px;
                margin-bottom: 6px;
                border-left: 3px solid #1e3a8a;
            }
            
            .detail-card label {
                font-weight: 600;
                color: #374151;
                font-size: 8pt;
            }
            
            .detail-card p {
                color: #000;
                font-size: 9pt;
                margin-top: 1px;
                border-bottom: 1px dotted #d1d5db;
                padding-bottom: 2px;
            }
            
            .info-row {
                margin-bottom: 4px;
            }
            
            table {
                border-collapse: collapse;
                width: 100%;
                font-size: 8pt;
                border: 2px solid #374151;
            }
            
            table th {
                background: #e5e7eb;
                border: 1px solid #374151;
                padding: 3px;
                font-weight: bold;
                text-align: left;
                font-size: 8pt;
                color: #1e3a8a;
            }
            
            table td {
                border: 1px solid #9ca3af;
                padding: 3px;
            }
            
            .print-footer {
                margin-top: 15px;
                padding-top: 8px;
                border-top: 2px solid #374151;
                font-size: 7pt;
                color: #374151;
                text-align: center;
            }
            
            .print-footer p {
                margin: 2px 0;
                border: none;
            }
        }
';

// No additional links needed for this page
$additionalLinks = [];

// Include header
include '../includes/header.php';
?>

<main class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-4 flex justify-between items-center no-print">
        <a href="dashboard.php" class="text-blue-600 hover:text-blue-800" data-i18n="button.back">← ආපසු</a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            🖨️ <span data-i18n="button.print">මුද්රණය කරන්න</span>
        </button>
    </div>

    <div id="clubDetails">
        <div class="text-center py-8">
            <span data-i18n="message.loading">පූරණය වෙමින්...</span>
        </div>
    </div>
</main>

<?php
// Scripts to include
$scripts = ['../assets/js/club-details.js'];

// Include footer
include '../includes/footer.php';
?>