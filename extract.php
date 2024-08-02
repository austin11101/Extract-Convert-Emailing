<?php

require 'vendor/autoload.php'; 

$pdf = new TCPDF();


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Austin');
$pdf->SetTitle('Database Tables');
$pdf->SetSubject('System Daily Report');
$pdf->SetKeywords('TCPDF, PDF, system, daily, reports');


$pdf->SetHeaderData('', 0, 'System Daily Report', 'Generated PDF');


$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);


$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->SetFont('helvetica', '', 12);


$pdf->AddPage();


$servername = getenv('DB_SERVER') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: 'Muxe@2001';
$database = getenv('DB_NAME') ?: 'readings';
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tables = [
    "door_data",
    "motion_data",
    "smoke_data",
    "tempdata",
    "vibration_data",
    "water_data",
];

foreach ($tables as $table) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
        continue;
    }

    $sql = "SELECT * FROM `$table`";
    $result = $conn->query($sql);

    if ($result === false) {
        continue;
    }

    
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 10, 'Table: ' . htmlspecialchars($table), 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Ln();


    $html = '<style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
         td {
            border: 1px solid #ddd;
            padding: 20px;
            text-align: center;
            
        }

          th {
            border: 1px solid #ddd;
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            }


    </style>
    <table>
        <thead>
            <tr>';

    $fields = $result->fetch_fields();
    $columns = [];

    foreach ($fields as $field) {
        if ($field->name !== 'id') {
            $columns[] = htmlspecialchars($field->name);
        }
    }

    foreach ($columns as $column) {
        $html .= '<th>' . $column . '</th>';
    }

    $html .= '</tr>
        </thead>
        <tbody>';

    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        foreach ($columns as $column) {
            $html .= '<td>' . htmlspecialchars($row[$column]) . '</td>';
        }
        $html .= '</tr>';
    }

    $html .= '</tbody>
    </table>';

    
    $pdf->writeHTML($html, true, false, true, false, '');
}


$conn->close();


$pdf->Output(__DIR__ . '/view.pdf', 'F'); 

echo "PDF has been created as view.pdf";
?>