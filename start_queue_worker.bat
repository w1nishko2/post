@echo off
cd /d "C:\OSPanel\domains\post"
echo Starting Laravel Queue Worker...
echo Press Ctrl+C to stop
php artisan queue:work --tries=3 --timeout=30
pause