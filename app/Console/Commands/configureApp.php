<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

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
            if (File::exists($envExamplePath)) {
                File::copy($envExamplePath, $envPath);
                $this->info('.env file created from .env.example.');
            } else {
                // Fallback if .env.example is also missing (less ideal, but robust)
                File::put($envPath, "APP_NAME=Laravel\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\nDB_PORT=3306\nDB_DATABASE=\nDB_USERNAME=\nDB_PASSWORD=\n");
                $this->warn('.env.example not found. Created a basic .env file.');
            }
        }

        // 2. Generate Application Key (this will update the APP_KEY line in .env)
        // It's crucial to run this before other operations if the key isn't set.
        $this->info('Generating application key...');
        try {
            Artisan::call('key:generate');
            $this->info(Artisan::output()); // Show output of key:generate (e.g., "Application key set successfully.")
        } catch (\Exception $e) {
            $this->error('Failed to generate application key: ' . $e->getMessage());
            return;
        }

        // 3. Prompt for Database Configuration
        $dbHost = $this->ask('Enter your database host (default: 127.0.0.1)', '127.0.0.1');
        $dbPort = $this->ask('Enter your database port (default: 3306)', '3306');
        $dbName = $this->ask('Enter your database name (default: class_scheduling)', 'class_scheduling');
        $dbUsername = $this->ask('Enter your database username (default: root)', 'root');
        $dbPassword = (string) $this->secret('Enter your database password (leave blank if none)', 'root'); 

        // 4. Read the *current* .env content (which now includes the APP_KEY)
        $existingEnvContent = File::get($envPath);

        // 5. Update specific lines in the .env content with user inputs using the helper
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'DB_HOST', $dbHost);
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'DB_PORT', $dbPort);
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'DB_DATABASE', $dbName);
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'DB_USERNAME', $dbUsername);
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'DB_PASSWORD', $dbPassword);

        // Add/ensure other common variables (from your original list)
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'APP_NAME', 'Laravel');
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


        // 6. Write the fully updated content back to .env file
        try {
            File::put($envPath, $existingEnvContent);
            $this->info('.env file updated with database configuration and other settings!');
        } catch (\Exception $e) {
            $this->error('Failed to write updated .env file: ' . $e->getMessage());
            return;
        }

        // 7. Clear the configuration cache AFTER all .env changes are made
        $this->info('Clearing configuration cache...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        $this->info('Configuration cache cleared.');

        // 8. Run migrations
        $this->info('Running database migrations...');
        try {
            // Added --force for production safety check; ensure DB exists before running
            $this->call('migrate', ['--force' => true]); 
        } catch (\Exception $e) {
            $this->error('Failed to run migrations: ' . $e->getMessage());
            return;
        }

        $this->info('Application configuration complete.');
        $this->info('You can now run `php artisan serve` to start the server.');
        // $this->call('serve');
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
        // To handle values that might contain special regex characters
        $valueForRegex = preg_quote($value, '/');
        
        // If the key exists, replace its value
        if (preg_match("/^{$key}=.*/m", $content)) {
            // Replace with the new value, ensure it's quoted if it contains spaces or special characters
            return preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $content);
        }
        // If the key doesn't exist, append it to the end of the file
        // Add a newline if the content doesn't end with one, to prevent appending on the last line
        return rtrim($content, "\n") . "\n{$key}={$value}\n";
    }
}