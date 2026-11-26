<?php
/* Validates recipients and sends transactional email through the Brevo SMTP API endpoint using the credentials in the local application configuration. */

require_once __DIR__ . '/db.php';

// Send an HTML email through Brevo and return a consistent success/error result.
function brevo_send_email(array $recipients, string $subject, string $html): array
{
    $config = app_config();
    if (empty($config['brevo_api_key']) || empty($config['brevo_sender_email'])) {
        return ['success' => false, 'message' => 'Brevo API key or sender email is not configured.'];
    }

    $to = [];
    
    foreach ($recipients as $email => $name) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) $to[] = ['email' => $email, 'name' => $name];
    }
    
    if (!$to) return ['success' => false, 'message' => 'No valid recipient email address is available.'];
    
    // Build the payload using the configured sender and only validated recipient addresses.
    $payload = json_encode([
        'sender' => ['name' => $config['brevo_sender_name'] ?? "AZ Preston Hall", 'email' => $config['brevo_sender_email']],
        'to' => $to,
        'subject' => $subject,
        'htmlContent' => $html,
    ]);

    $headers = "Content-Type: application/json\r\n" . "api-key: " . $config['brevo_api_key'] . "\r\n";
    $context = stream_context_create(['http' => ['method' => 'POST', 'header' => $headers, 'content' => $payload, 'timeout' => 20, 'ignore_errors' => true]]);
    
    // Submit the email request and inspect the HTTP response code below.
    $response = @file_get_contents('https://api.brevo.com/v3/smtp/email', false, $context);
    $status = 0;
    foreach ($http_response_header ?? [] as $header) if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $m)) $status = (int)$m[1];
    if ($response !== false && $status >= 200 && $status < 300) return ['success' => true, 'message' => 'Email accepted by Brevo.'];
    return ['success' => false, 'message' => 'Brevo request failed. HTTP ' . $status . ': ' . substr((string)$response, 0, 300)];
}
