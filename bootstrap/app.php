<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Support\Seo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Public 404s and 500s render in the site layout, like the Nuxt
        // error page. The admin keeps Filament's own error pages.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $status = $response->getStatusCode();

            if (! in_array($status, [404, 500, 503], true) || $request->is('admin', 'admin/*', 'livewire/*')) {
                return $response;
            }

            if ($status !== 404 && config('app.debug')) {
                return $response;
            }

            $seo = new Seo(title: $status === 404 ? 'Page not found' : 'Something went wrong', description: '', index: false);

            return Inertia::render('site/Error', ['status' => $status, 'meta' => $seo->forClient()])
                ->withViewData(['seo' => $seo])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
