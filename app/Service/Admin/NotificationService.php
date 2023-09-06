<?php

namespace App\Service\Admin;

use App\Models\Store;
use App\Models\User;
use Filament\Notifications\Notification;

class NotificationService
{
    public static function new(): NotificationService
    {
        return new self;
    }

    public function send(
        Store  $store,
        string $title,
        string $status = 'success',
        string $icon = 'check',
    ): void
    {
        $users = User::whereIn('role_id', [1, 3])
            ->get(['id', 'role_id', 'permissions'])
            ->filter(function (User $user) use ($store) {
                if (in_array($user->role_id, [1, 2])) {
                    return true;
                }

                return in_array($store->id, $user->permissions);
            });

        Notification::make()
            ->icon("heroicon-o-$icon")
            ->status($status)
            ->title($title)
            ->sendToDatabase($users);
    }

    public function sendToOwner(
        Store  $store,
        string $title,
        string $status = 'success',
        string $icon = 'check',
    ): void
    {
        $store->user->notify(
            Notification::make()
                ->icon("heroicon-o-$icon")
                ->status($status)
                ->title($title)
                ->toDatabase()
        );
    }
}
