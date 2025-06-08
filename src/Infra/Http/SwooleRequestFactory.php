<?php

declare(strict_types=1);

namespace App\Infra\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request;

final class SwooleRequestFactory
{
    public static function fromSwoole(Request $swooleRequest): ServerRequestInterface
    {
        $psr17Factory = new Psr17Factory();

        $method = $swooleRequest->server['request_method'] ?? 'GET';
        $uri = ($swooleRequest->server['request_scheme'] ?? 'http') . '://' .
               ($swooleRequest->header['host'] ?? 'localhost') .
               ($swooleRequest->server['request_uri'] ?? '/');

        $serverRequest = new ServerRequest(
            $method,
            $uri,
            self::transformHeaders($swooleRequest->header ?? []),
            $swooleRequest->rawContent() ?: null,
            $swooleRequest->server['server_protocol'] ?? '1.1',
            self::transformServerParams($swooleRequest->server ?? [])
        );

        if (!empty($swooleRequest->cookie)) {
            $serverRequest = $serverRequest->withCookieParams($swooleRequest->cookie);
        }

        if (!empty($swooleRequest->get)) {
            $serverRequest = $serverRequest->withQueryParams($swooleRequest->get);
        }

        if (!empty($swooleRequest->post)) {
            $serverRequest = $serverRequest->withParsedBody($swooleRequest->post);
        }

        if (!empty($swooleRequest->files)) {
            $serverRequest = $serverRequest->withUploadedFiles(
                self::transformUploadedFiles($swooleRequest->files, $psr17Factory)
            );
        }

        return $serverRequest;
    }

    private static function transformHeaders(array $headers): array
    {
        $transformed = [];
        foreach ($headers as $key => $value) {
            $transformed[ucwords($key, '-')] = $value;
        }

        return $transformed;
    }

    private static function transformServerParams(array $server): array
    {
        $transformed = [];
        foreach ($server as $key => $value) {
            $transformed[strtoupper($key)] = $value;
        }

        if (!isset($transformed['REQUEST_TIME'])) {
            $transformed['REQUEST_TIME'] = time();
        }

        if (!isset($transformed['REQUEST_TIME_FLOAT'])) {
            $transformed['REQUEST_TIME_FLOAT'] = microtime(true);
        }

        return $transformed;
    }

    private static function transformUploadedFiles(array $files, Psr17Factory $factory): array
    {
        $transformed = [];

        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $transformed[$key] = [];
                foreach ($file['name'] as $i => $name) {
                    $transformed[$key][] = $factory->createUploadedFile(
                        $factory->createStreamFromFile($file['tmp_name'][$i]),
                        $file['size'][$i],
                        $file['error'][$i],
                        $name,
                        $file['type'][$i]
                    );
                }
            } else {
                $transformed[$key] = $factory->createUploadedFile(
                    $factory->createStreamFromFile($file['tmp_name']),
                    $file['size'],
                    $file['error'],
                    $file['name'],
                    $file['type']
                );
            }
        }

        return $transformed;
    }
}
