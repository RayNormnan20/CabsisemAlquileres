<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Permission;
use App\Models\Role;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Forms\Components\View;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-open';

    protected static ?int $navigationSort = 4;

    protected static function getNavigationLabel(): string
    {
        return __('Cargos');
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
                        Forms\Components\Grid::make()
                            ->columns(1)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Permission name'))
                                    ->unique(table: Permission::class, column: 'name')
                                    ->maxLength(255)
                                    ->required(),

                                // Vista personalizada expandible para agrupar los permisos por módulo
                                View::make('filament.resources.role.permissions-tree')
                                    ->viewData([
                                        'permissions' => Permission::orderBy('name')->get(),
                                    ])
                                    ->columnSpanFull(),

                                // Campo oculto para inicializar el estado de permisos en el formulario sin dehidratar al guardar
                                Forms\Components\Hidden::make('permissions')
                                    ->default(fn ($record) => $record?->permissions()->pluck('id')->all() ?? [])
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function (Forms\Components\Hidden $component, $state) {
                                        // Si no hay estado aún, hidratar desde el record para preselección en edición
                                        if (is_array($state) && count($state)) {
                                            return;
                                        }

                                        $livewire = $component->getLivewire();
                                        $record = method_exists($livewire, 'getRecord') ? $livewire->getRecord() : ($livewire->record ?? null);
                                        $component->state($record ? $record->permissions()->pluck('id')->all() : []);
                                    }),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Permission name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TagsColumn::make('permissions.name')
                    ->label(__('Permissions'))
                    ->limit(2),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
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
               // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
