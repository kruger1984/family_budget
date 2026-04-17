<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories;

use Illuminate\Support\Facades\Blade;

class CategoryIcons
{
    public static function options(): array
    {
        return [
            'heroicon-o-shopping-cart' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-shopping-cart class="w-5 h-5 text-gray-500"/> <span>Продукты / Покупки</span></div>'),
            'heroicon-o-briefcase' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-briefcase class="w-5 h-5 text-gray-500"/> <span>Зарплата / Работа</span></div>'),
            'heroicon-o-truck' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-truck class="w-5 h-5 text-gray-500"/> <span>Транспорт / Авто</span></div>'),
            'heroicon-o-home' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-home class="w-5 h-5 text-gray-500"/> <span>Аренда / Дом</span></div>'),
            'heroicon-o-heart' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-heart class="w-5 h-5 text-gray-500"/> <span>Здоровье</span></div>'),
            'heroicon-o-bolt' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-bolt class="w-5 h-5 text-gray-500"/> <span>Коммуналка</span></div>'),
            'heroicon-o-academic-cap' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-academic-cap class="w-5 h-5 text-gray-500"/> <span>Обучение</span></div>'),
            'heroicon-o-shopping-bag' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-shopping-bag class="w-5 h-5 text-gray-500"/> <span>Одежда / Шопинг</span></div>'),
            'heroicon-o-building-storefront' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-building-storefront class="w-5 h-5 text-gray-500"/> <span>Рестораны / Кафе</span></div>'),
            'heroicon-o-film' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-film class="w-5 h-5 text-gray-500"/> <span>Развлечения / Кино</span></div>'),
            'heroicon-o-gift' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-gift class="w-5 h-5 text-gray-500"/> <span>Подарки</span></div>'),
            'heroicon-o-banknotes' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-banknotes class="w-5 h-5 text-gray-500"/> <span>Сбережения / Копилка</span></div>'),
            'heroicon-o-credit-card' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-credit-card class="w-5 h-5 text-gray-500"/> <span>Кредиты / Долги</span></div>'),
            'heroicon-o-chart-bar' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-chart-bar class="w-5 h-5 text-gray-500"/> <span>Инвестиции / Бизнес</span></div>'),
            'heroicon-o-paper-airplane' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-paper-airplane class="w-5 h-5 text-gray-500"/> <span>Путешествия / Отпуск</span></div>'),
            'heroicon-o-user-group' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-user-group class="w-5 h-5 text-gray-500"/> <span>Семья / Дети</span></div>'),
            'heroicon-o-trophy' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-trophy class="w-5 h-5 text-gray-500"/> <span>Спорт / Хобби</span></div>'),
            'heroicon-o-device-phone-mobile' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-device-phone-mobile class="w-5 h-5 text-gray-500"/> <span>Связь / Интернет</span></div>'),
            'heroicon-o-receipt-percent' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-receipt-percent class="w-5 h-5 text-gray-500"/> <span>Налоги / Комиссии</span></div>'),
            'heroicon-o-wrench-screwdriver' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-wrench-screwdriver class="w-5 h-5 text-gray-500"/> <span>Ремонт / Услуги</span></div>'),
            'heroicon-o-sparkles' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-sparkles class="w-5 h-5 text-gray-500"/> <span>Красота / Уход</span></div>'),
            'heroicon-o-squares-2x2' => Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-squares-2x2 class="w-5 h-5 text-gray-500"/> <span>Разное / Другое</span></div>'),
        ];
    }
}
