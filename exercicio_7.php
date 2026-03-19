<?php
header("Content-Type: application/json");

declare(strict_types=1);

/**
 * Decodifica Base64Url para string normal.
 */
function base64UrlDecode(string $data): string
{
    $remainder = strlen($data) % 4;
    if ($remainder > 0) {
        $data .= str_repeat('=', 4 - $remainder);
    }

    $data = strtr($data, '-_', '+/');

    $decoded = base64_decode($data, true);

    if ($decoded === false) {
        throw new InvalidArgumentException('Falha ao decodificar Base64Url.');
    }

    return $decoded;
}

/**
 * Decodifica um JWT sem validar assinatura.
 */
function decodeJwt(string $jwt): array
{
    $parts = explode('.', trim($jwt));

    if (count($parts) !== 3) {
        throw new InvalidArgumentException('Token JWT malformado: deve conter 3 partes separadas por ponto.');
    }

    [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

    if ($encodedHeader === '' || $encodedPayload === '' || $encodedSignature === '') {
        throw new InvalidArgumentException('Token JWT malformado: uma ou mais partes estão vazias.');
    }

    $headerJson = base64UrlDecode($encodedHeader);
    $payloadJson = base64UrlDecode($encodedPayload);

    $header = json_decode($headerJson, true);
    $payload = json_decode($payloadJson, true);

    if (!is_array($header)) {
        throw new InvalidArgumentException('Header inválido: JSON malformado.');
    }

    if (!is_array($payload)) {
        throw new InvalidArgumentException('Payload inválido: JSON malformado.');
    }

    return [
        'header' => $header,
        'payload' => $payload,
    ];
}

function formatTimestamp(mixed $timestamp): string
{
    if (!is_numeric($timestamp)) {
        return 'Campo exp ausente ou inválido';
    }

    return date('Y-m-d H:i:s', (int) $timestamp) . ' UTC';
}

$jwt = $argv[1] ?? null;

if ($jwt === null) {
    echo "Uso:\n";
    echo "php index.php <seu-jwt>\n";
    exit(1);
}

try {
    $decoded = decodeJwt($jwt);

    $header = $decoded['header'];
    $payload = $decoded['payload'];

    $alg = $header['alg'] ?? 'não encontrado';
    $typ = $header['typ'] ?? 'não encontrado';
    $exp = $payload['exp'] ?? null;

    echo "=== HEADER ===\n";
    echo json_encode($header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;

    echo "=== PAYLOAD ===\n";
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;

    echo "=== CAMPOS SOLICITADOS ===\n";
    echo "alg: {$alg}\n";
    echo "typ: {$typ}\n";
    echo "exp: " . ($exp ?? 'não encontrado') . "\n";
    echo "exp formatado: " . formatTimestamp($exp) . "\n";

} catch (Throwable $e) {
    fwrite(STDERR, "Erro: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
?>