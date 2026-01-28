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
        
            .equipment-list {
                display: flex;
                flex-direction: column;
                border: 1px solid #e5e7eb;
                border-radius: 0.375rem;
                overflow: hidden;
            }
            
            .equipment-item {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem 0.75rem;
                border-bottom: 1px solid #f3f4f6;
            }

            .equipment-item:nth-child(even) {
                background-color: #f9fafb;
            }
            
            .equipment-item:last-child {
                border-bottom: none;
            }
            
            .equipment-item .eq-name {
                color: #374151;
                font-weight: 500;
                font-size: 0.9em;
            }

            .equipment-item .eq-qty {
                font-weight: 600;
                color: #1f2937;
                background: #f3f4f6;
                padding: 0.1rem 0.4rem;
                border-radius: 4px;
                font-size: 0.9em;
                min-width: 20px;
                text-align: center;
            }

            @media print {
                @page {
                    margin: 10mm;
                    size: A4;
                }
                
                body {
                    background: white;
                    font-size: 8pt;
                    line-height: 1.2;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                #clubDetails {
                    border: 3px double #1e3a8a;
                    padding: 15px; /* Padding inside the border */
                    min-height: 260mm; /* Ensure border stretches to approx A4 height */
                    position: relative;
                    box-sizing: border-box;
                }
                
                .no-print, .gov-header, footer, .accessibility-fab, .accessibility-panel {
                    display: none !important;
                }
                
                .print-header {
                    display: block !important;
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #1e3a8a;
                }
                
                .print-header .dept-name {
                    font-size: 10pt;
                    font-weight: bold;
                    color: #4b5563;
                    text-transform: uppercase;
                    margin-bottom: 5px;
                }
                
                .print-header h1 {
                    font-size: 20pt;
                    font-weight: 900;
                    color: #1e3a8a;
                    margin: 5px 0;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    line-height: 1.1;
                }
                
                .print-header .club-name-display {
                    font-size: 14pt;
                    font-weight: bold;
                    color: #000;
                    margin-top: 10px;
                    text-decoration: none;
                    background: #f3f4f6;
                    padding: 5px 15px;
                    border-radius: 20px;
                    display: inline-block;
                    border: 1px solid #d1d5db;
                }
    
                /* Subtitle and doc-title classes removed/unused now */
                
                .info-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 10px;
                }
                
                .detail-card {
                    box-shadow: none;
                    border: 1px solid #9ca3af;
                    page-break-inside: avoid;
                    margin-bottom: 8px;
                    padding: 8px;
                    background: #fff;
                    border-radius: 4px;
                }
                
                .detail-card h2 {
                    font-size: 9pt;
                    font-weight: bold;
                    color: #fff; /* White text on blue background */
                    background: #1e3a8a; /* Dark blue background */
                    padding: 4px 8px;
                    margin: -8px -8px 8px -8px; /* Full width header */
                    border-radius: 3px 3px 0 0;
                    border-left: none;
                    text-transform: uppercase;
                }
                
                .detail-card label {
                    font-weight: 700;
                    color: #374151;
                    font-size: 7.5pt;
                    display: block;
                    margin-bottom: 1px;
                    text-transform: uppercase;
                    opacity: 0.8;
                }
                
                .detail-card p {
                    color: #000;
                    font-size: 9pt;
                    font-weight: 500;
                    margin-top: 0;
                    border-bottom: 1px solid #e5e7eb;
                    padding-bottom: 2px;
                    min-height: 14px;
                }
                
                .info-row {
                    margin-bottom: 6px;
                    gap: 1rem;
                }

                .equipment-list {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr); /* 3 Columns grid */
                    gap: 8px; /* Spacing between items */
                    border: 1px solid #9ca3af;
                    padding: 8px;
                }

                .equipment-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    break-inside: avoid;
                    border: 1px solid #e5e7eb; /* Box style for grid items */
                    padding: 4px 6px;
                    background: #fff;
                    border-radius: 3px;
                }

                .equipment-item:last-child {
                    border-bottom: 1px solid #e5e7eb; /* Reset default style override */
                }
                
                .equipment-item .eq-name {
                    font-weight: 500;
                    padding-right: 5px;
                    font-size: 7.5pt;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
                
                .equipment-item .eq-qty {
                    background: #f3f4f6;
                    padding: 1px 6px;
                    border-radius: 4px;
                    font-weight: 700;
                    font-size: 7pt;
                    border: 1px solid #d1d5db;
                }
                
                .print-footer {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    width: 100%;
                    padding: 0 15px 10px 15px; /* Padding inside the footer area */
                    box-sizing: border-box;
                }

                .signatures {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 30px; /* Space between signature and footer note */
                    padding: 0 10px;
                }

                .sig-block {
                    text-align: center;
                    width: 200px;
                }

                .sig-line {
                    color: #9ca3af;
                    margin-bottom: 5px;
                    letter-spacing: -1px;
                    overflow: hidden;
                    white-space: nowrap;
                    font-weight: bold;
                }

                .sig-label {
                    font-weight: bold;
                    font-size: 9pt;
                    color: #000;
                    text-transform: uppercase;
                }
                
                .footer-bottom {
                    border-top: 2px solid #1e3a8a;
                    padding-top: 8px;
                    text-align: center;
                }

                .footer-bottom p {
                    margin: 0;
                    border: none;
                    font-size: 7pt;
                    color: #374151;
                    font-weight: normal;
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
$scripts = ['../assets/js/club-details.js?v=' . time()];

// Include footer
include '../includes/footer.php';
?>

<script>
// Make user role available to JavaScript
window.currentUserRole = '<?php echo htmlspecialchars(getCurrentRole() ?? 'admin', ENT_QUOTES, 'UTF-8'); ?>';
</script>