# System Daily Report Generator

This application generates a daily report in PDF format from specified database tables and sends it via email. The report includes data from various tables in the database, formatted as an HTML table within a PDF document.

## Features

- Connects to a MySQL database and retrieves data from specified tables.
- Generates a PDF report with data from these tables.
- Includes a header and a logo in the PDF.
- Sends the generated PDF report via email.

## Prerequisites

- PHP 7.4 or higher
- Composer (for managing PHP dependencies)
- MySQL database
- SMTP server for sending emails (e.g., Gmail)

## Installation

1. **Clone the Repository**

   ```bash
   git clone https://github.com/yourusername/yourrepository.git
   cd yourrepository


composer install
DB_SERVER=localhost
DB_USER=root
DB_PASS=yourpassword
DB_NAME=readings

If using Gmail or another SMTP provider, update the SMTP settings in the PHPMailer configuration:

$mail->Host       = 'smtp.gmail.com'; 
$mail->SMTPAuth   = true;
$mail->Username   = 'your-email@gmail.com'; 
$mail->Password   = 'your-email-password';  
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
$mail->Port       = 465; 
