<?php

A seguir, um exemplo do fluxo Authorization Code simplificado.
GET /authorize?response_type=code&client_id=clienteX&redirect_uri=https://app.com/callback&scope=email

Usuário autentica-se e autoriza. O provedor então retorna:
https://app.com/callback?code=ABC123

A aplicação então troca o código por um token:
POST /token
Content-Type: application/x-www-form-urlencoded
grant_type=authorization_code&code=ABC123&redirect_uri=https://app.com/callback&client_id=clienteX&client_secret=segredo


Resposta esperada:
{
  "access_token": "token123",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "refresh987"
}


/* ------------------------------------------------------------------------------------------------ */

Uma aplicação web deseja acessar a API de fotos de um usuário. Ela precisa da permissão "photos.read".

-- Passo 1: Redirecionamento
O cliente encaminha o usuário para o servidor de autorização:

GET https://auth.exemplo.com/authorize?
  response_type=code&
  client_id=abc123&
  redirect_uri=https://app.com/callback&
  scope=photos.read
  
-- Passo 2: Autenticação e Consentimento
O usuário faz login e aceita conceder o acesso.

-- Passo 3: Retorno do Código
O authorization server envia o código temporário:
https://app.com/callback?code=XYZ987

-- Passo 4: Troca do Código por Access Token
A aplicação cliente solicita o token:
POST https://auth.exemplo.com/token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code
code=XYZ987
redirect_uri=https://app.com/callback
client_id=abc123
client_secret=xyzsecret

-- Passo 5: Recebimento dos Tokens
A resposta inclui o access token e o refresh token:
{
  "access_token": "token123",
  "refresh_token": "refresh456",
  "expires_in": 3600,
  "token_type": "Bearer"
}

-- Passo 6: Acesso ao Resource Server
A aplicação acessa a API de fotos:
GET https://api.exemplo.com/photos
Authorization: Bearer token123


/* ------------------------------------------------------------------------------------------------ */

-- Estrutura de um JWT de Access Token
header.payload.signature
O payload pode conter:
`sub` (identificador do usuário)
`aud` (audiência)
`iss` (emissor)
`exp` (expiração)
`scope` (permissões)

Exemplo simplificado:
{
  "sub": "123456",
  "scope": "read:profile write:posts",
  "exp": 1712268800
}


/* ------------------------------------------------------------------------------------------------ */

-- Requisição de Access Token usando Authorization Code
POST /oauth/token
Content-Type: application/x-www-form-urlencoded
grant_type=authorization_code&code=abc123&redirect_uri=https://app.com/callback

Resposta:
{
  "access_token": "eyJhbGciOiJI...",
  "refresh_token": "def789",
  "token_type": "Bearer",
  "expires_in": 300
}

-- Renovação do Access Token usando Refresh Token
POST /oauth/token
Content-Type: application/x-www-form-urlencoded
grant_type=refresh_token&refresh_token=def789

Resposta:
{
  "access_token": "novoToken123",
  "refresh_token": "novoRefresh456",
  "expires_in": 300
}

-- Envio de Access Token para API
GET /api/user/profile
Authorization: Bearer eyJhbGciOiJI...


-- Após o processo de autorização, o Authorization Server irá emitir um token contendo esses escopos:
{
  "access_token": "ey...",
  "scope": "profile.read email.read",
  "token_type": "Bearer",
  "expires_in": 3600
}

-- Escopos Baseados em Recursos
Delimitam permissões por tipo de recurso.
Exemplos:
`users.read`
`users.write`
`orders.read`
`orders.update`

-- Escopos Baseados em Ações
Úteis quando se deseja reforçar limites muito específicos.
Exemplos:
`transactions.create`
`cart.add_item`
`cart.remove_item`

-- Escopos Baseados em Domínio ou Contexto de Negócio
Aplicação recomendada em sistemas grandes e distribuídos.
Exemplos:
`billing.invoices.manage`
`inventory.products.read`
`analytics.reports.generate`


/* ------------------------------------------------------------------------------------------------ */

-- Exemplo de payload de um token JWT:
{
  "sub": "user123",
  "scope": "read:orders write:orders",
  "iss": "https://auth.example.com/",
  "aud": "https://api.example.com/",
  "exp": 1711923600,
  "iat": 1711920000
}


/* ------------------------------------------------------------------------------------------------ */

Um Bearer Token é um tipo de credencial emitida por um Authorization Server no contexto do OAuth2. O nome "bearer" (portador) indica que qualquer entidade que possua o token tem o direito de usá-lo, sem a necessidade de outras provas adicionais. Assim, o nível de segurança depende fortemente de:
- Proteção durante o transporte (HTTPS obrigatório).
- Armazenamento seguro no cliente.
- Validação rigorosa no servidor.
Bearer Tokens podem ser opacos ou estruturados (como JWT), mas o método de envio pelo HTTP segue o mesmo padrão.


