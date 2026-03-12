<?php

// Solicitar token
echo "Digite o Access Token: ";
$token = trim(fgets(STDIN));

// Solicitar URL
echo "Digite a URL da requisição: ";
$url = trim(fgets(STDIN));

// Inicializar CURL
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token",
        "Accept: application/json"
    ]
]);

$response = curl_exec($ch);

// Separar status
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Separar headers e body
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

// Exibir resposta
echo "\n===== STATUS =====\n";
echo $status . "\n";

echo "\n===== HEADERS =====\n";
echo $headers;

echo "\n===== BODY =====\n";
echo $body;
