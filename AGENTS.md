# AGENTS.md

## Project

PunBB forum mod (gemorroj/punbb-mod). Legacy-style PHP forum with WAP and WEB
interfaces.

- PHP >= 8.5, MySQL >= 8.4
- Web server: Apache 2 / Nginx / Angie
- Dependencies via Composer (`composer.json`)

## Structure

- Root `*.php` files are pages/entry points (`index.php`, `viewtopic.php`,
  `profile.php`, `admin_*.php`, etc.)
- `include/` — core: `common.php`, `functions.php`, `DBLayer.php`,
  `PunTemplate.php`, `parser.php`, plus submodules (`attach`, `pms`, `poll`,
  `user`, `informer`)
- `include/template/` — Smarty templates (WAP and WEB)
- `lang/` — translations
- `config.php` — DB and cookie settings (created by `.install.php`)

## Commands

- Code style: `vendor/bin/php-cs-fixer fix` (config: `.php-cs-fixer.dist.php`)
- There is no test suite.

## Conventions

- Indentation: 4 spaces, LF line endings, UTF-8 (see `.editorconfig`)
- PHP-CS-Fixer rules: `@Symfony`, `@PHP8x5Migration`; no `declare(strict_types)`,
  no arrow functions
- No comments in code unless strictly necessary
- Global functions and constants (legacy style, `PUN` constant guards entry
  points); classes use namespaces
