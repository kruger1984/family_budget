<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Auth\Pages\Login;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class CustomLogin extends Login
{
    protected function getFormActions(): array
    {
        $actions = parent::getFormActions();

        if (app()->environment('local', 'testing')) {
            $actions[] = $this->getTestLoginAction('loginAsAdmin', 'Вход: Глава семьи', 'admin@example.com', 'danger');
            $actions[] = $this->getTestLoginAction('loginAsMember', 'Вход: Участник', 'member@example.com', 'gray');
        }

        return $actions;
    }

    protected function getTestLoginAction(string $name, string $label, string $email, string $color): Action
    {
        return Action::make($name)
            ->label($label)
            ->link()
            ->color($color)
            ->extraAttributes(['type' => 'button'])
            ->action(function () use ($email) {

                $user = User::query()->where('email', $email)->first();

                if (! $user) {
                    Notification::make()
                        ->title('Тестовый пользователь не найден')
                        ->body("Пользователь с email $email отсутствует в БД.")
                        ->danger()
                        ->send();

                    return null;
                }

                /** @var Authenticatable $user */
                Auth::login($user);

                return redirect()->intended(filament()->getUrl());
            });
    }
}
