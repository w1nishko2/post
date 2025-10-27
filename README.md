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

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

# 🛍️ Weebs Market - Telegram E-commerce Platform

Laravel-based e-commerce platform with Telegram Mini App integration.

## 🚀 Features

- 🤖 **Telegram Bot Integration** - Multi-bot support with unique short names
- 📱 **Telegram Mini App** - Modern PWA for shopping
- 📦 **Mass Product Import** - Queue-based Excel import with image downloading
- 🛒 **Smart Checkout System** - Async order processing with queue
- ⏰ **Auto-cancel Orders** - Automatic cancellation of expired orders
- 🎨 **Category Management** - Dynamic categories with images
- 📊 **Statistics** - Visitor tracking and analytics

## ⚙️ CRON Setup for Sweb Hosting

This project uses **Laravel Scheduler** for automated task processing.

### 📚 Documentation Files

| File | Description |
|------|-------------|
| **CRON_INDEX.md** | 📖 Main documentation hub |
| **CRON_ИНСТРУКЦИЯ.txt** | 🇷🇺 Simple step-by-step guide (Russian) |
| **CRON_QUICK_GUIDE.txt** | ⚡ Quick copy-paste commands |
| **CRON_SETUP_SWEB.md** | 📝 Detailed Sweb setup guide |
| **check-cron-config.php** | 🔍 Configuration checker script |

### ⚡ Quick Setup

Add **ONE** CRON job in Sweb control panel:

**Schedule:** `* * * * *` (every minute)

**Command:**
```bash
/usr/bin/php /home/USERNAME/domains/DOMAIN.COM/public_html/artisan schedule:run >> /dev/null 2>&1
```

Replace:
- `USERNAME` - your hosting username
- `DOMAIN.COM` - your domain

### 🎯 Automated Tasks

After CRON setup, the following tasks run automatically:

| Task | Schedule | Description |
|------|----------|-------------|
| **Product Import** | Every minute | Processes 50 products from `import_queue` |
| **Order Checkout** | Every minute | Processes 100 orders from `checkout_queue` |
| **Cancel Expired Orders** | Every 15 minutes | Auto-cancel pending orders older than 24h |
| **Clear Sessions** | Daily | Remove old inactive sessions |

### 🔍 Monitoring

```bash
# Check import queue
php artisan import:monitor-queue

# Check checkout queue  
php artisan checkout:monitor

# View scheduled tasks
php artisan schedule:list

# Manual run (testing)
php artisan schedule:run
```

### 📖 Full Documentation

For complete setup instructions, see **[CRON_INDEX.md](CRON_INDEX.md)**

## 🛠️ Installation

```bash
# Clone repository
git clone https://github.com/w1nishko2/post.git

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run build

# Setup CRON (see CRON documentation above)
```

## 📦 Project Structure

```
app/
├── Console/Commands/      # CRON commands
├── Http/Controllers/      # Web & API controllers
├── Models/               # Eloquent models
├── Imports/              # Excel import classes
├── Jobs/                 # Background jobs
└── Services/             # Business logic

database/
├── migrations/           # Database schema
└── seeders/             # Test data

resources/
├── css/                 # Styles (Vite)
├── js/                  # JavaScript (Vite)
└── views/               # Blade templates

public/
└── storage/             # Public file storage
```

## 🔑 Key Components

### Import Queue System
- Queue-based product import from Excel
- Async image downloading from Yandex.Disk
- Automatic category creation
- Retry mechanism for failed imports

### Checkout Queue System
- Async order processing
- Telegram user integration
- Inventory reservation
- Auto-notifications

### Telegram Integration
- Multi-bot support
- Webhook handling
- Mini App authentication
- Order notifications

## 📞 Support

- **Sweb Hosting:** https://sweb.ru/support
- **Laravel Docs:** https://laravel.com/docs
- **Project Issues:** https://github.com/w1nishko2/post/issues

---

**Version:** 1.0  
**Laravel:** 10.x  
**PHP:** 8.2+  
**Database:** MySQL 5.7+
