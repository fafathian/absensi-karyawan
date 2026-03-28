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

use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;


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
                // --- FITUR BARU: FILTER BULAN & TAHUN ---
                Filter::make('filter_bulan_tahun')
                    ->form([
                        Forms\Components\Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                '01' => 'Januari',
                                '02' => 'Februari',
                                '03' => 'Maret',
                                '04' => 'April',
                                '05' => 'Mei',
                                '06' => 'Juni',
                                '07' => 'Juli',
                                '08' => 'Agustus',
                                '09' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ]),
                        Forms\Components\Select::make('tahun')
                            ->label('Tahun')
                            ->options([
                                '2024' => '2024',
                                '2025' => '2025',
                                '2026' => '2026',
                                '2027' => '2027',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['bulan'],
                                fn(Builder $query, $bulan): Builder => $query->whereMonth('date', $bulan),
                            )
                            ->when(
                                $data['tahun'],
                                fn(Builder $query, $tahun): Builder => $query->whereYear('date', $tahun),
                            );
                    })
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction::make()
                    ->label('Export ke Excel')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->exports([
                        \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                            ->withColumns([
                                // NOMOR URUT: Menggunakan format penulisan yang lebih simpel
                                \pxlrbt\FilamentExcel\Columns\Column::make('id')
                                    ->heading('No')
                                    ->formatStateUsing(function ($state, $record) {
                                        static $no = 0;

                                        if (!$record || !$record->id) {
                                            return null;
                                        }

                                        return ++$no;
                                    }),

                                // 1. Identitas & Tanggal
                                \pxlrbt\FilamentExcel\Columns\Column::make('user.name')
                                    ->heading('Nama Karyawan'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('date')
                                    ->heading('Tanggal'),

                                // 2. Data Masuk
                                \pxlrbt\FilamentExcel\Columns\Column::make('clock_in_time')
                                    ->heading('Jam Masuk'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('clock_in_latitude')
                                    ->heading('Lat Masuk'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('clock_in_longitude')
                                    ->heading('Long Masuk'),

                                // 3. Data Keluar
                                \pxlrbt\FilamentExcel\Columns\Column::make('clock_out_time')
                                    ->heading('Jam Keluar'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('clock_out_latitude')
                                    ->heading('Lat Keluar'),
                                \pxlrbt\FilamentExcel\Columns\Column::make('clock_out_longitude')
                                    ->heading('Long Keluar'),
                            ]),
                    ]),
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
