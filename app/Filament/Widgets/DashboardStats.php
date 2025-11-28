<?php

namespace App\Filament\Widgets;

use App\Models\Teacher;
use App\Models\Student;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Camera;
use App\Models\Room;
use Illuminate\Support\Facades\Cache;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    // Widget sarlavhasi (paneldagi ustuncha)
    protected ?string $heading = 'Tizim statistikasi';

    // Agar siz polling (avtomatik yangilanish) xohlasangiz:
    // default 5s; o'zgartirish yoki null bilan o'chirish mumkin
    protected static ?string $pollingInterval = '10s';

    // Lazy yuklashni o'chirish uchun: protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Short caching to avoid hitting DB every live poll
        $counts = Cache::remember('dashboard_counts', 30, function () {
            return [
                'teachers' => Teacher::count(),
                'students' => Student::count(),
                'groups'   => Group::count(),
                'lessons'  => Lesson::count(),
                'cameras'  => Camera::count(),
                'rooms'    => Room::count(),
            ];
        });

        return [
            Stat::make('O‘qituvchilar', $counts['teachers'])
                ->description($counts['teachers'] . ' ta')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Talabalar', $counts['students'])
                ->description($counts['students'] . ' ta')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Guruhlar', $counts['groups'])
                ->description($counts['groups'] . ' ta')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Darslar', $counts['lessons'])
                ->description($counts['lessons'] . ' ta')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),

            Stat::make('Kameralar', $counts['cameras'])
                ->description($counts['cameras'] . ' ta')
                ->descriptionIcon('heroicon-m-camera')
                ->color('gray'),

            Stat::make('Xonalar', $counts['rooms'])
                ->description($counts['rooms'] . ' ta')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),
        ];
    }
}
