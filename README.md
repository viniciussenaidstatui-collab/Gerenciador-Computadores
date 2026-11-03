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

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
