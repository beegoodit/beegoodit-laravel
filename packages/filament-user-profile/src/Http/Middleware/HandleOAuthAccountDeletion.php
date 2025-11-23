<?php

namespace BeeGoodIT\FilamentUserProfile\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class HandleOAuthAccountDeletion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check BEFORE processing the request - if account was deleted, redirect immediately
        if (Session::has('account_deleted_after_oauth')) {
            \Illuminate\Support\Facades\Log::info('HandleOAuthAccountDeletion - flag found, redirecting to home', [
                'path' => $request->path(),
                'method' => $request->method(),
            ]);

            $deletedEmail = Session::get('deleted_user_email');
            Session::forget('account_deleted_after_oauth');
            Session::forget('deleted_user_email');

            // Ensure user is logged out
            try {
                if (Auth::check()) {
                    Auth::guard('web')->logout();
                }
            } catch (\Exception $e) {
                // User might already be deleted, ignore
            }

            Session::invalidate();
            Session::regenerateToken();

            // Redirect to home
            return redirect('/')->with('account_deleted', true);
        }

        $response = $next($request);

        // Also check AFTER response in case flag was set during request processing
        if (Session::has('account_deleted_after_oauth')) {
            \Illuminate\Support\Facades\Log::info('HandleOAuthAccountDeletion - flag found AFTER request, modifying redirect', [
                'path' => $request->path(),
                'response_type' => get_class($response),
            ]);

            Session::forget('account_deleted_after_oauth');
            Session::forget('deleted_user_email');

            // If it's a redirect response, change it to redirect to home
            if ($response instanceof \Illuminate\Http\RedirectResponse) {
                return redirect('/')->with('account_deleted', true);
            }

            // If it's a regular response, redirect to home
            return redirect('/')->with('account_deleted', true);
        }

        return $response;
    }
}