/* ------------------------------------------------------------------------------------------------ */

-- Estrutura Básica de Proteção de Endpoints
A implementação pode ser dividida em quatro etapas principais:

Receber o token
Validar o token
Extrair permissões (escopos ou claims)
Decidir se o acesso é permitido
Esses passos costumam ser aplicados por um middleware, filter, ou interceptor, dependendo da linguagem ou framework.

Exemplo genérico:

function authMiddleware(req, res, next) {
  const authHeader = req.headers['authorization'];

  if (!authHeader) {
    return res.status(401).json({ error: 'Token não fornecido' });
  }

  const parts = authHeader.split(' ');
  const scheme = parts[0];
  const token = parts[1];

  if (scheme !== 'Bearer' || !token) {
    return res.status(401).json({ error: 'Formato inválido de token' });
  }

  try {
    const payload = validateToken(token); // Função de validação implementada em outro módulo
    req.user = payload;
    next();
  } catch (err) {
    return res.status(401).json({ error: 'Token inválido ou expirado' });
  }
}


/* ------------------------------------------------------------------------------------------------ */

-- Exemplos Práticos de Implementação
Node.js (Express) com JWT
Uma API simples com um endpoint protegido poderia ser:

const express = require('express');
const jwt = require('jsonwebtoken');

const app = express();
const SECRET = 'chave-secreta-exemplo';

function auth(req, res, next) {
  const h = req.headers['authorization'];
  if (!h) return res.status(401).json({ error: 'Token ausente' });

  const [scheme, token] = h.split(' ');
  if (scheme !== 'Bearer') return res.status(401).json({ error: 'Formato inválido' });

  try {
    const payload = jwt.verify(token, SECRET);
    req.user = payload;
    next();
  } catch (e) {
    return res.status(401).json({ error: 'Token inválido' });
  }
}

app.get('/protegido', auth, (req, res) => {
  res.json({ mensagem: 'Acesso autorizado', usuario: req.user });
});

app.listen(3000)


/* ------------------------------------------------------------------------------------------------ */

-- Exemplo genérico de validação usando JWKS:

const jwksClient = require('jwks-rsa');
const jwt = require('jsonwebtoken');

const client = jwksClient({ jwksUri: 'https://auth.exemplo.com/.well-known/jwks.json' });

function getKey(header, callback) {
  client.getSigningKey(header.kid, (err, key) => {
    const signingKey = key.getPublicKey();
    callback(null, signingKey);
  });
}

function validate(token) {
  return new Promise((resolve, reject) => {
    jwt.verify(token, getKey, {}, (err, decoded) => {
      if (err) return reject(err);
      resolve(decoded);
    });
  });
}

Essa abordagem é a utilizada por plataformas como Auth0, Keycloak, Azure AD, Google Identity e outras.


/* ------------------------------------------------------------------------------------------------ */

-- Melhores Práticas:
1. Sempre validar expiração (exp).
2. Verificar o emissor (iss).
3. Validar a audiência (aud).
4. Recusar tokens sem escopos definidos para operações sensíveis.
5. Nunca aceitar tokens via query string.
6. Habilitar HTTPS em todos os ambientes.
7. Aplicar rate limiting.
8. Registrar falhas de autenticação para auditoria.


/* ------------------------------------------------------------------------------------------------ */

-- Revogando um Refresh Token (via cURL):

curl -X POST https://auth.server.com/revoke \
  -u client123:secret \
  -d token=xyz.refresh.token \
  -d token_type_hint=refresh_token

-- Revogando Múltiplos Tokens (Fluxo de Logout):

async function revokeToken(token) {
  const params = new URLSearchParams();
  params.append('token', token);

  await axios.post(
    'https://auth.server.com/revoke',
    params,
    {
      auth: { username: 'client123', password: 'secret' }
    }
  );
}
Aplicações robustas revogam todos os tokens ativos associados ao usuário durante o logout.


/* ------------------------------------------------------------------------------------------------ */

-- Uso de AWS Secrets Manager em uma aplicação Node.js
import { SecretsManagerClient, GetSecretValueCommand } from '@aws-sdk/client-secrets-manager';

const client = new SecretsManagerClient({ region: "us-east-1" });

async function loadCredentials() {
  const command = new GetSecretValueCommand({ SecretId: "client-app/credentials" });
  const response = await client.send(command);
  const secrets = JSON.parse(response.SecretString);
  return secrets.client_secret;
}

loadCredentials().then(secret => {
  console.log("Client secret carregado com segurança.");
});


