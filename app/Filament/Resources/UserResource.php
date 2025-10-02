<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 3;

    protected static function getNavigationLabel(): string
    {
        return __('Users');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Configuración');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Card::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nombres')
                                ->required()
                                ->maxLength(100),

                            Forms\Components\TextInput::make('apellidos')
                                ->label('Apellidos')
                                ->required()
                                ->maxLength(100),

                            Forms\Components\TextInput::make('celular')
                                ->label('Celular')
                                ->required()
                                ->tel()
                                ->maxLength(15),

                            Forms\Components\TextInput::make('email')
                                ->label('Correo Electrónico')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            Forms\Components\TextInput::make('password')
                                ->label('Contraseña')
                                ->password()
                                ->maxLength(255)
                                ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                                ->dehydrated(fn ($state) => filled($state))
                                ->required(fn ($livewire) => $livewire instanceof CreateRecord)
                                ->confirmed(),

                            Forms\Components\TextInput::make('password_confirmation')
                                ->label('Confirmar contraseña')
                                ->password()
                                ->maxLength(255)
                                ->dehydrated(false),

                            Forms\Components\Toggle::make('is_active')
                                ->label('¿Usuario activo?')
                                ->default(true),

                            Forms\Components\Select::make('roles')
                                ->label('Cargos')
                                ->required()
                                ->multiple()
                                ->relationship('roles', 'name')
                                ->preload()
                                ->searchable(),

                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Nombre'))
                    ->sortable()
                    ->searchable(),
                /*
                Tables\Columns\TextColumn::make('nombres')
                    ->label(__('Nombres'))
                    ->sortable()
                    ->searchable(),
                */
                Tables\Columns\TextColumn::make('apellidos')
                    ->label(__('Apellidos'))
                    ->sortable()
                    ->searchable(),

                    /*
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email address'))
                    ->sortable()
                    ->searchable(),
*/
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Estado')
                    ->boolean(),

                Tables\Columns\TagsColumn::make('roles.name')
                    ->label(__('Cargo'))
                    ->limit(2),

                    /*
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label(__('Email verified at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
*/
                
                /* Tables\Columns\TextColumn::make('socials')
                    ->label(__('Linked social networks'))
                    ->view('partials.filament.resources.social-icon'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(), */
                
            ])
            
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('')
                    ->icon('heroicon-o-pencil-alt')
                    ->color('primary')
                    ->size('lg')
                    ->url(fn ($record): string => static::getUrl('edit', ['record' => $record]))
                    ->extraAttributes([
                        'title' => 'Editar',
                        'class' => 'hover:bg-primary-50 rounded-full'
                    ]),

                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-s-eye')
                    ->color('secondary'),
           
                Tables\Actions\ViewAction::make(),
                
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
