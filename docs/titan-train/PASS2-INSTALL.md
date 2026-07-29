# Titan Train Pass 2 Installation

1. Register `App\Domains\TitanTrain\Infrastructure\Providers\TitanTrainServiceProvider::class` immediately after the WorkCore provider in `config/app.php`.
2. Install Composer dependencies for the host.
3. Run `php artisan migrate`.
4. Run `php artisan titan-train:setup-cleaner-foundation --company=<company-id> --actor=<user-id>`.
5. Ensure Passport/API authentication and the `X-Titan-Company` tenant header are available to the Chatbot PWA.
6. Load `/chatbot-pwa/apps/titan-train.js` and call `/api/v1/titan-train/pwa/bootstrap`.
