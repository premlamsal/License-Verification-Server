# License Verification Server

A standalone PHP API server for validating and activating application licenses. Host this on a separate server that only you control.

## Requirements

- PHP 8.1+
- SQLite (bundled with PHP)
- HTTPS recommended for production

## Setup

1. Upload the `licence-verification-system` folder to your server.
2. Run `composer install` to install dependencies (`vlucas/phpdotenv`).
3. Copy `.env` and update at least:
   - `LICENSE_SECRET` - shared secret with the main application
   - `ADMIN_USERNAME` / `ADMIN_PASSWORD` - admin login credentials
4. Ensure `storage/` is writable.

```bash
chmod 755 storage
```

5. Point your web server document root to the `public/` directory, or keep the current structure with `.htaccess` rewrites.

## Admin Panel

Visit `/` in your browser to access the admin panel.

Features:
- Dashboard with key and request statistics
- Create, edit, view, and delete license keys
- View activation logs
- Bind license keys to domains and set expiration dates

## API

All API endpoints are under `/api` and require an `X-License-Signature` header.

### POST /api/verify

Verifies a license key.

**Headers:**
- `X-License-Signature`: HMAC-SHA256 of the JSON payload using `LICENSE_SECRET`

**Payload:**
```json
{
  "license_key": "XXXXX-XXXXX-XXXXX-XXXXX",
  "domain": "client-site.com",
  "ip": "1.2.3.4",
  "app_version": "1.0.0"
}
```

**Response:**
```json
{
  "valid": true,
  "status": "active",
  "meta": {"plan": "pro"},
  "activated_at": "2024-01-01T00:00:00+00:00",
  "expires_at": null
}
```

### POST /api/activate

Activates a license key for a domain.

**Headers:**
- `X-License-Signature`: HMAC-SHA256 of the JSON payload using `LICENSE_SECRET`

**Payload:**
```json
{
  "license_key": "XXXXX-XXXXX-XXXXX-XXXXX",
  "domain": "client-site.com",
  "ip": "1.2.3.4"
}
```

**Response:**
```json
{
  "success": true,
  "message": "License activated successfully.",
  "meta": {"plan": "pro"},
  "expires_at": null
}
```

## How It Works

1. You create license keys in the admin panel.
2. The main application sends signed requests to `/api/verify` and `/api/activate`.
3. This server checks the key, domain binding, expiration, and returns a JSON response.
4. Activation logs are stored locally for auditing.
