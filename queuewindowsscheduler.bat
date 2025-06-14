@echo off
SET "LARAVEL_PATH=%~dp0"
SCHTASKS /CREATE /TN "LaravelQueueWorker" /TR "%LARAVEL_PATH%artisan queue:work --sleep=5 --tries=5" /SC MINUTE /MO 10 /RL HIGHEST /F
echo Task scheduled successfully!
