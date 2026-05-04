# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PetConnect is a pet adoption platform built with PHP/Slim Framework. Users can browse pets, submit adoption requests, and chat with an AI assistant. Admins manage pets, users, and adoption workflows. The app runs on a WAMP stack (Windows Apache MySQL PHP) at `http://localhost/eCommerceProject/PetConnect/`.

## Development Commands

```bash
# Install PHP dependencies
composer install

# Seed the database with sample data (pets, categories, admin user)
# Visit in browser: http://localhost/eCommerceProject/PetConnect/seed
# Default admin credentials: admin@petconnect.ca / admin1234
```

There is no build step, test suite, or npm scripts — this is a plain PHP app with vanilla JS/CSS.

## Environment Setup

Copy `.env.example` to `.env` and configure:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — MySQL connection
- `GROQ_API_KEY` — for the AI chat feature (Groq Llama API)
- `MAIL_*` — SMTP via Brevo for email features (registration, 2FA, password reset)

RedBeanPHP auto-creates database tables on first run (no migrations needed). In production, `R::freeze(true)` is set in `index.php` to prevent schema changes.

## Architecture

**Stack:** Slim Framework 4 + Twig + RedBeanPHP ORM + PHP-DI

**Request flow:** `.htaccess` → `index.php` (DI container + middleware + routes) → Controller → Model/Twig → Response

**Key entry point:** `index.php` — registers all routes, middleware, and the DI container. All controllers are wired here via PHP-DI.

### MVC Layout

- `src/Controllers/` — 7 controllers extending `BaseController` (which provides `render()`, `redirect()`, `json()`, `flash()`)
- `src/Models/` — RedBeanPHP wrappers extending `Model` base class with `find()`, `create()`, `save()`, `delete()`
- `src/Middleware/` — PSR-15 middleware for auth checks, flash messages, security headers, maintenance mode
- `src/Services/Mailer.php` — PHPMailer abstraction for SMTP email
- `src/I18n/I18n.php` — Symfony Translation wrapper (en/fr)
- `templates/` — Twig templates using `base.twig` inheritance
- `Translations/messages.{en,fr}.php` — Translation strings

### Auth & Sessions

Authentication is session-based (`$_SESSION['user_id']`, `$_SESSION['user_role']`). `AuthMiddleware` protects user routes; `AdminMiddleware` protects `/admin/*` routes. The app uses `delight-im/auth` for registration/login and `robthree/twofactorauth` for TOTP-based 2FA.

### Key Route Groups

- Public: `/`, `/pets`, `/pets/search`, `/pets/{id}`, `/lang/{locale}`, `/contact`, `/donate`
- Auth: `/login`, `/register`, `/2fa`, `/profile`, `/reset-password/*`
- Adoption: `/pets/{id}/adopt`, `/adoptions`, `/adoptions/{id}`
- Admin (double-protected): `/admin/*`
- API: `POST /api/chat` (Groq Llama AI chat, returns JSON)

### Database Schema

Tables (auto-created by RedBeanPHP): `user`, `pet`, `category`, `adoptionrequest`, `adoptionhistory`. Pet status values: `available` / `pending` / `adopted`. User roles: `user` / `admin`.

### Frontend

- `Assets/styles.css` — all CSS, no framework
- `Assets/app.js` — live search (debounced fetch), flash message animations
- `Assets/chat.js` — AI chat widget interactions
- `Assets/uploads/` — user-uploaded pet photos (not committed to git)

### Localization

Language is switched via `GET /lang/{locale}` (stores `en` or `fr` in session). Twig templates call `__('key')` helper defined in `I18n.php`. Adding a language requires a new `Translations/messages.{locale}.php` file and registering it in `I18n.php`.

### AI Chat

`ChatController` proxies messages to Groq's API (model: `llama-3.3-70b-versatile`) via cURL. The system prompt configures the assistant to know about PetConnect's features and available pets. Protected by `AuthMiddleware` — only logged-in users can chat.

## Maintenance Mode

Set `define('MAINTENANCE_MODE', true)` near the top of `index.php` to enable. `MaintenanceMiddleware` intercepts all requests and renders `templates/maintenance/maintenance.twig`.

## Available Claude Code Skills

Installed skills useful for this project:

| Skill | Command | When to use |
|---|---|---|
| Code Review | `/review` | Review a PR or set of changes before merging |
| Security Review | `/security-review` | Audit auth, session handling, input validation, CSP headers |
| Simplify | `/simplify` | Clean up a controller or model after adding features |
| UI/UX Pro Max | `/ui-ux-pro-max` | Improve Twig templates, CSS layout, or UX flows |
| Frontend Design | `/frontend-design` | CSS/JS work on `Assets/styles.css`, `app.js`, `chat.js` |
| Security Audit | `/audit` | Deep scan of a specific file or route for vulnerabilities |
| Test Generation | `/testgen` | Generate PHPUnit tests for controllers or models |
| Browser Testing | `/ruflo-browser` | Manually drive browser flows (login, adoption, admin) when no test suite exists |
| Documentation | `/ruflo-docs` | Generate API or inline docs for controllers/services |

Skills **not applicable** to this project: market-data, neural-trader, IoT, WASM, federation, knowledge-graph — these are unrelated to a PHP web app.