/* ------------------------------------------------------------------------------------------------ */

-- Troca do Código por Tokens
A aplicação (backend) envia uma requisição ao Authorization Server para trocar o código por tokens. Essa etapa não envolve o navegador.

A requisição deve conter:
grant_type=authorization_code
code: o código recebido.
redirect_uri: deve ser idêntica à da etapa inicial.
client_id e client_secret (em aplicações confidenciais).
code_verifier (quando PKCE está sendo usado).

Exemplo:
POST /oauth/token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code&
code=abc123&
redirect_uri=https://app.example.com/callback&
client_id=cliente123&
client_secret=S3cr3t&
code_verifier=klasjdf8923...
O Authorization Server retorna:

{
  "access_token": "eyJhbGciOi...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "8sddf98as7df...",
  "scope": "read write"
}

Esse é o ponto central de segurança: o token nunca transita pelo navegador, apenas o código temporário.


/* ------------------------------------------------------------------------------------------------ */

-- Exemplo Completo de Fluxo
A sequência resumida:

1. Usuário acessa a aplicação.
2. Sistema redireciona para Authorization Server.
3. Usuário autentica e concede acesso.
4. Authorization Server redireciona com code.
5. Backend troca o code por tokens.
6. Backend usa o token para acessar APIs.

Esse ciclo descreve o funcionamento padrão adotado por plataformas como Google, Microsoft, GitHub e provedores OpenID Connect.


/* ------------------------------------------------------------------------------------------------ */

-- PKCE

O Authorization Code Flow é um dos mecanismos mais utilizados no OAuth2 para permitir que aplicações obtenham tokens de acesso com segurança. Porém, quando aplicado em ambientes públicos ou inseguros — como Single Page Applications (SPAs), aplicativos mobile ou qualquer ambiente em que o client secret não possa ser armazenado com segurança — surge um risco crítico: a possibilidade de interceptação do authorization code por agentes mal‑intencionados.

Para mitigar esse risco, o OAuth 2.1 e as melhores práticas modernas recomendam o uso obrigatório de uma extensão chamada PKCE (Proof Key for Code Exchange). Originalmente projetada para aplicações mobile, PKCE tornou‑se um padrão de segurança amplamente adotado em todos os cenários onde o sigilo do client secret não é garantido.

Antes de entender o PKCE, é essencial compreender o risco que ele resolve. No fluxo Authorization Code tradicional, o cliente:
Redireciona o usuário ao servidor de autorização.
Recebe um authorization code de volta por meio de redirecionamento.
Troca esse code por tokens de acesso.
O risco aparece entre o momento em que o servidor de autorização envia o authorization code e o momento em que o cliente o utiliza. Esse intervalo pode ser explorado para um ataque conhecido como Authorization Code Interception Attack.

O ataque ocorre quando:
Um invasor consegue interceptar o redirecionamento contendo o authorization code.
Ele utiliza esse code para solicitar tokens diretamente ao servidor de autorização.
Como o fluxo tradicional não exige uma prova adicional de posse, o invasor pode obter tokens válidos.

Essa falha se torna particularmente grave em:
Aplicativos mobile (onde apps maliciosos podem capturar callbacks personalizados).
SPAs (onde scripts maliciosos podem interceptar dados na URL).
Ambientes com proxies inseguros.
Para evitar isso, é necessário um mecanismo que garanta que somente o cliente legítimo, que iniciou o fluxo, possa completar a troca pelo token. É aqui que entra o PKCE.

-- PKCE significa Proof Key for Code Exchange (Prova de Chave para Troca de Código). É uma extensão do OAuth2 que substitui a necessidade de um client secret e implementa um mecanismo de prova criptográfica entre as etapas do fluxo Authorization Code.

Com PKCE, o cliente cria um token temporário chamado code verifier, do qual deriva outro valor, o code challenge, que será enviado ao servidor de autorização.

Quando o cliente tenta trocar o authorization code por tokens, ele deve apresentar o code verifier original. O servidor então verifica se:
O code verifier corresponde ao code challenge enviado anteriormente.
Se corresponder, a troca do authorization code é permitida; caso contrário, ela é bloqueada.

Essa prova garante que, mesmo que o authorization code seja interceptado, ele é inútil para o atacante, pois ele não possui o code verifier.


/* ------------------------------------------------------------------------------------------------ */

-- PKCE em Mobile (iOS e Android)
Aplicativos mobile são particularmente vulneráveis a interceptação de callbacks.

Exemplos de ataques reais:
Outro app registrado com o mesmo esquema de URI intercepta o retorno.
WebView manipulada captura a URL.
PKCE garante que mesmo que o retorno seja capturado, a troca do código será impossível.


/* ------------------------------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------------------------------ */
