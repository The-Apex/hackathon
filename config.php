<?php
// Centralized Configuration Helper for BloodSync

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Remove optional surrounding quotes
            $val = trim($val, '"\'');
            putenv("$key=$val");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
}

// Google Maps API Key
$apiKey = getenv('GOOGLE_MAPS_API_KEY') ?: ($_ENV['GOOGLE_MAPS_API_KEY'] ?? ($_SERVER['GOOGLE_MAPS_API_KEY'] ?? ''));
define('GOOGLE_MAPS_API_KEY', $apiKey);

function getGoogleMapsApiKey() {
    return defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '';
}

/**
 * Returns script tag to load Google Maps JS API with Places library and callback.
 * If no API key is configured, loads with an empty key so standard fallback logic fires cleanly.
 */
function renderGoogleMapsScript($callback = 'initGoogleMaps') {
    $key = htmlspecialchars(getGoogleMapsApiKey());
    $src = "https://maps.googleapis.com/maps/api/js?key={$key}&libraries=places&callback={$callback}";
    return "<script async defer src=\"{$src}\"></script>";
}

/**
 * Dispatches an SMS to the recipient's phone number.
 * Supports Fast2SMS API, Twilio API, or logs to database with WhatsApp direct url.
 */
function sendSmsNotification($phone, $message) {
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($cleanPhone) === 10) {
        $tenDigit = $cleanPhone;
    } elseif (strlen($cleanPhone) === 12 && substr($cleanPhone, 0, 2) === '91') {
        $tenDigit = substr($cleanPhone, 2);
    } else {
        $tenDigit = $cleanPhone;
    }

    $fast2smsKey = getenv('FAST2SMS_API_KEY') ?: ($_ENV['FAST2SMS_API_KEY'] ?? '');
    if (!empty($fast2smsKey) && strlen($tenDigit) === 10) {
        $fields = [
            "sender_id" => "TXTIND",
            "message" => $message,
            "route" => "v3",
            "numbers" => $tenDigit
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.fast2sms.com/dev/bulkV2");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "authorization: " . $fast2smsKey,
            "Content-Type: application/json"
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            return ['status' => 'sent', 'gateway' => 'fast2sms', 'response' => $response];
        }
    }

    // Twilio Support
    $twilioSid = getenv('TWILIO_SID') ?: ($_ENV['TWILIO_SID'] ?? '');
    $twilioToken = getenv('TWILIO_TOKEN') ?: ($_ENV['TWILIO_TOKEN'] ?? '');
    $twilioFrom = getenv('TWILIO_FROM') ?: ($_ENV['TWILIO_FROM'] ?? '');
    if (!empty($twilioSid) && !empty($twilioToken) && !empty($twilioFrom)) {
        $toPhone = (strlen($cleanPhone) === 10) ? '+91' . $cleanPhone : '+' . $cleanPhone;
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$twilioSid}:{$twilioToken}");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'To' => $toPhone,
            'From' => $twilioFrom,
            'Body' => $message
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code >= 200 && $code < 300) {
            return ['status' => 'sent', 'gateway' => 'twilio', 'response' => $res];
        }
    }

    return ['status' => 'logged', 'gateway' => 'local_simulation'];
}

/**
 * Dispatches an Email to the recipient.
 * Supports direct authenticated SMTP (Gmail, Outlook, Brevo, SendGrid, etc.)
 * or falls back cleanly to PHP mail() / local database logging.
 */
