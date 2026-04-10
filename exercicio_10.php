<?php
header("Content-Type: application/json");

/* ARQUIVO 1 DE home.php para ficar seguro e chamar o callback.php */
session_start();

if (!isset($_SESSION['access_token'])) {
    header("Location: index.php");
    exit;
}

echo "<h1>Usuário autenticado ✅</h1>";
echo "<p>Access Token salvo na sessão.</p>";


/* ARQUIVO 2 DE callback.php */
session_start();

$token_url = 'https://SEU_PROVIDER/oauth/token';
$client_id = 'SEU_CLIENT_ID';
$redirect_uri = 'http://localhost:8000/callback.php';

$authorization_code = $_GET['code'] ?? null;

if (!$authorization_code) {
    die("Erro: authorization_code não recebido");
}

$code_verifier = $_SESSION['code_verifier'] ?? null;

if (!$code_verifier) {
    die("Erro: code_verifier não encontrado");
}

// 🔁 Troca o code por token usando cURL (melhor que file_get_contents)
$ch = curl_init($token_url);

$postFields = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $client_id,
    'code' => $authorization_code,
    'redirect_uri' => $redirect_uri,
    'code_verifier' => $code_verifier
]);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded'
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    die('Erro no cURL: ' . curl_error($ch));
}

curl_close($ch);

$data = json_decode($response, true);

// ❌ Tratamento de erro do provider
if ($httpCode !== 200 || isset($data['error'])) {
    echo "<h2>Erro ao obter token:</h2>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    exit;
}

// ✅ Salvar tokens na sessão
$_SESSION['access_token'] = $data['access_token'] ?? null;
$_SESSION['id_token'] = $data['id_token'] ?? null;

// ✅ Exibir resposta
echo "<h2>Tokens recebidos com sucesso 🎉</h2>";

echo "<h3>Access Token:</h3>";
echo "<pre>" . htmlspecialchars($data['access_token']) . "</pre>";

if (isset($data['id_token'])) {
    echo "<h3>ID Token:</h3>";
    echo "<pre>" . htmlspecialchars($data['id_token']) . "</pre>";
}
?>