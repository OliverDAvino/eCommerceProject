<?php

namespace App\Services;

class Mailer
{
    private const API_URL = 'https://api.brevo.com/v3/smtp/email';

    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $body
    ): void {
        $apiKey = trim($_ENV['BREVO_API_KEY'] ?? '');

        if ($apiKey === '') {
            throw new \RuntimeException('Email service is not configured.');
        }

        error_log('Brevo API key prefix: ' . substr($apiKey, 0, 12));

        $payload = json_encode([
            'sender'   => [
                'name'  => $_ENV['MAIL_FROM_NAME'] ?? 'PetConnect',
                'email' => $_ENV['MAIL_FROM'] ?? '',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject'     => $subject,
            'textContent' => $body,
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'api-key: ' . $apiKey,
            ],
        ]);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr !== '' || $raw === false) {
            error_log('Mailer curl error: ' . $curlErr);
            throw new \RuntimeException('Email could not be sent.');
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log('Mailer API error ' . $httpCode . ': ' . $raw);
            throw new \RuntimeException('Email could not be sent.');
        }
    }
}