function sendEmailNotification($to, $subject, $htmlBody, $replyTo = '') {
    $smtpHost = getenv('SMTP_HOST') ?: ($_ENV['SMTP_HOST'] ?? '');
    $smtpPort = (int)(getenv('SMTP_PORT') ?: ($_ENV['SMTP_PORT'] ?? 587));
    $smtpUser = getenv('SMTP_USER') ?: ($_ENV['SMTP_USER'] ?? '');
    $smtpPass = getenv('SMTP_PASS') ?: ($_ENV['SMTP_PASS'] ?? '');
    $smtpSecure = getenv('SMTP_SECURE') ?: ($_ENV['SMTP_SECURE'] ?? ($smtpPort == 465 ? 'ssl' : 'tls'));
    $fromEmail = getenv('SMTP_FROM') ?: ($_ENV['SMTP_FROM'] ?? 'no-reply@bloodsync.com');
    $fromName = getenv('SMTP_FROM_NAME') ?: ($_ENV['SMTP_FROM_NAME'] ?? 'BloodSync Emergency Network');

    // 1. Direct Authenticated SMTP if host is provided
    if (!empty($smtpHost)) {
        $timeout = 15;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $protocol = ($smtpSecure === 'ssl' || $smtpPort == 465) ? 'ssl://' : '';
        $socket = @stream_socket_client("{$protocol}{$smtpHost}:{$smtpPort}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
        
        if ($socket) {
            $read = function() use ($socket) {
                $data = '';
                while ($line = fgets($socket, 515)) {
                    $data .= $line;
                    if (substr($line, 3, 1) == ' ') break;
                }
                return $data;
            };
            $write = function($cmd) use ($socket) {
                fputs($socket, $cmd . "\r\n");
            };

            $greeting = $read();
            if (substr($greeting, 0, 3) == '220') {
                $write("EHLO " . (gethostname() ?: 'localhost'));
                $read();

                if (($smtpSecure === 'tls' || $smtpPort == 587) && $protocol === '') {
                    $write("STARTTLS");
                    $starttls = $read();
                    if (substr($starttls, 0, 3) == '220') {
                        if (stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                            $write("EHLO " . (gethostname() ?: 'localhost'));
                            $read();
                        }
                    }
                }

                $authenticated = true;
                if (!empty($smtpUser) && !empty($smtpPass)) {
                    $write("AUTH LOGIN");
                    $authRes = $read();
                    if (substr($authRes, 0, 3) == '334') {
                        $write(base64_encode($smtpUser));
                        $uRes = $read();
                        if (substr($uRes, 0, 3) == '334') {
                            $write(base64_encode($smtpPass));
                            $pRes = $read();
                            if (substr($pRes, 0, 3) != '235') {
                                $authenticated = false;
                            }
                        } else {
                            $authenticated = false;
                        }
                    } else {
                        $authenticated = false;
                    }
                }

                if ($authenticated) {
                    $write("MAIL FROM: <{$fromEmail}>");
                    $mailFromRes = $read();
                    if (substr($mailFromRes, 0, 3) == '250') {
                        $write("RCPT TO: <{$to}>");
                        $rcptRes = $read();
                        if (substr($rcptRes, 0, 3) == '250') {
                            $write("DATA");
                            $dataRes = $read();
                            if (substr($dataRes, 0, 3) == '354') {
                                $headers = "MIME-Version: 1.0\r\n";
                                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                                $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
                                $headers .= "To: <{$to}>\r\n";
                                $headers .= "Date: " . date('r') . "\r\n";
                                $headers .= "Subject: {$subject}\r\n";
                                if (!empty($replyTo)) {
                                    $headers .= "Reply-To: <{$replyTo}>\r\n";
                                }
                                $headers .= "X-Mailer: BloodSync-SMTP/1.0\r\n";

                                $write($headers . "\r\n" . $htmlBody . "\r\n.");
                                $sendRes = $read();
                                $write("QUIT");
                                fclose($socket);

                                if (substr($sendRes, 0, 3) == '250') {
                                    return ['status' => 'sent', 'gateway' => 'smtp', 'details' => 'Accepted by SMTP server for delivery'];
                                }
                            }
                        }
                    }
                }
            }
            if (is_resource($socket)) {
                fclose($socket);
            }
        }
    }

    // 2. Standard PHP mail() fallback
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
    if (!empty($replyTo)) {
        $headers .= "Reply-To: <{$replyTo}>\r\n";
    }
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    $mailSent = @mail($to, $subject, $htmlBody, $headers);
    if ($mailSent) {
        return ['status' => 'sent', 'gateway' => 'php_mail', 'details' => 'Accepted by local mail agent'];
    }

    // 3. Clean logging fallback (Local simulation)
    return [
        'status' => 'logged',
        'gateway' => 'local_simulation',
        'details' => 'No active SMTP credentials in .env. Notification logged to database audit trail.'
    ];
}
?>
