<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/lib/fpdf.php';

$reg_id = isset($_GET['reg_id']) ? trim($_GET['reg_id']) : '';
if ($reg_id === '') {
    http_response_code(400);
    echo 'reg_id missing';
    exit;
}

$sql = "SELECT cro.reorg_date, cr.club_name, cr.district, cr.division
        FROM club_reorg cro
        LEFT JOIN club_register cr ON cro.reg_id COLLATE utf8mb4_general_ci = cr.reg_id COLLATE utf8mb4_general_ci
        WHERE cro.reg_id = ?
        ORDER BY cro.reorg_date DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('s', $reg_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $title = 'Reorganization History - ' . $reg_id;
    $pdf->Cell(0, 10, $title, 0, 1);
    $pdf->Ln(4);
    $pdf->SetFont('Arial', '', 12);

    // Header
    $pdf->Cell(50, 8, 'Club Name', 1);
    $pdf->Cell(40, 8, 'District', 1);
    $pdf->Cell(40, 8, 'Division', 1);
    $pdf->Cell(40, 8, 'Reorg Date', 1);
    $pdf->Ln();

    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(50, 8, ($row['club_name'] ?: 'N/A'), 1);
        $pdf->Cell(40, 8, ($row['district'] ?: 'N/A'), 1);
        $pdf->Cell(40, 8, ($row['division'] ?: 'N/A'), 1);
        $pdf->Cell(40, 8, ($row['reorg_date'] ?: 'N/A'), 1);
        $pdf->Ln();
    }

    $filename = 'reorg_history_' . preg_replace('/[^a-zA-Z0-9-_]/', '_', $reg_id) . '.pdf';
    $pdf->Output('D', $filename);
    $stmt->close();
    $conn->close();
    exit;
} else {
    http_response_code(500);
    echo 'DB error: ' . $conn->error;
}
