# MoleKVM — AI-Powered Remote Control Device

> The world's first AI-powered KVM device. Plug a thumb-sized dongle into any computer and control it remotely with AI autopilot. No drivers. No software. Works even in BIOS.

[![License: Apache-2.0](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![Docker](https://img.shields.io/badge/docker-ready-green.svg)](docker-compose.yml)
[![PHP](https://img.shields.io/badge/php-8.0%2B-purple.svg)](index.php)

---

## 🚀 Quick Start (Using Makefile)

The easiest way to get started is using the provided Makefile:

```bash
# 1. Setup environment
make install

# 2. Start development server
make dev

# 3. Open http://localhost:8080
```

### Available Commands

Run `make help` to see all available commands:

| Command | Description |
|---------|-------------|
| `make install` | Setup environment files |
| `make dev` | Start Docker development environment |
| `make dev-stop` | Stop all services |
| `make test` | Run all tests |
| `make test-e2e` | Run end-to-end tests |
| `make build` | Build Docker images |
| `make logs` | View logs |
| `make clean` | Clean up containers |

### Alternative: Manual Docker

```bash
# Clone and start
git clone <repo-url>
cd www
docker-compose -f docker-compose.plesk.yml up -d

# Open http://localhost:8080
```

### Plesk Hosting

1. Upload `index.php` and `.env.example` to your Plesk-hosted domain
2. Rename `.env.example` to `.env` and configure
3. Access via your domain - Plesk auto-detects `index.php`

---

## 📁 Project Structure

```
www/
├── index.php              # Main entry point (Plesk-compatible)
├── molekvm-landing.html   # Static HTML backup
├── Makefile               # Project commands & automation
├── .env.example           # Environment configuration template
├── docker-compose.yml     # Docker orchestration
├── docker-compose.plesk.yml  # Plesk emulation mode
├── Dockerfile             # Plesk-emulated Apache/PHP image
├── apache-vhost.conf      # Apache virtual host config
├── plesk-admin/           # Plesk Panel Emulation
│   ├── index.html         # Mock Plesk dashboard
│   ├── css/
│   └── js/
├── tests/                 # Test suites
│   ├── e2e/              # Playwright E2E tests
│   └── unit/             # Unit tests
└── README.md             # This file
```

---

## 🧪 Testing & E2E

### Quick Test Commands

```bash
# Run all tests
make test

# Run E2E tests only
make test-e2e

# Run E2E with UI debugger
make test-e2e-ui
```

### E2E Test Setup (Playwright)

```bash
# Install Playwright
make e2e-setup

# Record new test
make e2e-record

# View test report
make e2e-report
```

### Writing E2E Tests

Tests are in `tests/e2e/` directory:

```javascript
// tests/e2e/landing.spec.js
const { test, expect } = require('@playwright/test');

test('landing page loads', async ({ page }) => {
  await page.goto('http://localhost:8080');
  await expect(page).toHaveTitle(/MoleKVM/);
  await expect(page.locator('h1')).toContainText('AI mole');
});

test('pricing section visible', async ({ page }) => {
  await page.goto('http://localhost:8080');
  await page.click('text=Pricing');
  await expect(page.locator('.price-card')).toHaveCount(3);
});
```

### Test Coverage

| Test Type | Command | Status |
|-----------|---------|--------|
| Unit | `make test-unit` | 🚧 Planned |
| Integration | `make test-integration` | 🚧 Planned |
| E2E | `make test-e2e` | ✅ Ready |
| Visual | `make test-visual` | ✅ Ready |

### CI/CD Testing

```bash
# Full CI pipeline
make ci-test

# Includes: install → dev → test-e2e → clean
```

---

## 🐳 Docker Setup

### Plesk Emulation Mode

The Docker setup includes a **Plesk Panel Emulation** interface for testing Plesk-specific configurations locally.

```bash
# Start all services
docker-compose up -d

# Services available:
# - http://localhost:8080     - MoleKVM website (Apache/PHP)
# - http://localhost:8081     - Plesk Panel Emulation (mock)
# - http://localhost:8082     - Nginx alternative

# View logs
docker-compose logs -f apache

# Stop
docker-compose down
```

### Docker Images

| Service | Image | Ports | Purpose |
|---------|-------|-------|---------|
| Apache/PHP | `molekvm/apache-php` | 8080 | Main website (Plesk-like) |
| Plesk Mock | `molekvm/plesk-admin` | 8081 | Panel emulation UI |
| Nginx | `molekvm/nginx` | 8082 | Alternative web server |

---

## 🖥️ Plesk Hosting Guide

### Upload to Plesk

1. **File Manager Method:**
   - Plesk → Files → Upload `index.php` to `httpdocs/`
   - Upload `.env` file (optional)

2. **Git Deployment:**
   ```bash
   # In Plesk Git extension
   Repository URL: <your-git-url>
   Deployment path: /httpdocs
   ```

3. **FTP/SFTP:**
   ```bash
   sftp user@your-domain.com
   put index.php /httpdocs/
   ```

### Plesk PHP Settings

Recommended settings in Plesk → PHP Settings:

```ini
max_execution_time = 30
memory_limit = 128M
upload_max_filesize = 64M
post_max_size = 64M
max_input_vars = 1000
```

### Plesk .htaccess (if using Apache)

```apache
# Auto-generated by Plesk - modify as needed
<IfModule mod_headers.c>
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
</IfModule>

# PHP handler (Plesk sets this automatically)
# AddHandler fcgid-script .php
```

---

## ⚙️ Environment Configuration

Copy `.env.example` to `.env` and configure:

```bash
# Core Settings
SITE_NAME="MoleKVM"
SITE_URL=https://molekvm.com

# Stripe (Payment Processing)
STRIPE_PK=pk_test_...
STRIPE_SK=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Email (SMTP via Plesk or external)
SMTP_HOST=localhost
SMTP_PORT=25
SMTP_USER=
SMTP_PASS=
SMTP_FROM=noreply@molekvm.com

# Database (if using backend features)
DB_HOST=localhost
DB_NAME=molekvm
DB_USER=molekvm
DB_PASS=secure_password

# AI/Cloud API
AI_API_KEY=
AI_API_URL=https://api.openai.com/v1

# Debug Mode (disable in production)
DEBUG=false
```

---

## 🎨 Customization

### Changing Colors/Theme

Edit CSS variables in `index.php`:

```css
:root {
  --earth: #1a1610;
  --tunnel: #0d0b08;
  --amber: #f0a030;
  /* ... */
}
```

### Adding Payment Methods

The checkout modal uses Stripe. To add other providers:

1. Add new button in pricing section
2. Create handler function (see `openCheckout()` in JavaScript)
3. Configure in `.env`

---

## 🔒 Security

### Plesk Security Settings

1. **SSL/TLS:**
   - Plesk → SSL/TLS Certificates → Let's Encrypt (free)
   - Force HTTPS redirect enabled

2. **Firewall:**
   ```bash
   # Plesk Firewall rules (auto-configured)
   TCP 80    # HTTP
   TCP 443   # HTTPS
   TCP 8443  # Plesk Panel
   ```

3. **ModSecurity:**
   - Enable in Plesk → Security → Web Application Firewall
   - Use OWASP Core Rule Set

### Application Security Headers

Already included in `index.php`:

```php
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

---

## 📊 Monitoring & Logs

### Plesk Log Locations

```
/var/www/vhosts/example.com/logs/access_log
/var/www/vhosts/example.com/logs/error_log
/var/log/plesk/php_error.log
```

### Docker Logs

```bash
# Real-time logs
docker-compose logs -f

# Specific service
docker-compose logs -f apache
```

---

## 🛠️ Development

### Local Development Workflow

```bash
# 1. Start dev environment
docker-compose up -d

# 2. Watch for changes (live reload optional)
npm install -g browser-sync
browser-sync start --proxy "localhost:8080" --files "*.php,*.css,*.js"

# 3. Edit index.php - changes reflect immediately

# 4. Test Plesk deployment
docker-compose -f docker-compose.plesk.yml up -d
```

### Testing Plesk Environment Locally

The `plesk-admin/` directory contains a mock Plesk panel UI:

```bash
# Access mock Plesk panel
curl http://localhost:8081

# Test deployment workflow
./project.sh deploy-test
```

---

## 📚 API Endpoints (if using server.js)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/create-payment-intent` | POST | Stripe payment initialization |
| `/api/kickstarter-notify` | POST | Kickstarter waitlist signup |
| `/api/contact` | POST | Contact form submission |
| `/api/health` | GET | Health check |

---

## 🚢 Deployment Checklist

- [ ] Update `.env` with production values
- [ ] Set `DEBUG=false` in production
- [ ] Configure Stripe live keys
- [ ] Set up SMTP for transactional emails
- [ ] Enable SSL certificate (Let's Encrypt)
- [ ] Configure backups in Plesk
- [ ] Set up monitoring (optional)
- [ ] Test checkout flow end-to-end
- [ ] Verify email deliverability

---

## 🆘 Troubleshooting

### Plesk 500 Error

```bash
# Check PHP error log
cat /var/log/plesk/php_error.log

# Verify file permissions
chmod 644 index.php
chown www-data:www-data index.php
```

### Docker Port Conflicts

```bash
# Change ports in docker-compose.yml
ports:
  - "8090:80"  # Use 8090 instead of 8080
```

### Stripe Integration Issues

1. Verify `STRIPE_PK` in `.env`
2. Check browser console for JS errors
3. Ensure webhook URL is publicly accessible

---

## 📄 License

Apache 2.0 - See [LICENSE](LICENSE) file

---

## 👤 Author

**Tom Sapletta** 
- Website: [sapletta.com](https://sapletta.com)
- Email: tom@sapletta.com
- GitHub: [@tom-sapletta-com](https://github.com/tom-sapletta-com)

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing`
5. Open Pull Request

---

<p align="center">
  <strong>Made with 🐛 in the EU</strong><br>
  <em>WEEE Registration: DE12345678</em>
</p>

## License

Apache License 2.0 - see [LICENSE](LICENSE) for details.

## Author

Created by **Tom Sapletta** - [tom@sapletta.com](mailto:tom@sapletta.com)
