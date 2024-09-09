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
    $mail->Host       = 'smtp.*****.com'; 
    $mail->SMTPAuth   = true;
    $mail->addAttachment($filename); 
    $mail->Username   = 'austin****@gmail.com'; 
    $mail->Password   = '**** **** **** ****';  
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
    $mail->Port       = 465; 
    $mail->setFrom('austin****@gmail.com', 'Austin');
    $mail->addAddress('baloyi*****@gmail.com', 'Recipient Name');

    $mail->isHTML(true); 
    $mail->Subject = 'Daily Report PDF';
    $mail->Body    = 'Dear Client,<br><br>Please find the daily report attached.<br><br>Best regards,<br>Rasi';
    $mail->AltBody = 'Dear recipient, Please find the daily report attached. Best regards, Rasi';

    $mail->send();
    echo 'Email has been sent successfully.';
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
