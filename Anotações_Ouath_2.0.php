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

/* ------------------------------------------------------------------------------------------------ */

/* ------------------------------------------------------------------------------------------------ */
