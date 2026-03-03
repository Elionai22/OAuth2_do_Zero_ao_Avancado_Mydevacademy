# OAuth2 do Zero ao Avançado (MyDevAcademy)

Projeto de exercícios práticos do curso de OAuth 2.0, com exemplos em PHP para treinar os conceitos de **autenticação** (quem é o usuário) e **autorização** (o que ele pode acessar).

## Objetivo

Este repositório foi criado para:

- praticar validação de identidade com `userId` + `token`;
- validar permissões por recurso;
- entender erros de autenticação e autorização;
- evoluir os exercícios ao longo do curso.

## Estrutura do projeto

```text
.
├── index.php
├── exercicio_1.php
└── README.md
```

## Arquivos

### `index.php`

Arquivo base do projeto. Atualmente apenas define o retorno como JSON:

- `Content-Type: application/json; charset=utf-8`

### `exercicio_1.php`

Primeiro exercício com uma simulação de controle de acesso:

- base de usuários simulada em array;
- validação de `userId` e `token`;
- validação de existência do usuário;
- validação de permissão por recurso (`dashboard`, `profile`);
- resposta em JSON padronizada (`success` ou `error`).

Também contém um bloco de requisição simulada (`$request`) para testar diferentes cenários.

## Requisitos

- PHP 7.4+ (recomendado PHP 8+)
- Servidor local (XAMPP, WAMP, Laragon ou equivalente)

## Como executar no XAMPP

1. Inicie o Apache no XAMPP.
2. Garanta que a pasta do projeto esteja em:

	 ```text
	 C:\xampp\htdocs\sites\OAuth2_do_Zero_ao_Avancado_Mydevacademy
	 ```

3. Acesse no navegador:

- `http://localhost/sites/OAuth2_do_Zero_ao_Avancado_Mydevacademy/index.php`
- `http://localhost/sites/OAuth2_do_Zero_ao_Avancado_Mydevacademy/exercicio_1.php`

## Cenários para testar no `exercicio_1.php`

Edite o array `$request` para validar os comportamentos:

- **Sucesso de acesso**: token correto + recurso permitido.
- **Usuário não identificado**: remova `userId` ou `token`.
- **Usuário não encontrado**: use um `userId` inexistente.
- **Token inválido**: use token diferente do usuário.
- **Sem permissão**: use recurso fora das permissões do usuário.

## Exemplo de resposta de erro (autorização)

```json
{
	"status": "error",
	"type": "authorization_error",
	"message": "Usuário não tem permissão para acessar este recurso."
}
```

## Próximos exercícios (sugestão)

- separar autenticação e autorização em funções diferentes;
- criar múltiplos níveis de escopo/permissão;
- simular expiração de token;
- iniciar fluxo de OAuth 2.0 por etapas (Authorization Code, Client Credentials, etc.).