<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan; // Don't forget this!

class ConfigureApp extends Command
{
    protected $signature = 'app:configure';
    protected $description = 'Configure the application environment (.env file)';

    public function handle()
    {
        $this->info('Welcome! Let\'s configure your application.');

        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        // 1. Ensure .env file exists. If not, copy from .env.example.
        if (! File::exists($envPath)) {
            $initialEnvContent = "APP_NAME=Laravel\n";
            $initialEnvContent .= "APP_ENV=local\n";
            $initialEnvContent .= "APP_KEY=\n"; // Crucial: ensure this line exists initially
            $initialEnvContent .= "APP_DEBUG=true\n";
            $initialEnvContent .= "APP_URL=http://localhost\n\n";
            // Add other essential minimums if needed for the app to function before full config
            
            try {
                File::put($envPath, $initialEnvContent);
                $this->info('.env file created with initial structure.');
            } catch (\Exception $e) {
                $this->error('Failed to create .env file: ' . $e->getMessage());
                return; // Stop if creation fails
            }
        }

        // Generate Application Key FIRST
        // This command will update the APP_KEY= line in the .env file.
        // It's important to do this before the config is fully written with other variables,
        // and crucially, before you try to use any services that depend on the key.
        $this->info('Generating application key...');
        try {
            // Clear config cache BEFORE key:generate runs, so it reads the .env from disk
            Artisan::call('config:clear'); // Essential before key:generate
            Artisan::call('key:generate');
            $this->info(Artisan::output()); // Show output of key:generate (e.g., "Application key set successfully.")
        } catch (\Exception $e) {
            $this->error('Failed to generate application key: ' . $e->getMessage());
            $this->error('Ensure .env file is writable and APP_KEY= line exists.');
            return;
        }

        // Re-read .env content to ensure it has the newly generated APP_KEY
        // This is important because key:generate might have changed the file.
        $existingEnvContent = File::get($envPath);

        // Update specific lines in the .env content with user inputs
        // This approach is safer than rebuilding the entire file, as it preserves
        // other .env variables that might exist (e.g., mail settings, services, etc.)
        $existingEnvContent = preg_replace('/^DB_HOST=.*$/m', "DB_HOST=$dbHost", $existingEnvContent);
        $existingEnvContent = preg_replace('/^DB_PORT=.*$/m', "DB_PORT=$dbPort", $existingEnvContent);
        $existingEnvContent = preg_replace('/^DB_DATABASE=.*$/m', "DB_DATABASE=$dbName", $existingEnvContent);
        $existingEnvContent = preg_replace('/^DB_USERNAME=.*$/m', "DB_USERNAME=$dbUsername", $existingEnvContent);
        $existingEnvContent = preg_replace('/^DB_PASSWORD=.*$/m', "DB_PASSWORD=$dbPassword", $existingEnvContent);
        
        // Ensure other common variables are present, or add them if they don't exist
        // This makes your command more robust if the .env.example doesn't have everything
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'APP_ENV', 'local');
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'APP_DEBUG', 'true');
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'APP_URL', 'http://localhost');
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'LOG_CHANNEL', 'stack');
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'DB_CONNECTION', 'mysql');
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'BROADCAST_DRIVER', 'log');
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'CACHE_DRIVER', 'file');
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'SESSION_DRIVER', 'file');
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'QUEUE_CONNECTION', 'sync');
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'SANCTUM_STATEFUL_DOMAINS', 'localhost');
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'FORWARDED_HEADER_COUNT', '0');
        // You might want to add other variables from your .env.example that you want to manage.


        // Write updated content back to .env file
        try {
            File::put($envPath, $existingEnvContent);
            $this->info('.env file updated with database configuration and other settings!');
        } catch (\Exception $e) {
            $this->error('Failed to write updated .env file: ' . $e->getMessage());
            $this->error('Please ensure the .env file is writable.');
            return;
        }

        // Clear the configuration cache AFTER all .env changes are made
        $this->info('Clearing configuration cache...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        $this->info('Configuration cache cleared.');

        // Now, run migrations
        $this->info('Running database migrations...');
        try {
            $this->call('migrate', ['--force' => true]); // Added --force for production safety check
        } catch (\Exception $e) {
            $this->error('Failed to run migrations: ' . $e->getMessage());
            $this->error('Please check your database server status, credentials, and if the database exists.');
            return;
        }

        $this->info('Application configuration complete.');
        $this->info('You can now run `php artisan serve` to start the server.');
        // $this->call('serve'); // Still recommend against auto-serving
    }

    /**
     * Ensures an environment variable exists or updates its value in the content.
     * @param string $content The current .env file content.
     * @param string $key The key of the environment variable.
     * @param string $value The value to set.
     * @return string The updated .env file content.
     */
    protected function ensureEnvVariable(string $content, string $key, string $value): string
    {
        // Escape value for regex if it contains special characters
        $escapedValue = preg_quote($value, '/');
        
        // If the key exists, replace its value
        if (preg_match("/^{$key}=.*/m", $content)) {
            return preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $content);
        }
        // If the key doesn't exist, append it
        return $content . "\n{$key}={$value}";
    }
}