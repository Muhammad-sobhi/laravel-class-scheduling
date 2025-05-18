<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str; // Added for str_replace in ensureEnvVariable

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
                // Fallback if .env.example is also missing
                $this->warn('.env.example not found. Creating a basic .env file.');
                $fallbackContent = "APP_NAME=Laravel\n";
                $fallbackContent .= "APP_ENV=local\n";
                $fallbackContent .= "APP_KEY=\n"; // Crucial placeholder for key:generate
                $fallbackContent .= "APP_DEBUG=true\n";
                $fallbackContent .= "APP_URL=http://localhost\n\n";
                $fallbackContent .= "LOG_CHANNEL=stack\n\n";
                $fallbackContent .= "DB_CONNECTION=mysql\n";
                $fallbackContent .= "DB_HOST=127.0.0.1\n";
                $fallbackContent .= "DB_PORT=3306\n";
                $fallbackContent .= "DB_DATABASE=\n";
                $fallbackContent .= "DB_USERNAME=\n";
                $fallbackContent .= "DB_PASSWORD=\n\n";
                // Add any other crucial defaults from .env.example here if needed for minimal function
                try {
                    File::put($envPath, $fallbackContent);
                } catch (\Exception $e) {
                    $this->error('Failed to create basic .env file: ' . $e->getMessage());
                    return; // Stop if creation fails
                }
            }
        }

        // 2. Ensure necessary storage subdirectories exist and are writable
        // This addresses "No such file or directory" errors for sessions, cache, logs etc.
        $this->info('Ensuring storage directories exist and have proper permissions...');
        $storageDirs = [
            'framework/cache',
            'framework/sessions',
            'framework/views',
            'app',
            'logs',
        ];
        foreach ($storageDirs as $dir) {
            $fullPath = storage_path($dir);
            if (!File::isDirectory($fullPath)) {
                try {
                    File::makeDirectory($fullPath, 0775, true); // Recursive, writable by group
                    $this->line(" - Created: " . $fullPath);
                } catch (\Exception $e) {
                    $this->error(" - Failed to create directory {$fullPath}: " . $e->getMessage());
                    $this->error('Please ensure your PHP user has write permissions to the "storage/" directory.');
                    return; // Stop if crucial directory cannot be created
                }
            }
        }

        // Attempt to set/fix permissions on key directories (especially important for fresh clones or Linux)
        try {
            // @ suppresses warnings if chmod fails on Windows where it's less relevant
            @chmod(storage_path(), 0775);
            @chmod(storage_path('framework'), 0775);
            @chmod(base_path('bootstrap/cache'), 0775); // For cached config/services
            $this->info('Storage directory permissions checked/set.');
        } catch (\Exception $e) {
            $this->warn('Could not set permissions on storage/bootstrap/cache. Manual adjustment might be needed.');
            // Don't return here, as command might still work on Windows or if permissions are already fine.
        }


        // 3. Generate Application Key (this will update the APP_KEY line in .env)
        $this->info('Generating application key...');
        try {
            // Clear config cache BEFORE key:generate runs, so it reads the .env from disk
            Artisan::call('config:clear'); // Essential before key:generate
            Artisan::call('key:generate');
            $this->info(Artisan::output()); // Show output of key:generate
        } catch (\Exception $e) {
            $this->error('Failed to generate application key: ' . $e->getMessage());
            $this->error('Ensure .env file is writable and APP_KEY= line exists.');
            return;
        }

        // 4. Prompt for Database Configuration
        $dbHost = $this->ask('Enter your database host (default: 127.0.0.1)', '127.0.0.1');
        $dbPort = $this->ask('Enter your database port (default: 3306)', '3306');
        $dbName = $this->ask('Enter your database name (default: class_scheduling)', 'class_scheduling');
        $dbUsername = $this->ask('Enter your database username (default: root)', 'root');
        // Explicitly cast to string to prevent TypeError if secret() returns null/non-string
        $dbPassword = (string) $this->secret('Enter your database password (leave blank if none)', '');

        // 5. Read the *current* .env content (which now includes the APP_KEY generated above)
        $existingEnvContent = File::get($envPath);

        // 6. Update specific lines in the .env content with user inputs using the helper
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'DB_HOST', $dbHost);
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'DB_PORT', $dbPort);
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'DB_DATABASE', $dbName);
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'DB_USERNAME', $dbUsername);
        $existingEnvContent = $this->ensureEnvVariable($existingEnvContent, 'DB_PASSWORD', $dbPassword);

        // Ensure other common variables are present, or add them if they don't exist
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
        // You might want to add other variables from your .env.example that you want to manage.

        // 7. Write the fully updated content back to .env file
        try {
            File::put($envPath, $existingEnvContent);
            $this->info('.env file updated with database configuration and other settings!');
        } catch (\Exception $e) {
            $this->error('Failed to write updated .env file: ' . $e->getMessage());
            $this->error('Please ensure the .env file is writable.');
            return;
        }

        // --- VALIDATING ACTIVE DB CONFIGURATION (FOR DEBUGGING) ---
        $this->warn("\n--- VALIDATING ACTIVE DB CONFIGURATION (READ FROM LARAVEL) ---");
        // Clear config cache again just before validation and migrate, to be absolutely sure
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        $this->info('Config & Cache cleared for final check.');

        // Re-read config after clearing cache
        $this->info('DB_HOST: ' . config('database.connections.mysql.host'));
        $this->info('DB_PORT: ' . config('database.connections.mysql.port'));
        $this->info('DB_DATABASE: ' . config('database.connections.mysql.database'));
        $this->info('DB_USERNAME: ' . config('database.connections.mysql.username'));
        $this->info('DB_PASSWORD: ' . (config('database.connections.mysql.password') ? '********' : '[EMPTY/NULL]'));
        $this->warn("--- END VALIDATION ---\n");
        // --- END DEBUGGING ---

        // 8. Run migrations
        $this->info('Running database migrations...');
        try {
            // Added --force for non-interactive execution (e.g., in production/scripts)
            $this->call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            $this->error('Failed to run migrations: ' . $e->getMessage());
            $this->error('Please check your database server status, credentials, and if the database exists.');
            return;
        }

        $this->info('Application configuration complete.');
        $this->info('You can now run `php artisan serve` to start the server.');
        // $this->call('serve'); // Still recommend against auto-serving as it blocks the terminal
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
        // Quote value for regex to prevent issues with special characters
        $valueForRegex = preg_quote($value, '/');

        // Check if the key exists using a more robust regex
        // 'm' modifier for multiline, allowing ^ and $ to match start/end of lines
        if (preg_match("/^$key=.*$/m", $content)) {
            // If the key exists, replace its entire line with the new value
            return preg_replace("/^$key=.*$/m", "{$key}={$value}", $content);
        } else {
            // If the key does not exist, append it to the end of the file
            // Ensure there's a newline before appending if the file doesn't end with one.
            return rtrim($content, "\n") . "\n{$key}={$value}\n";
        }
    }
}