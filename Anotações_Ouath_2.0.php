<?php

/* ----- */
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