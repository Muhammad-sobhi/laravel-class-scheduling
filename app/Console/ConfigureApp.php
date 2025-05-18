<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConfigureApp extends Command
{
    protected $signature = 'app:configure';
    protected $description = 'Configure the application environment (.env file)';

    public function handle()
{
    $this->info('Welcome! Let\'s configure your application.');

    // Database Configuration
    $dbHost = $this->ask('Enter your database host (default: 127.0.0.1)', '127.0.0.1');
    $dbPort = $this->ask('Enter your database port (default: 3306)', '3306');
    $dbName = $this->ask('Enter your database name (default: class_scheduling)', 'class_scheduling');
    $dbUsername = $this->ask('Enter your database username (default: root)', 'root');
    $dbPassword = $this->secret('Enter your database password (leave blank if none)', ''); // Default to empty string

    // Construct the .env file path
    $envPath = base_path('.env');

    // Construct the .env file content
    $envContent = "APP_NAME=Laravel\n";
    $envContent .= "APP_ENV=local\n";
    $envContent .= "APP_KEY=\n";
    $envContent .= "APP_DEBUG=true\n";
    $envContent .= "APP_URL=http://localhost\n\n";
    $envContent .= "LOG_CHANNEL=stack\n\n";
    $envContent .= "DB_CONNECTION=mysql\n";
    $envContent .= "DB_HOST=$dbHost\n";
    $envContent .= "DB_PORT=$dbPort\n";
    $envContent .= "DB_DATABASE=$dbName\n";
    $envContent .= "DB_USERNAME=$dbUsername\n";
    $envContent .= "DB_PASSWORD=$dbPassword\n\n";
    $envContent .= "BROADCAST_DRIVER=log\n\n";
    $envContent .= "CACHE_DRIVER=file\n";
    $envContent .= "SESSION_DRIVER=file\n";
    $envContent .= "QUEUE_CONNECTION=sync\n\n";
    $envContent .= "SANCTUM_STATEFUL_DOMAINS=localhost\n\n";
    $envContent .= "FORWARDED_HEADER_COUNT=0\n";

    // Write to .env file
    try {
        File::put($envPath, $envContent);
        $this->info('.env file updated successfully!');
    } catch (\Exception $e) {
        $this->error('Failed to write to .env file: ' . $e->getMessage());
        return; // Stop if writing fails
    }

    // Generate Application Key, Migrate and start Server
    $this->call('key:generate');
    $this->call('migrate');
    $this->info('Application configuration complete. You can now run `php artisan serve` to start the server.');
    $this->call('serve');
    
}

}