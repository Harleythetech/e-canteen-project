# Environment Variables

All environment variables are defined in `.env`. The `.env.example` file contains all keys with empty values as a template.

---

## Application

| Variable | Description | Example |
|---|---|---|
| `APP_NAME` | Application name shown in browser title | `PLSP E-Canteen` |
| `APP_ENV` | Environment (`local`, `production`) | `local` |
| `APP_KEY` | 32-character encryption key (auto-generated) | `base64:...` |
| `APP_DEBUG` | Show detailed errors (`true` in dev only) | `true` |
| `APP_URL` | Full URL of the application | `https://ecanteen.plsp.edu.ph` |
| `APP_TIMEZONE` | PHP timezone for all date/time operations | `Asia/Manila` |

---

## Database

| Variable | Description | Example |
|---|---|---|
| `DB_CONNECTION` | Database driver | `pgsql` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `5432` |
| `DB_DATABASE` | Database name | `ecanteen` |
| `DB_USERNAME` | Database user | `postgres` |
| `DB_PASSWORD` | Database password | `secret` |

---

## PayMongo

| Variable | Description | Where to Get |
|---|---|---|
| `PAYMONGO_SECRET_KEY` | Secret key for server-side API calls | PayMongo Dashboard → Developers → API Keys |
| `PAYMONGO_PUBLIC_KEY` | Public key (not currently used server-side) | PayMongo Dashboard → Developers → API Keys |
| `PAYMONGO_WEBHOOK_SECRET` | Webhook signing secret for signature verification | PayMongo Dashboard → Developers → Webhooks |

Test keys start with `sk_test_` / `pk_test_`. Live keys start with `sk_live_` / `pk_live_`.

---

## Session & Cache

| Variable | Description | Default |
|---|---|---|
| `SESSION_DRIVER` | Where sessions are stored | `database` |
| `SESSION_LIFETIME` | Session expiry in minutes | `120` |
| `CACHE_STORE` | Cache backend | `database` |
| `QUEUE_CONNECTION` | Queue backend | `database` |

---

## Mail

| Variable | Description |
|---|---|
| `MAIL_MAILER` | Mail driver (`log` for dev, `smtp` for prod) |
| `MAIL_HOST` | SMTP host |
| `MAIL_PORT` | SMTP port |
| `MAIL_USERNAME` | SMTP username |
| `MAIL_PASSWORD` | SMTP password |
| `MAIL_FROM_ADDRESS` | Sender email address |
| `MAIL_FROM_NAME` | Sender name |

In development, set `MAIL_MAILER=log` to write emails to `storage/logs/laravel.log` instead of sending them.

---

## Production Checklist

Before deploying to production:

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_URL` to your actual domain
- [ ] Set `APP_TIMEZONE=Asia/Manila`
- [ ] Use live PayMongo keys (`sk_live_`, `pk_live_`)
- [ ] Configure `PAYMONGO_WEBHOOK_SECRET` with the live webhook secret
- [ ] Set up the cron job for the scheduler
- [ ] Run `php artisan config:cache` and `php artisan route:cache`
- [ ] Run `npm run build` and commit the `public/build` directory
- [ ] Run `php artisan storage:link`
