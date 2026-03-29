<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Filament\Tables\Columns\TextColumn;


class AbsentEmployeesTable extends TableWidget
{

    protected function getTableQuery(): Builder
    {
        $today = Carbon::today()->toDateString();

        return User::query()
            ->where('role', 'employee')
            ->whereDoesntHave('attendances', function ($query) use ($today) {
                $query->where('date', $today);
            });
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Nama Karyawan Bolos')
                ->color('danger'),
            TextColumn::make('email')
                ->label('Email'),
        ];
    }
}
