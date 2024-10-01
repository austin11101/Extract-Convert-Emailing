<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$pdf = new TCPDF();

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Austin');
$pdf->SetTitle('Database Tables');
$pdf->SetSubject('System Daily Report');
$pdf->SetKeywords('TCPDF, PDF, system, daily, reports');

$pdf->SetHeaderData('', 0, 'System Daily Report', 'Generated PDF', [0,0,0], [255,255,255]);
$pdf->setHeaderData('', 0, '', '', [0,0,0], [255,255,255]);

$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(10); 
$pdf->SetFooterMargin(10);

$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

$pdf->SetFont('helvetica', '', 12);

$pdf->AddPage();

$logoPath = __DIR__ . '/logo2.png';
$pdf->Image($logoPath, 10, 10, 0, 20, 'PNG', '', 'T', true, 300, '', false, false, 0, false, false, false);

$pdf->SetFont('helvetica', 'B', 20);
$pdf->Cell(0, 10, 'System Daily Report', 0, 1, 'R');
$pdf->SetFont('helvetica', '', 12);
$pdf->Ln();

$servername = getenv('DB_SERVER') ?: '*.*.*.*';
$username = getenv('DB_USER') ?: '****';
$password = getenv('DB_PASS') ?: '******';
$database = getenv('DB_NAME') ?: 'readings';
$conn = new mysqli($servername, $username, $password, $database, port:3306, socket: '/opt/lampp/var/mysql/mysql.sock');

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

    // Get the columns for the table
    $columnsResult = $conn->query("SHOW COLUMNS FROM `$table`");
    if ($columnsResult === false) {
        continue; // Skip the table if there was an error fetching columns
    }

    $dateColumn = null; // To store the date column name

    // Loop through the columns to find 'Date' or 'Data'
    while ($column = $columnsResult->fetch_assoc()) {
        if ($column['Field'] === 'Date' || $column['Field'] === 'Data') {
            $dateColumn = $column['Field'];
            break; // Break once we find a matching date column
        }
    }

    // Check if a date column was found
    if (!$dateColumn) {
        echo "No 'Date' or 'Data' column found in table '$table'. Skipping...\n";
        continue; // Skip this table if neither 'Date' nor 'Data' columns are found
    }

    // If we found a date column, proceed with the query for the 7th, 8th, 9th, or 10th day of any month
    $sql = "SELECT * FROM `$table` WHERE DAY(`$dateColumn`) IN (7, 8, 9, 10)";
    $result = $conn->query($sql);

    if ($result === false || $result->num_rows == 0) {
        echo "No results found for table '$table' on the 7th, 8th, 9th, or 10th.\n";
        continue; // Skip if no data is found for these days
    }

    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 10, 'Table: ' . htmlspecialchars($table), 0, 1, 'L'); 
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

$date = date('Y-m-d');
$filename = __DIR__ . "/view_$date.pdf";

$pdf->Output($filename, 'F'); 

echo "PDF has been created as " . htmlspecialchars($filename);

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; 
    $mail->SMTPAuth   = true;
    $mail->addAttachment($filename); 
    $mail->Username   = '******28@gmail.com'; 
    $mail->Password   = '*********';  
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
    $mail->Port       = ****; 
    $mail->setFrom('*********28@gmail.com', 'Austin');
    $mail->addAddress('*******567@gmail.com', 'Recipient Name');

    $mail->isHTML(true); 
    $mail->Subject = 'Daily Report PDF';
    $mail->Body    = 'Dear Client,<br><br>Please find the daily report attached.<br><br>Best regards,<br>Rasi';
    $mail->AltBody = 'Dear recipient, Please find the daily report attached. Best regards, Rasi';

    $mail->send();
    echo 'Email has been sent successfully.';
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
