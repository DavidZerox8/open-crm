<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class LogAuthenticationActivity
{
    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(Login::class, [self::class, 'handleLogin']);
        $events->listen(Logout::class, [self::class, 'handleLogout']);
        $events->listen(Failed::class, [self::class, 'handleFailed']);
    }

    /**
     * Log successful login attempts.
     */
    public function handleLogin(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        activity('auth')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event('login')
            ->withProperties([
                'guard' => $event->guard,
            ])
            ->log('User logged in');
    }

    /**
     * Log successful logout actions.
     */
    public function handleLogout(Logout $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        activity('auth')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event('logout')
            ->withProperties([
                'guard' => $event->guard,
            ])
            ->log('User logged out');
    }

    /**
     * Log failed authentication attempts.
     */
    public function handleFailed(Failed $event): void
    {
        $identifier = (string) ($event->credentials['email'] ?? $event->credentials['username'] ?? 'unknown');

        activity('auth')
            ->event('failed')
            ->withProperties([
                'guard' => $event->guard,
                'identifier' => $identifier,
            ])
            ->log('Authentication failed');
    }
}
