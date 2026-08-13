<?php

function apiRequest(
    string $method,
    string $url,
    ?array $data = null
): array {

    $apiKey = getenv('API_INTERNAL_KEY');

    if (!$apiKey) {
        return [
            'success' => false,
            'status' => 500,
            'data' => null,
            'error' => 'API_INTERNAL_KEY não configurada'
        ];
    }

    $headers = [
        'Content-Type: application/json',
        'X-API-Key: ' . $apiKey
    ];

    $options = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers) . "\r\n",
            'ignore_errors' => true
        ]
    ];

    if ($data !== null) {
        $options['http']['content'] = json_encode($data);
    }

    $context = stream_context_create($options);

    $response = @file_get_contents(
        $url,
        false,
        $context
    );

    $status = 0;

    if (isset($http_response_header)) {

        foreach ($http_response_header as $header) {

            if (preg_match(
                '#HTTP/\S+\s+(\d+)#',
                $header,
                $matches
            )) {
                $status = (int) $matches[1];
                break;
            }
        }
    }

    if ($response === false) {

        return [
            'success' => false,
            'status' => $status,
            'data' => null,
            'error' => 'Não foi possível conectar à API'
        ];
    }

    $decoded = json_decode($response, true);

    return [
        'success' => $status >= 200 && $status < 300,
        'status' => $status,
        'data' => $decoded,
        'error' => null
    ];
}