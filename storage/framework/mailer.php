<?php
declare(strict_types=1);

function welga_mail_header_value(string $value): string
{
    return trim(str_replace(["\r", "\n"], '', $value));
}

function welga_mail_encode_header(string $value): string
{
    $value = welga_mail_header_value($value);
    if ($value === '') {
        return '';
    }
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

function welga_smtp_read($socket): string
{
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') {
            break;
        }
    }
    return $response;
}

function welga_smtp_expect($socket, array $codes): string
{
    $response = welga_smtp_read($socket);
    $code = (int)substr($response, 0, 3);
    if (!in_array($code, $codes, true)) {
        throw new RuntimeException('SMTP response ' . $code . ': ' . trim($response));
    }
    return $response;
}

function welga_smtp_command($socket, string $command, array $codes): string
{
    if (fwrite($socket, $command . "\r\n") === false) {
        throw new RuntimeException('SMTP write failed.');
    }
    return welga_smtp_expect($socket, $codes);
}

function welga_send_text_mail(string $subject, string $body, ?string $replyTo = null, ?string $replyName = null): bool
{
    $config = welga_config('mail');
    if (empty($config['enabled'])) {
        welga_log('mail', 'SMTP disabled; message not sent.');
        return false;
    }

    $host = trim((string)($config['host'] ?? ''));
    $port = (int)($config['port'] ?? 587);
    $encryption = strtolower(trim((string)($config['encryption'] ?? 'tls')));
    $username = trim((string)($config['username'] ?? ''));
    $password = (string)($config['password'] ?? '');
    $fromEmail = filter_var((string)($config['from_email'] ?? ''), FILTER_VALIDATE_EMAIL) ?: '';
    $fromName = welga_mail_header_value((string)($config['from_name'] ?? 'WELGA Website'));
    $recipient = filter_var((string)($config['recipient'] ?? ''), FILTER_VALIDATE_EMAIL) ?: '';
    $timeout = max(3, min(30, (int)($config['timeout'] ?? 12)));

    if ($host === '' || $port < 1 || $fromEmail === '' || $recipient === '') {
        welga_log('mail', 'SMTP configuration incomplete.');
        return false;
    }

    $transportHost = $encryption === 'ssl' ? 'ssl://' . $host : $host;
    $errno = 0;
    $errstr = '';
    $socket = @stream_socket_client(
        $transportHost . ':' . $port,
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT
    );

    if (!is_resource($socket)) {
        welga_log('mail', 'SMTP connection failed.', ['error' => $errstr, 'code' => $errno]);
        return false;
    }

    stream_set_timeout($socket, $timeout);

    try {
        welga_smtp_expect($socket, [220]);
        $hostname = preg_replace('/[^a-z0-9.-]/i', '', (string)($_SERVER['SERVER_NAME'] ?? 'welga.com')) ?: 'welga.com';
        welga_smtp_command($socket, 'EHLO ' . $hostname, [250]);

        if ($encryption === 'tls') {
            welga_smtp_command($socket, 'STARTTLS', [220]);
            $crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($crypto !== true) {
                throw new RuntimeException('Unable to enable SMTP TLS.');
            }
            welga_smtp_command($socket, 'EHLO ' . $hostname, [250]);
        }

        if ($username !== '') {
            welga_smtp_command($socket, 'AUTH LOGIN', [334]);
            welga_smtp_command($socket, base64_encode($username), [334]);
            welga_smtp_command($socket, base64_encode($password), [235]);
        }

        welga_smtp_command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
        welga_smtp_command($socket, 'RCPT TO:<' . $recipient . '>', [250, 251]);
        welga_smtp_command($socket, 'DATA', [354]);

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'From: ' . welga_mail_encode_header($fromName) . ' <' . $fromEmail . '>',
            'To: <' . $recipient . '>',
            'Subject: ' . welga_mail_encode_header($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];

        $validReply = $replyTo !== null ? filter_var($replyTo, FILTER_VALIDATE_EMAIL) : false;
        if ($validReply) {
            $safeReplyName = welga_mail_header_value((string)($replyName ?? ''));
            $headers[] = 'Reply-To: ' . ($safeReplyName !== '' ? welga_mail_encode_header($safeReplyName) . ' ' : '') . '<' . $validReply . '>';
        }

        $messageBody = chunk_split(base64_encode(str_replace(["\r\n", "\r"], "\n", $body)), 76, "\r\n");
        $payload = implode("\r\n", $headers) . "\r\n\r\n" . $messageBody;
        // SMTP dot-stuffing.
        $payload = preg_replace('/(^|\r\n)\./', '$1..', $payload) ?? $payload;
        if (fwrite($socket, $payload . "\r\n.\r\n") === false) {
            throw new RuntimeException('SMTP message write failed.');
        }
        welga_smtp_expect($socket, [250]);
        welga_smtp_command($socket, 'QUIT', [221]);
        fclose($socket);
        return true;
    } catch (Throwable $e) {
        welga_log('mail', 'SMTP delivery failed.', ['error' => $e->getMessage()]);
        if (is_resource($socket)) {
            @fwrite($socket, "QUIT\r\n");
            @fclose($socket);
        }
        return false;
    }
}
