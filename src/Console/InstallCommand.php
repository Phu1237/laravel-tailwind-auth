<?php

namespace Phu1237\TailwindAuth\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{/**
 * The name and signature of the console command.
 *
 * @var string
 */protected $signature = 'auth:install {--c|controllers : Install with controllers}
                            {--core : Install with controllers and routes}
                            {--e|empty : Install with controllers and empty blade}
                            {--b|backup : Backup the old files if it existed}';

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
    {// Backup if command have option backup
        if ($this->option('backup')) {$this->backupFilesAndDirectories();
        }

        // Controllers...
        (new Filesystem)->ensureDirectoryExists(app_path('Http/Controllers/Auth'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/App/Http/Controllers/Auth', app_path('Http/Controllers/Auth'));

        // Requests...
        (new Filesystem)->ensureDirectoryExists(app_path('Http/Requests/Auth'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/App/Http/Requests/Auth', app_path('Http/Requests/Auth'));

        // Base install
        if ($this->option('core')) {// Routes...
            $this->appendToFile('require __DIR__.\'/auth.php\';', base_path('routes/web.php'));
            copy(__DIR__.'/../../stubs/routes/auth.php', base_path('routes/auth.php'));
            // Replace the HOME path to '/'
            $this->replaceInFile('/home', '/', app_path('Providers/RouteServiceProvider.php'));
        }

        // Just export if no option --controllers
        if (!$this->option('controllers') && !$this->option('core')) {// Views...
            (new Filesystem)->ensureDirectoryExists(resource_path('views/auth'));
            (new Filesystem)->ensureDirectoryExists(resource_path('views/layouts'));
            // Empty blade
            if ($this->option('empty')) {(new Filesystem)->copyDirectory(__DIR__.'/../../stubs/resources/views_empty/auth', resource_path('views/auth'));
                (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/resources/views_empty/layouts', resource_path('views/layouts'));
            } else {(new Filesystem)->copyDirectory(__DIR__.'/../../stubs/resources/views/auth', resource_path('views/auth'));
                (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/resources/views/layouts', resource_path('views/layouts'));
            }

            // Tests...
            (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/tests/Feature', base_path('tests/Feature'));

            // Routes...
            $this->appendToFile('require __DIR__.\'/auth.php\';', base_path('routes/web.php'));
            copy(__DIR__.'/../../stubs/routes/auth.php', base_path('routes/auth.php'));

            // Replace the HOME path to '/'
            $this->replaceInFile('/home', '/', app_path('Providers/RouteServiceProvider.php'));

            // Tailwind / Webpack...
            // NPM Packages...
            $this->updateNodePackages(function ($packages) {return [
                    '@tailwindcss/forms' => '^0.2.1',
                    'autoprefixer' => '^10.1.0',
                    'postcss' => '^8.2.1',
                    'postcss-import' => '^12.0.1',
                    'tailwindcss' => '^2.0.2',
                ] + $packages;
            });
            copy(__DIR__.'/../../stubs/tailwind.config.js', base_path('tailwind.config.js'));
            copy(__DIR__.'/../../stubs/webpack.mix.js', base_path('webpack.mix.js'));
            copy(__DIR__.'/../../stubs/resources/css/app.css', resource_path('css/app.css'));
        }

        $this->info('Tailwind Authentication scaffolding installed successfully.');
        $this->comment('Please execute the "npm install && npm run dev" command to build your assets.');
    }

    protected function backupFilesAndDirectories()
    {(new Filesystem)->ensureDirectoryExists(base_path('backups'));
        $array = [
            app_path('Http/Controllers/Auth'),
            app_path('Http/Requests/Auth'),
            resource_path('views/auth'),
            resource_path('views/layouts'),
            base_path('tests/Feature'),
            base_path('routes/web.php'),
            base_path('routes/auth.php'),
            app_path('Providers/RouteServiceProvider.php'),
            base_path('tailwind.config.js'),
            base_path('webpack.mix.js'),
            resource_path('css/app.css'),
        ];
        foreach ($array as $item) {$explode = explode('\\', $item);
            $last = $explode[count($explode) - 1];
            $backup_path = 'backups/'.$last;
            if (file_exists($item)) {if (is_file($item)) {$this->ensureDirectoryOfFileExists($backup_path);copy($item, base_path($backup_path));
                } else if (is_dir($item)) {(new Filesystem)->copyDirectory($item, $backup_path);
                }
            }
        }
        $this->info('View backup files at /backups.');

        return;
    }

    /**
     * Create directory for file if not existed yet
     *
     * @param  string $path
     * @return void
     */
    private static function ensureDirectoryOfFileExists($path)
    {$explode = explode('/', $path);
        array_pop($explode);
        $dir = implode('/', $explode);

        (new Filesystem)->ensureDirectoryExists(base_path($dir));
    }

    /**
     * Update the "package.json" file.
     *
     * @param  callable $callback
     * @param  bool     $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {if (!file_exists(base_path('package.json'))) {return;}$configurationKey = $dev ? 'devDependencies' : 'dependencies';

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
    {file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    /**
     * Append content to the end of file
     *
     * @param  string $content
     * @param  string $path
     * @return void
     */
    protected function appendToFile($content, $path)
    {// And line before and after the content
        file_put_contents($path, file_get_contents($path)."\n".$content."\n");
    }
}
