<?php
header('Content-Type: application/json; charset=utf-8');

// Simulação de base de usuários
$users = [
    1 => [
        "name" => "João",
        "token" => "abc123",
        "permissions" => ["dashboard", "profile"]
    ],
    2 => [
        "name" => "Maria",
        "token" => "def456",
        "permissions" => ["profile"]
    ]
];

function handleRequest($request, $users) {

    // Verifica se userId e token foram enviados
    if (!isset($request['userId']) || !isset($request['token'])) {
        return [
            "status" => "error",
            "type" => "authentication_error",
            "message" => "Usuário não identificado."
        ];
    }

    $userId = $request['userId'];
    $token = $request['token'];
    $resource = $request['resource'];

    // Verifica se usuário existe
    if (!isset($users[$userId])) {
        return [
            "status" => "error",
            "type" => "authentication_error",
            "message" => "Usuário não encontrado."
        ];
    }

    $user = $users[$userId];

    // Verifica se token é válido
    if ($user['token'] !== $token) {
        return [
            "status" => "error",
            "type" => "authentication_error",
            "message" => "Token inválido."
        ];
    }

    // Verifica permissão de acesso ao recurso
    if (!in_array($resource, $user['permissions'])) {
        return [
            "status" => "error",
            "type" => "authorization_error",
            "message" => "Usuário não tem permissão para acessar este recurso."
        ];
    }

    // Se passou por tudo
    return [
        "status" => "success",
        "message" => "Acesso permitido ao recurso."
    ];
}


// 🔎 Simulação de requisição
$request = [
    "userId" => 2,
    "token" => "def456",
    "resource" => "dashboard"
];

$result = handleRequest($request, $users);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>