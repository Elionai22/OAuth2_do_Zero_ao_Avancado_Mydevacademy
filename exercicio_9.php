<?php
header("Content-Type: application/json");

/* ARQUIVO 1 DE utils.php */
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function generateCodeVerifier() {
    return base64UrlEncode(random_bytes(32));
}

function generateCodeChallenge($verifier) {
    return base64UrlEncode(hash('sha256', $verifier, true));
}


/* ARQUIVO 2 DE index.php */
session_start();
require 'utils.php';

$client_id = 'SEU_CLIENT_ID';
$redirect_uri = 'http://localhost:8000/callback.php';
$auth_url = 'https://SEU_PROVIDER/oauth/authorize';

// 1. Gerar PKCE
$code_verifier = generateCodeVerifier();
$code_challenge = generateCodeChallenge($code_verifier);

// salvar na sessão (IMPORTANTÍSSIMO)
$_SESSION['code_verifier'] = $code_verifier;

// 2. Montar URL de autorização
$params = http_build_query([
    'response_type' => 'code',
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'code_challenge' => $code_challenge,
    'code_challenge_method' => 'S256',
    'scope' => 'openid profile email'
]);

// 3. Redirecionar usuário
header("Location: $auth_url?$params");
exit;


/* ARQUIVO 3 DE callback.php */
<?php
session_start();

$token_url = 'https://SEU_PROVIDER/oauth/token';
$client_id = 'SEU_CLIENT_ID';
$redirect_uri = 'http://localhost:8000/callback.php';

// pega o authorization code
$authorization_code = $_GET['code'] ?? null;

if (!$authorization_code) {
    die("Erro: code não recebido");
}

// pega o verifier salvo
$code_verifier = $_SESSION['code_verifier'] ?? null;

if (!$code_verifier) {
    die("Erro: code_verifier não encontrado");
}

// 4. Trocar code por token
$data = [
    'grant_type' => 'authorization_code',
    'client_id' => $client_id,
    'code' => $authorization_code,
    'redirect_uri' => $redirect_uri,
    'code_verifier' => $code_verifier
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($token_url, false, $context);

echo "<h2>Resposta do Token:</h2>";
echo "<pre>";
print_r(json_decode($response, true));
echo "</pre>";
?>