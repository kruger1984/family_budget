<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Support\ValueObjects\Money;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TransactionForm
{
    public static function getSingleTransactionSchema(TransactionType $type): array
    {
        return [
            TextEntry::make('operation_type')
                ->label('Operation type')
                ->state($type->name),

            Select::make('user_id')
                ->relationship('user', 'name')
                ->label('User')
                ->default(auth()->id())
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set): mixed => $set('account_id', null)),

            Select::make('account_id')
                ->relationship(
                    name: 'account',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query, Get $get): Builder => self::filterAccountsByUser(
                        $query,
                        $get
                    )
                )
                ->label('Account')
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set, ?string $state) => self::autoFillCurrency($set, $state, 'currency')),
            TextEntry::make('balance_before')
                ->label('Balance before')
                ->state(function (Get $get): string {
                    $accountId = $get('account_id');

                    if (! $accountId) {
                        return '-';
                    }

                    /** @var Account|null $account */
                    $account = Account::query()->find($accountId);
                    if (! $account) {
                        return '-';
                    }

                    $balanceObj = $account->balance;
                    $balance = $balanceObj->raw() / 100;

                    /** @var Currency|string $currencyObj */
                    $currencyObj = $account->currency;
                    $currency = $currencyObj instanceof Currency ? $currencyObj->value : (string) $currencyObj;

                    return "$balance $currency";
                }),
            self::getAmountComponent(),

            Select::make('currency')
                ->options(Currency::class)
                ->label('Currency')
                ->required()
                ->disabled()
                ->dehydrated(),

            Select::make('category_id')
                ->relationship(
                    name: 'category',
                    titleAttribute: 'name',
                    modifyQueryUsing: function (Builder $query, Get $get) {
                        $userId = $get('user_id');

                        return $query
                            ->whereDoesntHave('children')
                            ->where(function (Builder $q) use ($userId): void {
                                $q->whereNull('family_id');

                                if ($userId) {
                                    $q->orWhereHas('family', function (Builder $f) use ($userId): void {
                                        $f->whereHas('members', function (Builder $m) use ($userId): void {
                                            $m->where('users.id', $userId);
                                        });
                                    });
                                }
                            });
                    }
                )
                ->label('Category')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('description')
                ->label('Description'),
        ];
    }

    public static function getTransferWizardSteps(): array
    {
        return [
            Step::make('Sender')
                ->description('Which account are we debiting from?')
                ->icon('heroicon-o-arrow-up-right')
                ->schema([
                    Select::make('user_id')
                        ->relationship('user', 'name')
                        ->label('User')
                        ->default(auth()->id())
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set): mixed => $set('account_id', null)),

                    Select::make('account_id')
                        ->relationship(
                            name: 'account',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query, Get $get): Builder => self::filterAccountsByUser(
                                $query,
                                $get
                            )
                        )
                        ->getOptionLabelFromRecordUsing(
                            fn (Account $record): string => self::formatAccountLabel($record)
                        )
                        ->label("Sender's account")
                        ->required()
                        ->live()
                        ->afterStateUpdated(
                            fn (Set $set, ?string $state) => self::autoFillCurrency($set, $state, 'currency')
                        ),

                    self::getAmountComponent('amount', 'Write-off amount')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get): void {
                            self::calculateCrossRate($set, $get);
                        }),

                    Select::make('currency')
                        ->options(Currency::class)
                        ->label('Currency')
                        ->required()
                        ->disabled()
                        ->dehydrated(),
                ]),

            Step::make('Recipient')
                ->description('Which account should we credit this to?')
                ->icon('heroicon-o-arrow-down-left')
                ->schema([
                    Select::make('target_account_id')
                        ->relationship(
                            name: 'targetAccount',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query, Get $get): Builder => self::filterAccountsByUser(
                                $query,
                                $get
                            )
                        )
                        ->getOptionLabelFromRecordUsing(
                            fn (Account $record): string => self::formatAccountLabel($record)
                        )
                        ->label("Recipient's account")
                        ->different('account_id')
                        ->required()
                        ->live()
                        ->afterStateUpdated(
                            fn (Set $set, ?string $state) => self::autoFillCurrency($set, $state, 'target_currency')
                        ),
                    TextInput::make('exchange_rate')
                        ->label('Exchange rate')
                        ->numeric()
                        ->dehydrated(false)
                        ->visible(
                            fn (Get $get): bool => $get('currency') !== $get('target_currency') && filled(
                                $get('target_currency')
                            )
                        )
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                            $amount = (float) $get('amount');
                            $rate = (float) $state;
                            if ($amount > 0 && $rate > 0) {
                                $set('target_amount', round($amount * $rate, 2));
                            }
                        }),

                    self::getAmountComponent(
                        'target_amount',
                        'Credit amount (auto-calculated if rate is set)',
                        'target_currency'
                    )
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get): void {
                            self::calculateCrossRate($set, $get);
                        }),

                    Select::make('target_currency')
                        ->options(Currency::class)
                        ->label('Target currency')
                        ->required()
                        ->disabled()
                        ->dehydrated(),
                ]),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options(TransactionType::class)
                    ->label('Type of transaction')
                    ->disabled()
                    ->dehydrated(false),

                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Initiator')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set): void {
                        $set('account_id', null);
                        $set('target_account_id', null);
                    }),

                Select::make('account_id')
                    ->relationship(
                        name: 'account',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, Get $get): Builder => self::filterAccountsByUser(
                            $query,
                            $get
                        )
                    )
                    ->label(
                        fn (?Transaction $record
                        ): string => $record?->type === TransactionType::Transfer ? "Sender's account" : 'Account'
                    )
                    ->required()
                    ->live()
                    ->afterStateUpdated(
                        fn (Set $set, ?string $state) => self::autoFillCurrency($set, $state, 'currency')
                    ),

                self::getAmountComponent(),

                Select::make('currency')
                    ->options(Currency::class)
                    ->label('Currency')
                    ->required()
                    ->disabled()
                    ->dehydrated(),

                Group::make([
                    Select::make('target_account_id')
                        ->relationship(
                            name: 'targetAccount',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query, Get $get): Builder => self::filterAccountsByUser(
                                $query,
                                $get
                            )
                        )
                        ->label("Recipient's account")
                        ->different('account_id')
                        ->required(fn (?Transaction $record): bool => $record?->type === TransactionType::Transfer)
                        ->live()
                        ->afterStateUpdated(
                            fn (Set $set, ?string $state) => self::autoFillCurrency($set, $state, 'target_currency')
                        ),

                    self::getAmountComponent(
                        'target_amount',
                        'Credit amount (if in a different currency)',
                        'target_currency'
                    )
                        ->required(fn (?Transaction $record): bool => $record?->type === TransactionType::Transfer),

                    Select::make('target_currency')
                        ->options(Currency::class)
                        ->label('Target currency')
                        ->required(fn (?Transaction $record): bool => $record?->type === TransactionType::Transfer)
                        ->disabled()
                        ->dehydrated(),

                ])->visible(fn (?Transaction $record): bool => $record?->type === TransactionType::Transfer),

                Select::make('category_id')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get) {
                            $userId = $get('user_id');

                            return $query
                                ->whereDoesntHave('children')
                                ->where(function (Builder $q) use ($userId): void {
                                    $q->whereNull('family_id');

                                    if ($userId) {
                                        $q->orWhereHas('family', function (Builder $f) use ($userId): void {
                                            $f->whereHas('members', function (Builder $m) use ($userId): void {
                                                $m->where('users.id', $userId);
                                            });
                                        });
                                    }
                                });
                        }
                    )
                    ->label('Category')
                    ->searchable()
                    ->preload(),

                TextInput::make('description')
                    ->label('Details'),
            ]);
    }

    private static function getAmountComponent(
        string $name = 'amount',
        string $label = 'Sum',
        string $currencyField = 'currency'
    ): TextInput {
        return TextInput::make($name)
            ->label($label)
            ->required()
            ->formatStateUsing(function ($state) {
                if ($state instanceof Money) {
                    return $state->raw() / 100;
                }

                return $state;
            })
            ->dehydrateStateUsing(function (string|int|float $state, Get $get) use ($currencyField): Money {
                $currencyValue = $get($currencyField);

                $currency = $currencyValue instanceof Currency
                    ? $currencyValue
                    : Currency::tryFrom((string) $currencyValue) ?? Currency::UAH;

                $amountInCents = (int) round((float) $state * 100);

                return new Money($amountInCents, $currency);
            })
            ->default(0);
    }

    private static function filterAccountsByUser(Builder $query, Get $get): Builder
    {
        $userId = $get('user_id');

        if (! $userId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($userId): void {
            $q->where('user_id', $userId)
                ->orWhereHas('family', function (Builder $q) use ($userId): void {
                    $q->whereHas('members', function (Builder $q) use ($userId): void {
                        $q->where('users.id', $userId);
                    });
                });
        });
    }

    private static function autoFillCurrency(Set $set, ?string $accountId, string $currencyField): void
    {
        if (! $accountId) {
            $set($currencyField, null);

            return;
        }

        /** @var Account|null $account */
        $account = Account::query()->find($accountId);
        if ($account) {
            /** @var Currency|string $currencyObj */
            $currencyObj = $account->currency;

            $set($currencyField, $currencyObj instanceof Currency ? $currencyObj->value : (string) $currencyObj);
        }
    }

    private static function formatAccountLabel(Account $record): string
    {
        /** @var Money $balanceObj */
        $balanceObj = $record->balance;
        $balance = $balanceObj->raw() / 100;

        /** @var Currency|string $currencyObj */
        $currencyObj = $record->currency;
        $currency = $currencyObj instanceof Currency ? $currencyObj->value : (string) $currencyObj;

        return "$record->name (Balance: $balance $currency)";
    }

    private static function calculateCrossRate(Set $set, Get $get): void
    {
        $amount = (float) $get('amount');
        $targetAmount = (float) $get('target_amount');

        if ($amount > 0 && $targetAmount > 0 && $get('currency') !== $get('target_currency')) {
            $set('exchange_rate', round($targetAmount / $amount, 4));
        }
    }
}
