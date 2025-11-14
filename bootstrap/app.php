<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\App as LaravelApp;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
        
        // Set locale from session for web routes
        $middleware->web(append: [
            \App\Http\Middleware\SetLocaleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle AuthenticationException for API routes (must be first)
        $exceptions->render(function (AuthenticationException $e, \Illuminate\Http\Request $request) {
            // For API routes, always return JSON 401 instead of redirecting
            $path = $request->path();
            
            // Check if this is an API route (starts with v1/ or api/)
            // OR if request accepts JSON
            if (str_starts_with($path, 'v1/') 
                || str_starts_with($path, 'api/') 
                || $request->expectsJson() 
                || $request->wantsJson()
                || $request->ajax()) {
                return response()->json([
                    'type' => 'about:blank',
                    'title' => 'Unauthorized',
                    'status' => 401,
                    'detail' => 'Unauthenticated',
                ], 401);
            }
            
            // For admin routes, redirect to admin login
            if (str_starts_with($path, 'admin/')) {
                return redirect()->route('admin.login');
            }
        });
        
        // Handle RouteNotFoundException (when auth tries to redirect to login that doesn't exist)
        $exceptions->render(function (RouteNotFoundException $e, \Illuminate\Http\Request $request) {
            $path = $request->path();
            $message = $e->getMessage();
            
            // Check if this is a login route error (authentication redirect attempt)
            // AND the request is for an API route
            if ((str_contains($message, 'Route [login]') || str_contains($message, 'login'))
                && (str_starts_with($path, 'v1/') 
                    || str_starts_with($path, 'api/') 
                    || $request->expectsJson() 
                    || $request->wantsJson())) {
                return response()->json([
                    'type' => 'about:blank',
                    'title' => 'Unauthorized',
                    'status' => 401,
                    'detail' => 'Unauthenticated',
                ], 401);
            }
        });
    })->create();
