<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changePassword')
                ->label('Змінити пароль')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->schema([
                    TextInput::make('password')
                        ->label('Новий пароль')
                        ->password()
                        ->required()
                        ->minLength(6)
                        ->confirmed(),
                    TextInput::make('password_confirmation')
                        ->label('Повторіть пароль')
                        ->password()
                        ->required(),
                ])
                ->action(function (User $record, array $data): void {
                    $record->update([
                        'password' => Hash::make($data['password']),
                    ]);

                    Notification::make()
                        ->title('Пароль успішно змінено')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
