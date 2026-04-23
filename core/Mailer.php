<?php
declare(strict_types=1);

class Mailer
{
    public static function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $config = require __DIR__ . '/../config/mail.php';

        try {
            $host = $config['host'] ?? 'smtp.gmail.com';
            $port = (int) ($config['port'] ?? 587);
            $username = $config['username'] ?? '';
            $password = $config['password'] ?? '';
            $fromEmail = $config['from_email'] ?? '';
            $fromName = $config['from_name'] ?? 'Barber Spa';

            // If no credentials, use PHP mail() function
            if (empty($username) || empty($password)) {
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            // Use SMTP via streams
            return self::sendViaSMTP($host, $port, $username, $password, $toEmail, $toName, $subject, $body, $fromEmail, $fromName);
        } catch (Exception $e) {
            error_log('Mailer Error: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendViaPHP(string $toEmail, string $toName, string $subject, string $body, string $fromEmail, string $fromName): bool
    {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $fromEmail . "\r\n";
        $headers .= "Reply-To: " . $fromEmail . "\r\n";

        $result = mail($toEmail, $subject, $body, $headers);
        
        if (!$result) {
            error_log('Mail Error: Failed to send email to ' . $toEmail);
            return false;
        }
        
        return true;
    }

    private static function sendViaSMTP(
        string $host,
        int $port,
        string $username,
        string $password,
        string $toEmail,
        string $toName,
        string $subject,
        string $body,
        string $fromEmail,
        string $fromName
    ): bool {
        try {
            // Connect to SMTP server
            $socket = @fsockopen($host, $port, $errno, $errstr, 10);
            
            if (!$socket) {
                error_log('SMTP Connection Error: ' . $errstr . ' (' . $errno . ')');
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            $out = "";
            
            // Read server greeting
            $response = fgets($socket, 1024);
            if (strpos($response, '220') === false) {
                fclose($socket);
                error_log('SMTP Error: Bad greeting - ' . $response);
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            // Send EHLO
            fwrite($socket, "EHLO localhost\r\n");
            $response = fgets($socket, 1024);
            while (substr($response, 3, 1) != ' ') {
                $response = fgets($socket, 1024);
                if (!$response) break;
            }

            // Start TLS
            fwrite($socket, "STARTTLS\r\n");
            $response = fgets($socket, 1024);
            
            if (strpos($response, '220') === false) {
                fclose($socket);
                error_log('SMTP Error: STARTTLS not available');
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            // Enable encryption
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                error_log('SMTP Error: Could not start TLS encryption');
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            // Send EHLO again after TLS
            fwrite($socket, "EHLO localhost\r\n");
            $response = fgets($socket, 1024);
            while (substr($response, 3, 1) != ' ') {
                $response = fgets($socket, 1024);
                if (!$response) break;
            }

            // Authenticate
            fwrite($socket, "AUTH LOGIN\r\n");
            $response = fgets($socket, 1024);
            
            if (strpos($response, '334') === false) {
                fclose($socket);
                error_log('SMTP Error: AUTH not accepted');
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            // Send username
            fwrite($socket, base64_encode($username) . "\r\n");
            $response = fgets($socket, 1024);
            
            if (strpos($response, '334') === false) {
                fclose($socket);
                error_log('SMTP Error: Username rejected');
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            // Send password
            fwrite($socket, base64_encode($password) . "\r\n");
            $response = fgets($socket, 1024);
            
            if (strpos($response, '235') === false) {
                fclose($socket);
                error_log('SMTP Error: Authentication failed - ' . $response);
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            // Send MAIL FROM
            fwrite($socket, "MAIL FROM:<" . $fromEmail . ">\r\n");
            $response = fgets($socket, 1024);
            
            if (strpos($response, '250') === false) {
                fclose($socket);
                error_log('SMTP Error: MAIL FROM rejected');
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            // Send RCPT TO
            fwrite($socket, "RCPT TO:<" . $toEmail . ">\r\n");
            $response = fgets($socket, 1024);
            
            if (strpos($response, '250') === false) {
                fclose($socket);
                error_log('SMTP Error: RCPT TO rejected');
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            // Send DATA
            fwrite($socket, "DATA\r\n");
            $response = fgets($socket, 1024);
            
            if (strpos($response, '354') === false) {
                fclose($socket);
                error_log('SMTP Error: DATA not accepted');
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            // Normalize body newlines to CRLF to prevent bare line feeds
            $body = str_replace(["\r\n", "\r", "\n"], "\r\n", $body);
            
            // Encode subject to support UTF-8 characters (like Vietnamese)
            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

            $messageId = md5(uniqid((string)microtime(true), true)) . "@barberspa.vn";

            // Build email message
            $message = "Date: " . date('r') . "\r\n";
            $message .= "Message-ID: <" . $messageId . ">\r\n";
            $message .= "To: " . $toName . " <" . $toEmail . ">\r\n";
            $message .= "From: " . $fromName . " <" . $fromEmail . ">\r\n";
            $message .= "Subject: " . $encodedSubject . "\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-type: text/html; charset=UTF-8\r\n";
            $message .= "\r\n";
            $message .= $body . "\r\n";

            // Send message
            fwrite($socket, $message . "\r\n.\r\n");
            $response = fgets($socket, 1024);
            
            if (strpos($response, '250') === false) {
                fclose($socket);
                error_log('SMTP Error: Message rejected - ' . $response);
                return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
            }

            // Send QUIT
            fwrite($socket, "QUIT\r\n");
            fclose($socket);

            return true;
        } catch (Exception $e) {
            error_log('SMTP Error: ' . $e->getMessage());
            return self::sendViaPHP($toEmail, $toName, $subject, $body, $fromEmail, $fromName);
        }
    }
}
