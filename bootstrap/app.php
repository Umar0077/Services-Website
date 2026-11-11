<?php

use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\ResolveTrackingNumber;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(HandleRedirects::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->web(append: [ResolveTrackingNumber::class]);
        if ($proxies = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : explode(',', $proxies));
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Website forms (not the admin): send people back to the form with their input and a toast alert,
        // instead of a bare "419 Page Expired" / "429 Too Many Requests" / error page. Errors are still logged.
        $websiteForm = fn (Request $request) => $request->isMethod('post')
            && ! $request->is('admin*', 'livewire*')
            && ! $request->expectsJson();
        $backWithToast = fn (Request $request, string $type, string $title, string $message) => redirect()->back(fallback: '/')
            ->withInput($request->except(['_token', 'form_token', 'company_website']))
            ->with('toast', compact('type', 'title', 'message'))
            ->with('toast_suppress_errors', true);

        $exceptions->render(function (\Throwable $e, Request $request) use ($websiteForm, $backWithToast) {
            $expired = $e instanceof \Illuminate\Session\TokenMismatchException
                || ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() === 419);
            if ($expired && $websiteForm($request)) {
                return $backWithToast($request, 'warning', 'Your session expired', 'The page was open for a long time. Your details are still filled in — please send the form again.');
            }
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) use ($websiteForm, $backWithToast) {
            if ($websiteForm($request)) {
                return $backWithToast($request, 'warning', 'Too many requests', 'Please wait a minute before sending another request, or call us instead.');
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) use ($websiteForm, $backWithToast) {
            $isHttp = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
            if ($websiteForm($request) && ! $isHttp && ! $e instanceof \Illuminate\Validation\ValidationException && ! config('app.debug')) {
                return $backWithToast($request, 'error', 'Something went wrong', 'We could not send your request. Please try again in a moment, or call us.');
            }
        });
    })->create();
