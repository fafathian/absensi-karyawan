<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\CreateAttendance;
use App\Filament\Resources\Attendances\Pages\EditAttendance;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

// 3 Baris ini yang akan menghilangkan garis merah di Actions
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    // Perbaikan tipe data untuk menampung BackedEnum
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Rekap Absensi';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Nama Karyawan')
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->required(),
                Forms\Components\TimePicker::make('clock_in_time')
                    ->label('Jam Masuk'),
                Forms\Components\TimePicker::make('clock_out_time')
                    ->label('Jam Keluar'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clock_in_time')
                    ->label('Jam Masuk')
                    ->time(),

                Tables\Columns\TextColumn::make('clock_in_location')
                    ->label('Lokasi Masuk')
                    ->getStateUsing(function (Attendance $record) {
                        return $record->clock_in_latitude ? '📍 Buka Maps' : 'Belum Absen';
                    })
                    ->url(function (Attendance $record) {
                        if ($record->clock_in_latitude && $record->clock_in_longitude) {
                            return "https://maps.google.com/?q={$record->clock_in_latitude},{$record->clock_in_longitude}";
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '📍 Buka Maps' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('clock_out_time')
                    ->label('Jam Keluar')
                    ->time(),

                Tables\Columns\TextColumn::make('clock_out_location')
                    ->label('Lokasi Keluar')
                    ->getStateUsing(function (Attendance $record) {
                        return $record->clock_out_latitude ? '📍 Buka Maps' : 'Belum Absen';
                    })
                    ->url(function (Attendance $record) {
                        if ($record->clock_out_latitude && $record->clock_out_longitude) {
                            return "https://maps.google.com/?q={$record->clock_out_latitude},{$record->clock_out_longitude}";
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '📍 Buka Maps' => 'warning',
                        default => 'gray',
                    }),
                // Tables\Columns\TextColumn::make('clock_in_latitude')
                //     ->label('Lokasi Masuk')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('clock_in_longitude')
                //     ->label('Lokasi Masuk')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('clock_out_latitude')
                //     ->label('Lokasi Keluar')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('clock_out_longitude')
                //     ->label('Lokasi Keluar')
                //     ->searchable(),
            ])
            ->filters([
                // Nanti kita bisa tambahkan filter bulan di sini
            ])
            ->actions([
                // Kosongkan sementara agar tidak error
            ])
            ->bulkActions([
                // Kosongkan sementara agar tidak error
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendances::route('/'),
            'create' => CreateAttendance::route('/create'),
            'edit' => EditAttendance::route('/{record}/edit'),
        ];
    }
}
