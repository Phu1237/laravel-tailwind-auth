<?php

namespace Phu1237\TailwindAuth\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:install {--c|controllers : Install with controllers}
                            {--e|empty : Install with controllers and empty blade}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Tailwind Authentication controllers and resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // NPM Packages...
        $this->updateNodePackages(function ($packages) {
            return [
                '@tailwindcss/forms' => '^0.2.1',
                'autoprefixer' => '^10.1.0',
                'postcss' => '^8.2.1',
                'postcss-import' => '^12.0.1',
                'tailwindcss' => '^2.0.2',
            ] + $packages;
        });

        // Controllers...
        (new Filesystem)->ensureDirectoryExists(app_path('Http/Controllers/Auth'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/App/Http/Controllers/Auth', app_path('Http/Controllers/Auth'));

        // Requests...
        (new Filesystem)->ensureDirectoryExists(app_path('Http/Requests/Auth'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/App/Http/Requests/Auth', app_path('Http/Requests/Auth'));

        // Views...
        // Just export if no option --controllers
        if (!$this->option('controllers')) {
            (new Filesystem)->ensureDirectoryExists(resource_path('views/auth'));
            (new Filesystem)->ensureDirectoryExists(resource_path('views/layouts'));

            (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/resources/views/auth', resource_path('views/auth'));
            (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/resources/views/layouts', resource_path('views/layouts'));
        } else if ($this->option('empty')) {
            (new Filesystem)->ensureDirectoryExists(resource_path('views_empty/auth'));
            (new Filesystem)->ensureDirectoryExists(resource_path('views_empty/layouts'));

            (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/resources/views_empty/auth', resource_path('views/auth'));
            (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/resources/views_empty/layouts', resource_path('views/layouts'));
        }

        if (!$this->option('controllers')) {
            // Tests...
            (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/tests/Feature', base_path('tests/Feature'));

            // Routes...
            copy(__DIR__.'/../../stubs/routes/web.php', base_path('routes/web.php'));
            copy(__DIR__.'/../../stubs/routes/auth.php', base_path('routes/auth.php'));

            // Replace the HOME path to '/'
            $this->replaceInFile('/home', '/', app_path('Providers/RouteServiceProvider.php'));

            // Tailwind / Webpack...
            copy(__DIR__.'/../../stubs/tailwind.config.js', base_path('tailwind.config.js'));
            copy(__DIR__.'/../../stubs/webpack.mix.js', base_path('webpack.mix.js'));
            copy(__DIR__.'/../../stubs/resources/css/app.css', resource_path('css/app.css'));
        }

        $this->info('Tailwind Authentication scaffolding installed successfully.');
        $this->comment('Please execute the "npm install && npm run dev" command to build your assets.');
    }

    /**
     * Update the "package.json" file.
     *
     * @param  callable $callback
     * @param  bool     $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (!file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }

    /**
     * Replace a given string within a given file.
     *
     * @param  string $search
     * @param  string $replace
     * @param  string $path
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
}
