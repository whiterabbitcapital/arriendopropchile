<?php

namespace Botble\Installer\Http\Middleware;

use BaseHelper;
use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Http\Request;

class CheckIfInstallingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $content = BaseHelper::getFileData(storage_path(INSTALLING_SESSION_NAME));

            $startingDate = Carbon::parse($content);

            if (! $content || \Carbon\Carbon::now()->diffInMinutes($startingDate) > 30) {
                return redirect()->route('public.index');
            }
        } catch (Exception) {
            return redirect()->route('public.index');
        }

        return $next($request);
    }
}
