<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Resources\UserResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Data Karyawan';
    protected static ?string $recordTitleAttribute = 'user';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create')
                    ->label('Password (kosongkan jika tidak ingin diubah)'),

                Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'employee' => 'Karyawan',
                    ])
                    ->required()
                    ->default('employee'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'employee' => 'success',
                        default => 'gray', // Jika isinya di luar admin/employee
                    }),
                Tables\Columns\IconColumn::make('device_id')
                    ->label('Device Bound')
                    ->boolean() // Akan jadi centang hijau jika ada isinya, silang merah jika null
                    ->trueIcon('heroicon-o-device-phone-mobile')
                    ->falseIcon('heroicon-o-x-circle')
                    ->getStateUsing(fn($record) => filled($record->device_id)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\Action::make('resetDevice')
                    ->label('Reset Device')
                    ->color('warning')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->requiresConfirmation()
                    // Tambahkan backslash dan path model lengkap
                    ->action(fn(\App\Models\User $record) => $record->update(['device_id' => null]))
                    ->successNotificationTitle('Device ID berhasil direset!'),

                // Tambahkan action bawaan agar tetap bisa Edit/Delete jika mau
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
