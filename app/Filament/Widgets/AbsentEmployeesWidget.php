<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AbsentEmployeesWidget extends BaseWidget
{
    // Agar widget otomatis refresh setiap 30 detik
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $today = Carbon::today()->toDateString();

        // 1. Total Karyawan (bukan admin)
        $totalEmployees = User::where('role', 'employee')->count();

        // 2. Karyawan yang sudah absen masuk hari ini
        $presentCount = Attendance::where('date', $today)
            ->whereNotNull('clock_in_time')
            ->count();

        // 3. Karyawan yang BELUM absen
        $absentCount = $totalEmployees - $presentCount;

        return [
            Stat::make('Karyawan Hadir', $presentCount)
                ->description('Sudah melakukan Clock In')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Belum Absen Hari Ini', $absentCount)
                ->description('Karyawan yang belum terlihat')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($absentCount > 0 ? 'danger' : 'gray')
                ->chart([$absentCount, $totalEmployees]), // Grafik simpel
        ];
    }
}
