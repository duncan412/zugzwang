[![NO AI](https://raw.githubusercontent.com/nuxy/no-ai-badge/refs/heads/master/badge.svg)](https://github.com/nuxy/no-ai-badge#no-ai-badge)
![PHPStan Level 9](https://img.shields.io/badge/phpstan%20level-9%20of%209-brightgreen?style=flat-square&logo=php)

# Zugzwang

Quick start

1. Install dependencies:

```bash
composer install
```

2. Copy the environment file and set values:

```bash
cp .env.example .env
# Edit .env: set ENABLE_JWT=1 and JWT_SECRET
```

3. Start the built-in PHP server:

```bash
php -S 0.0.0.0:8080 -t public
```

4. Endpoints:

- `GET /` — welcome
- `GET /board/{fen}` — show requested FEN
- `POST /token` — demo token generator (if ENABLE_JWT=1)

Dependencies:
This project uses Slim 4 for routing and supports optional JWT authentication.

Notes

- If `ENABLE_JWT` is `1`, JWT middleware will be activated and most routes will require a valid token. The demo `/token` endpoint returns a token for testing purposes only.


Special thanks to:
[The PHP League](https://thephpleague.com/) for creating Fractal
[Slim Framework](https://www.slimframework.com/) for Slim (4) Framework
[Matthieu Napoli](https://github.com/mnapoli) for creating PHP-DI
[James Read](https://github.com/JimTools) for forking and maintaining JTW tools!
[Vance Lucas](https://github.com/vlucas) for creating phpdotenv