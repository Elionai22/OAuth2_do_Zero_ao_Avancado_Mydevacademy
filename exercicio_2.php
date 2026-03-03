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

function loginAndAccess($userId, $token, $resource, $users)
{
    // 🔐 1 - Autenticação (validar token)

    if (!isset($users[$userId])) {
        return [
            "status" => "error",
            "type" => "not_authenticated",
            "message" => "Usuário não autenticado."
        ];
    }

    $user = $users[$userId];

    if ($user["token"] !== $token) {
        return [
            "status" => "error",
            "type" => "not_authenticated",
            "message" => "Token inválido. Usuário não autenticado."
        ];
    }

    // 🛡️ 2 - Autorização (verificar permissão)

    if (!in_array($resource, $user["permissions"])) {
        return [
            "status" => "error",
            "type" => "not_authorized",
            "message" => "Usuário autenticado, mas não autorizado para acessar este recurso."
        ];
    }

    // ✅ 3 - Autenticado e autorizado
    return [
        "status" => "success",
        "type" => "authorized",
        "message" => "Usuário autenticado e autorizado."
    ];
}


// 🔎 Teste da função
$result = loginAndAccess(
    2,              // userId
    "def456",       // token
    "dashboard",    // recurso solicitado
    $users
);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

?>