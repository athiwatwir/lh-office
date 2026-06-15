<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class BladeComponentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $basePath = app_path('View/Components');

        if (! is_dir($basePath)) {
            return;
        }

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = Str::of($file->getPathname())
                ->after($basePath.DIRECTORY_SEPARATOR)
                ->beforeLast('.php')
                ->replace(DIRECTORY_SEPARATOR, '/')
                ->toString();

            $class = 'App\\View\\Components\\'.str_replace('/', '\\', $relativePath);

            if (! class_exists($class) || ! is_subclass_of($class, Component::class)) {
                continue;
            }

            $alias = collect(explode('/', $relativePath))
                ->map(fn (string $segment): string => Str::kebab($segment))
                ->implode('.');

            Blade::component($alias, $class);
        }
    }
}
