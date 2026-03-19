<?php
header("Content-Type: application/json");

declare(strict_types=1);

function base64UrlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

$secret = getenv('JWT_SECRET') ?: 'minha_chave_secreta';

$header = [
    'alg' => 'HS256',
    'typ' => 'JWT',
];

$payload = [
    'sub' => '123',
    'name' => 'Robinho',
    'exp' => time() + 3600,
];

$encodedHeader = base64UrlEncode(json_encode($header, JSON_UNESCAPED_UNICODE));
$encodedPayload = base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));

$signature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $secret, true);
$encodedSignature = base64UrlEncode($signature);

$jwt = $encodedHeader . '.' . $encodedPayload . '.' . $encodedSignature;

echo $jwt . PHP_EOL;
?>