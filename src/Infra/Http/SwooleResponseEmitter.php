<?php

declare(strict_types=1);

namespace App\Infra\Http;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

final class SwooleResponseEmitter
{
    public static function emit(Response $swooleResponse, ResponseInterface $psrResponse): void
    {
        $swooleResponse->status($psrResponse->getStatusCode());

        foreach ($psrResponse->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }

        $body = $psrResponse->getBody();
        $payload = '';

        if ($body->getSize() > 0) {
            if ($body->isSeekable()) {
                $body->rewind();
            }

            $content = (string)$body->getContents();

            $contentType = $psrResponse->getHeaderLine('Content-Type');
            if (str_contains($contentType, 'application/json')) {
                try {
                    $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                    $payload = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    $payload = $content;
                }
            } else {
                $payload = $content;
            }
        }

        $swooleResponse->end($payload);
    }
}
