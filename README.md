<<<<<<< HEAD
<h1 align="center">Gerenciador de Computadores</h1>

<p align="center">
Controle de retirada e devolução de computadores em laboratórios de informática.
</p>

<p align="center">
<a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white" alt="Laravel 13"></a>
<a href="https://www.php.net"><img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.3+"></a>
<a href="https://www.sqlite.org"><img src="https://img.shields.io/badge/SQLite-003B57?logo=sqlite&logoColor=white" alt="SQLite"></a>
<a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/licença-MIT-green" alt="Licença MIT"></a>
</p>

## Sobre o projeto

O Gerenciador de Computadores é um sistema web feito em Laravel para organizar o empréstimo de computadores em laboratórios de informática. Cada máquina é cadastrada pelo número de patrimônio, e o sistema registra quem retirou, quando retirou e quando devolveu, mostrando quais computadores estão disponíveis. Projeto escolar desenvolvido no SENAI, na disciplina de Desenvolvimento de Sistemas.

Entre as funcionalidades:

- **Cadastro de usuários** com nome, e-mail, senha criptografada, CPF e data de nascimento.
- **Login e logout** com token de acesso válido por 2 dias, pela web e pela API.
- **Cadastro de computadores** pelo número de patrimônio (único), com confirmação pela senha do usuário logado.
- **Retirada** de computadores disponíveis, que passam para o status `em_uso`.
- **Devolução** dos próprios empréstimos ativos, que devolve o computador para `disponivel`.
- **Proteção contra retirada duplicada**, com transações e bloqueio de linha (`lockForUpdate`).

## Tecnologias

