<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application;
use RuntimeException;

class EmailService
{
    private array $config;
    private string $from;
    private string $fromName;

    public function __construct(Application $app)
    {
        $this->config = config('mail', []);
        $this->from = $this->config['from']['address'] ?? 'noreply@criminals.game';
        $this->fromName = $this->config['from']['name'] ?? 'Criminals Game';
    }

    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        $headers = $this->buildHeaders($options);
        $body = $this->buildBody($body, $options);

        return mail($to, $subject, $body, $headers);
    }

    public function sendTemplate(string $to, string $template, array $data = [], array $options = []): bool
    {
        $templatePath = app()->resourcePath("views/emails/{$template}.php");

        if (!file_exists($templatePath)) {
            throw new RuntimeException("Email template {$template} not found");
        }

        ob_start();
        extract($data);
        include $templatePath;
        $body = ob_get_clean();

        return $this->send($to, $options['subject'] ?? 'Criminals Game', $body, $options);
    }

    public function sendActivation(string $to, string $username, string $activationLink): bool
    {
        return $this->sendTemplate($to, 'activation', [
            'username' => $username,
            'activationLink' => $activationLink,
        ], [
            'subject' => 'Activate your Criminals Game account',
        ]);
    }

    public function sendPasswordReset(string $to, string $username, string $resetLink): bool
    {
        return $this->sendTemplate($to, 'password-reset', [
            'username' => $username,
            'resetLink' => $resetLink,
        ], [
            'subject' => 'Reset your Criminals Game password',
        ]);
    }

    public function sendNotification(string $to, string $username, string $message): bool
    {
        return $this->sendTemplate($to, 'notification', [
            'username' => $username,
            'message' => $message,
        ], [
            'subject' => 'Criminals Game Notification',
        ]);
    }

    private function buildHeaders(array $options): string
    {
        $headers = [];

        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $headers[] = sprintf('From: %s <%s>', $this->fromName, $this->from);

        if (isset($options['replyTo'])) {
            $headers[] = 'Reply-To: ' . $options['replyTo'];
        }

        if (isset($options['cc'])) {
            $headers[] = 'Cc: ' . $options['cc'];
        }

        if (isset($options['bcc'])) {
            $headers[] = 'Bcc: ' . $options['bcc'];
        }

        return implode("\r\n", $headers);
    }

    private function buildBody(string $body, array $options): string
    {
        if (!isset($options['isHtml']) || $options['isHtml'] === true) {
            $template = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
        .content { background: white; padding: 20px; border: 1px solid #ddd; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        a { color: #3498db; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Criminals Game</h1>
        </div>
        <div class="content">
            %s
        </div>
        <div class="footer">
            <p>&copy; %d Criminals Game. All rights reserved.</p>
            <p>This is an automated message, please do not reply.</p>
        </div>
    </div>
</body>
</html>';

            return sprintf($template, $body, date('Y'));
        }

        return $body;
    }
}