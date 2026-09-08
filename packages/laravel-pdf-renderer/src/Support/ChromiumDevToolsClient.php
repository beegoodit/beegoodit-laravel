<?php

declare(strict_types=1);

namespace BeegoodIT\Pdf\Support;

use RuntimeException;

/** Minimal blocking Chrome DevTools Protocol client over WebSocket. */
final class ChromiumDevToolsClient
{
    /** @var resource */
    private $socket;

    private int $nextId = 0;

    private string $readBuffer = '';

    /**
     * @param  resource  $socket
     */
    private function __construct($socket)
    {
        $this->socket = $socket;
    }

    public static function connect(string $wsUrl): self
    {
        $parts = parse_url($wsUrl);
        if ($parts === false || ! isset($parts['host'], $parts['port'], $parts['path'])) {
            throw new RuntimeException('Invalid Chromium DevTools WebSocket URL.');
        }

        $host = $parts['host'];
        $port = (int) $parts['port'];
        $path = $parts['path'].(isset($parts['query']) ? '?'.$parts['query'] : '');
        $key = base64_encode(random_bytes(16));

        $socket = @stream_socket_client(
            "tcp://{$host}:{$port}",
            $errno,
            $errstr,
            5,
        );
        if ($socket === false) {
            throw new RuntimeException("Unable to open Chromium DevTools socket: {$errstr} ({$errno})");
        }

        stream_set_timeout($socket, 60);

        $handshake = "GET {$path} HTTP/1.1\r\n"
            ."Host: {$host}:{$port}\r\n"
            ."Upgrade: websocket\r\n"
            ."Connection: Upgrade\r\n"
            ."Sec-WebSocket-Key: {$key}\r\n"
            ."Sec-WebSocket-Version: 13\r\n"
            ."\r\n";

        fwrite($socket, $handshake);
        $response = '';
        while (! str_contains($response, "\r\n\r\n")) {
            $chunk = fread($socket, 1024);
            if ($chunk === false || $chunk === '') {
                fclose($socket);
                throw new RuntimeException('Chromium DevTools WebSocket handshake failed.');
            }
            $response .= $chunk;
        }

        if (! str_contains($response, '101')) {
            fclose($socket);
            throw new RuntimeException('Chromium DevTools WebSocket upgrade rejected.');
        }

        $client = new self($socket);
        $client->readBuffer = substr($response, (int) strpos($response, "\r\n\r\n") + 4);

        return $client;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function send(string $method, array $params = []): array
    {
        $id = ++$this->nextId;
        $this->writeText(json_encode([
            'id' => $id,
            'method' => $method,
            'params' => (object) $params,
        ], JSON_THROW_ON_ERROR));

        while (true) {
            $message = json_decode($this->readMessage(), true, 512, JSON_THROW_ON_ERROR);
            if (($message['id'] ?? null) !== $id) {
                continue;
            }

            if (isset($message['error'])) {
                $error = $message['error'];
                $text = is_array($error)
                    ? (string) ($error['message'] ?? json_encode($error))
                    : (string) $error;
                throw new RuntimeException('Chromium DevTools error: '.$text);
            }

            /** @var array<string, mixed> $result */
            $result = $message['result'] ?? [];

            return $result;
        }
    }

    public function close(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }

    private function writeText(string $payload): void
    {
        $length = strlen($payload);
        $mask = random_bytes(4);
        $header = chr(0x81);

        if ($length <= 125) {
            $header .= chr(0x80 | $length);
        } elseif ($length <= 65535) {
            $header .= chr(0x80 | 126).pack('n', $length);
        } else {
            $header .= chr(0x80 | 127).pack('J', $length);
        }

        $masked = $payload;
        for ($i = 0; $i < $length; $i++) {
            $masked[$i] = $payload[$i] ^ $mask[$i % 4];
        }

        $frame = $header.$mask.$masked;
        $offset = 0;
        $total = strlen($frame);

        while ($offset < $total) {
            $written = fwrite($this->socket, substr($frame, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('Chromium DevTools WebSocket write failed.');
            }
            $offset += $written;
        }
    }

    private function readMessage(): string
    {
        $message = '';

        while (true) {
            [$fin, $opcode, $payload] = $this->readFrame();

            if ($opcode === 0x8) {
                throw new RuntimeException('Chromium DevTools WebSocket closed.');
            }

            if ($opcode === 0x9) {
                $this->writeControl(0xA, $payload);

                continue;
            }

            if ($opcode === 0xA) {
                continue;
            }

            if ($opcode === 0x1 || $opcode === 0x2) {
                $message = $payload;
            } elseif ($opcode === 0x0) {
                $message .= $payload;
            } else {
                throw new RuntimeException('Unexpected Chromium DevTools WebSocket frame.');
            }

            if ($fin) {
                return $message;
            }
        }
    }

    /**
     * @return array{0: bool, 1: int, 2: string}
     */
    private function readFrame(): array
    {
        $header = $this->readBytes(2);
        $byte1 = ord($header[0]);
        $byte2 = ord($header[1]);
        $fin = ($byte1 & 0x80) !== 0;
        $opcode = $byte1 & 0x0F;
        $masked = ($byte2 & 0x80) !== 0;
        $length = $byte2 & 0x7F;

        if ($length === 126) {
            $length = unpack('n', $this->readBytes(2))[1];
        } elseif ($length === 127) {
            $length = unpack('J', $this->readBytes(8))[1];
        }

        $mask = $masked ? $this->readBytes(4) : '';
        $payload = $this->readBytes($length);

        if ($masked) {
            for ($i = 0; $i < $length; $i++) {
                $payload[$i] = $payload[$i] ^ $mask[$i % 4];
            }
        }

        return [$fin, $opcode, $payload];
    }

    private function writeControl(int $opcode, string $payload): void
    {
        $length = strlen($payload);
        if ($length > 125) {
            throw new RuntimeException('Chromium DevTools control frame too large.');
        }

        $mask = random_bytes(4);
        $frame = chr(0x80 | ($opcode & 0x0F)).chr(0x80 | $length).$mask;
        for ($i = 0; $i < $length; $i++) {
            $frame .= $payload[$i] ^ $mask[$i % 4];
        }

        fwrite($this->socket, $frame);
    }

    private function readBytes(int $length): string
    {
        while (strlen($this->readBuffer) < $length) {
            $chunk = fread($this->socket, max(8192, $length - strlen($this->readBuffer)));
            if ($chunk === false || $chunk === '') {
                $meta = stream_get_meta_data($this->socket);
                if ($meta['timed_out'] ?? false) {
                    throw new RuntimeException('Chromium DevTools WebSocket read timed out.');
                }
                throw new RuntimeException('Chromium DevTools WebSocket read failed.');
            }
            $this->readBuffer .= $chunk;
        }

        $data = substr($this->readBuffer, 0, $length);
        $this->readBuffer = substr($this->readBuffer, $length);

        return $data;
    }
}