- [Laravel 13](https://laravel.com/docs) e PHP 8.3+ no back-end.
- [SQLite](https://www.sqlite.org) como banco de dados padrão.
- [Blade](https://laravel.com/docs/blade), [Tailwind CSS 4](https://tailwindcss.com), [jQuery](https://jquery.com) e [SweetAlert2](https://sweetalert2.github.io) no front-end.
- [Vite 8](https://vite.dev) para compilar os assets.
- [PHPUnit 12](https://phpunit.de) nos testes e [Laravel Pint](https://laravel.com/docs/pint) na formatação do código.

## Instalação

Você precisa de PHP 8.3 ou superior (com a extensão SQLite), Composer e Node.js.

```bash
git clone https://github.com/viniciussenaidstatui-collab/Gerenciador-Computadores.git
cd Gerenciador-Computadores

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate
```

Depois, suba o front-end e o servidor em dois terminais:

```bash
npm run dev
```

```bash
php artisan serve
```

A aplicação fica disponível em <http://localhost:8000>. Para gerar os assets de produção, use `npm run build`.

## Como usar

1. Acesse `/cadastro_usuario` e crie sua conta.
2. Entre em `/login` com seu e-mail e sua senha.
3. Na tela inicial, escolha **Cadastrar Computador** ou **Retirar ou devolver computador**.

## Rotas

As rotas marcadas com 🔒 exigem login.

| Método | Rota | Descrição |
| --- | --- | --- |
| GET | `/cadastro_usuario` | Formulário de cadastro de usuário |
| GET | `/login` | Formulário de login |
| POST | `/login` | Autentica o usuário |
| POST | `/logout` 🔒 | Encerra a sessão |
| GET | `/home` 🔒 | Tela inicial |
| GET | `/computadores/registrar` 🔒 | Formulário de cadastro de computador |
| POST | `/computadores/registrar` 🔒 | Cadastra um computador |
| GET | `/emprestimos` 🔒 | Computadores disponíveis e empréstimos ativos do usuário |
| POST | `/emprestimos` 🔒 | Registra uma retirada |
| PATCH | `/emprestimos/{emprestimo}/devolver` 🔒 | Registra uma devolução |

## API

A API usa o cabeçalho `Authorization: Bearer <token>`, com o token devolvido pelo login.

| Método | Rota | Descrição |
| --- | --- | --- |
| POST | `/api/cadastro_usuario` | Cadastra um usuário |
| POST | `/api/login` | Autentica e devolve o token |
| GET | `/api/token/validate` | Confere se o token é válido |
| POST | `/api/logout` | Invalida o token |
| POST | `/api/computadores` | Cadastra um computador (exige token e `senha_confirmacao`) |

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email": "aluno@exemplo.com", "senha": "123456"}'
```

## Banco de dados

| Tabela | Colunas principais |
| --- | --- |
| `usuario` | `nome`, `email` (único), `cpf` (único), `data_nascimento`, `senha` |
| `token_usuario` | `usuario_id`, `token`, `valido_ate` |
| `computadores` | `numero_patrimonio` (único), `status` (`disponivel` ou `em_uso`) |
| `emprestimos` | `computador_id`, `usuario_id`, `retirado_em`, `devolvido_em` |

Um empréstimo está ativo enquanto `devolvido_em` é nulo.

## Testes

```bash
composer test
```

Os testes rodam com SQLite em memória e cobrem o cadastro de computadores (login obrigatório, patrimônio duplicado e senha incorreta), a retirada (computador disponível, indisponível e inexistente) e a devolução (apenas do próprio empréstimo).

Para manter o padrão de código, rode `vendor/bin/pint --dirty` depois de alterar arquivos PHP.

## Contribuindo

Contribuições são bem-vindas. Abra uma *issue* para discutir a mudança ou envie um *pull request* com a descrição do que foi alterado e os testes correspondentes.

## Segurança

Se você encontrar uma vulnerabilidade, abra uma *issue* no repositório descrevendo o problema, sem incluir dados pessoais de ninguém.

## Licença

Este projeto é um software de código aberto licenciado sob a [licença MIT](https://opensource.org/licenses/MIT).


<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>
=======
# Gerenciador de Computadores
>>>>>>> Vini

Sistema web para controlar a **retirada e a devolução de computadores** em laboratórios de informática. Cada computador é cadastrado pelo número de patrimônio, e o sistema registra quem retirou, quando retirou e quando devolveu, mostrando em tempo real quais máquinas estão disponíveis.

Projeto escolar desenvolvido no **SENAI**, na disciplina de Desenvolvimento de Sistemas.

## Funcionalidades

- **Cadastro de usuários** com nome, e-mail, senha (criptografada com bcrypt), CPF e data de nascimento.
- **Login e logout** com token de acesso que vale por 2 dias.
- **Cadastro de computadores** pelo número de patrimônio, com confirmação por senha do usuário logado. O número de patrimônio é único.
- **Retirada de computador**: o usuário escolhe entre os computadores com status `disponivel`, e o computador passa para `em_uso`.
- **Devolução de computador**: o usuário devolve apenas os próprios empréstimos ativos, e o computador volta para `disponivel`.
- **Proteção contra retirada duplicada**: retirada e devolução rodam em transação com bloqueio de linha (`lockForUpdate`), então dois usuários não conseguem pegar o mesmo computador ao mesmo tempo.

## Tecnologias

| Camada | Tecnologia |
| --- | --- |
| Back-end | PHP 8.3+ e Laravel 13 |
| Banco de dados | SQLite (padrão do `.env.example`) |
| Front-end | Blade, Tailwind CSS 4, jQuery e SweetAlert2 |
| Build do front-end | Vite 8 |
| Testes | PHPUnit 12 |
| Formatação de código | Laravel Pint |

## Requisitos

- PHP 8.3 ou superior, com a extensão SQLite habilitada
- Composer
- Node.js e npm

## Como rodar

```bash
# 1. Clonar o repositório
git clone https://github.com/viniciussenaidstatui-collab/Gerenciador-Computadores.git
cd Gerenciador-Computadores

# 2. Instalar as dependências
composer install
npm install

# 3. Configurar o ambiente
cp .env.example .env
php artisan key:generate

# 4. Criar o banco SQLite e as tabelas
touch database/database.sqlite
php artisan migrate

# 5. Compilar o front-end e subir o servidor (em dois terminais)
npm run dev
php artisan serve
```

Depois é só acessar <http://localhost:8000>. Para gerar o front-end de produção, use `npm run build`.

O `composer setup` também existe e faz a instalação, o `.env`, a chave, as migrations e o build de uma vez.

## Como usar

1. Acesse `/cadastro_usuario` e crie uma conta.
2. Entre em `/login` com o e-mail e a senha.
3. Na tela inicial (`/home`), escolha uma ação:
   - **Cadastrar Computador**: informe o número de patrimônio e confirme com a sua senha.
   - **Retirar ou devolver computador**: retire um computador disponível ou devolva um que esteja com você.

## Rotas

### Páginas e ações web

As rotas marcadas com 🔒 exigem login.

| Método | Rota | Descrição |
| --- | --- | --- |
| GET | `/` | Página inicial |
| GET | `/cadastro_usuario` | Formulário de cadastro de usuário |
| GET | `/login` | Formulário de login |
| POST | `/login` | Autentica o usuário |
| POST | `/logout` 🔒 | Encerra a sessão |
| GET | `/home` 🔒 | Tela inicial do usuário logado |
| GET | `/computadores/registrar` 🔒 | Formulário de cadastro de computador |
| POST | `/computadores/registrar` 🔒 | Cadastra um computador |
| GET | `/emprestimos` 🔒 | Lista computadores disponíveis e empréstimos ativos do usuário |
| POST | `/emprestimos` 🔒 | Registra uma retirada |
| PATCH | `/emprestimos/{emprestimo}/devolver` 🔒 | Registra uma devolução |

### API (`/api`)

A autenticação da API usa o cabeçalho `Authorization: Bearer <token>`, com o token devolvido pelo login.

| Método | Rota | Descrição |
| --- | --- | --- |
| POST | `/api/cadastro_usuario` | Cadastra um usuário |
| POST | `/api/login` | Autentica e devolve o token |
| GET | `/api/token/validate` | Confere se o token é válido |
| POST | `/api/logout` | Invalida o token |
| POST | `/api/computadores` | Cadastra um computador (exige token e `senha_confirmacao`) |

Exemplo de login pela API:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email": "aluno@exemplo.com", "senha": "123456"}'
```

## Estrutura do banco de dados

**usuario**: `id`, `nome`, `email` (único), `cpf` (único), `data_nascimento`, `senha`, `timestamps`

**token_usuario**: `id`, `usuario_id` (exclui em cascata), `token`, `valido_ate`, `timestamps`

**computadores**: `id`, `numero_patrimonio` (único), `status` (`disponivel` ou `em_uso`, padrão `disponivel`), `timestamps`

**emprestimos**: `id`, `computador_id`, `usuario_id`, `retirado_em`, `devolvido_em` (nulo enquanto o computador não foi devolvido), `timestamps`

Um empréstimo está **ativo** enquanto `devolvido_em` é nulo.

## Estrutura do projeto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ComputadorController.php   # cadastro de computadores (web e API)
│   │   ├── EmprestimoController.php   # retirada e devolução
│   │   ├── LoginController.php        # login, logout e validação de token
│   │   └── UsuarioController.php      # cadastro de usuários
│   └── Middleware/
│       └── EnsureValidToken.php       # protege as rotas que exigem login
└── Models/                            # Usuario, TokenUsuario, Computador, Emprestimo
database/
├── migrations/
├── factories/
└── seeders/
resources/views/                       # telas em Blade
routes/                                # web.php e api.php
tests/Feature/                         # testes de computadores e empréstimos
```

## Testes

```bash
composer test
```

Os testes usam SQLite em memória e cobrem:

- cadastro de computadores: exige login, impede número de patrimônio duplicado e senha incorreta;
- retirada: computador disponível, indisponível e inexistente;
- devolução: o usuário devolve o próprio empréstimo e não consegue devolver o de outro usuário.

## Padrão de código

O projeto usa o Laravel Pint. Depois de alterar arquivos PHP, rode:

```bash
vendor/bin/pint --dirty
```

## Licença

Projeto sob a licença [MIT](https://opensource.org/licenses/MIT).