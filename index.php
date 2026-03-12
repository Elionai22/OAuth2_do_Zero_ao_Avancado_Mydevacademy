<?php
header("Content-Type: application/json");

// Pegar método e rota
$method = $_SERVER['REQUEST_METHOD'];
$route = $_SERVER['REQUEST_URI'];

// Pegar header Authorization
$headers = getallheaders();

$authHeader = $headers['Authorization'] ?? null;

$token = null;
$valid = false;

// Verificar se header existe
if ($authHeader) {

    // Verificar se começa com Bearer
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];

        // Validação simples do token (apenas exemplo)
        if (strlen($token) >= 10) {
            $valid = true;
        }
    }
}

// Registrar log
$log = date("Y-m-d H:i:s") .
       " | METHOD: $method" .
       " | ROUTE: $route" .
       " | TOKEN: " . ($token ?? "none") . PHP_EOL;

file_put_contents("logs.txt", $log, FILE_APPEND);

// Resposta JSON
if ($valid) {
    echo json_encode([
        "status" => "success",
        "message" => "Authentication accepted",
        "token" => $token
    ]);
} else {
    http_response_code(401);

    echo json_encode([
        "status" => "error",
        "message" => "Authentication rejected"
    ]);
}

?>