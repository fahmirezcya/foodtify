<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FoodResource\Pages;
use App\Filament\Resources\FoodResource\RelationManagers;
use App\Models\Food;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Collection;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FoodResource extends Resource
{
    protected static ?string $model = Food::class;
    protected static ?string $label = 'Produk';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make([
                    Section::make('Info Produk')
                        ->schema([

                            Forms\Components\Select::make('category_id')
                                ->label('Kategori')
                                ->placeholder('Pilih Kategori')
                                ->relationship('category', 'name')
                                ->native(false)
                                ->preload()
                                ->searchable()
                                ->required(),

                            Forms\Components\TextInput::make('name')
                                ->label('Nama Produk')
                                ->placeholder('Masukkan Nama')
                                ->minLength(5)
                                ->maxLength(45)
                                ->required(),


                            Forms\Components\TextInput::make('price')
                                ->label('Harga')
                                ->placeholder('Masukkan Harga')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters(',')
                                ->numeric()
                                ->step(1000)
                                ->minValue(1000)
                                ->maxValue(99999)
                                ->prefix('Rp')
                                ->suffix('.00')
                                ->columnSpanFull()
                                ->live(50)
                                ->afterStateUpdated(
                                    fn($state, Set $set, Get $get) =>
                                    $set(
                                        'price_afterdiscount',
                                        $get('percent') !== null && $get('price') // Pastikan percent dan price terisi
                                        ? number_format(
                                            (int) str_replace(',', '', (string) $get('price')) -
                                            ((int) str_replace(',', '', (string) $get('price')) * ((float) str_replace(',', '.', (string) $get('percent')) / 100)),
                                            0,
                                            '.',
                                            ','
                                        )
                                        : null
                                    )
                                )
                                ->required(),
                        ])
                        ->columns(2)
                        ->columnSpan(1),

                    Section::make('Deskripsi Produk')
                        ->schema([
                            Forms\Components\RichEditor::make('description')
                                ->label('')
                                ->placeholder('Masukkan Deskripsi')
                                ->minLength(10)
                                ->required(),
                        ])->columnSpan(1),
                ]),

                Group::make([
                    Section::make('Promo')
                        ->schema([
                            Forms\Components\Toggle::make('is_ready')
                                ->label('Ketersediaan')
                                ->inline(false)
                                ->default(true)
                                ->required(),
                            Forms\Components\Toggle::make('is_promo')
                                ->label('Apakah Promo?')
                                ->inline(false)
                                ->live()
                                ->afterStateUpdated(
                                    fn($state, Set $set) =>
                                    $state
                                    ? null // Jika true, biarkan nilai tetap
                                    : ($set('percent', '') & $set('price_afterdiscount', '')) // Jika false, set nilai menjadi null
                                )
                                ->default(false)
                                ->required(),

                            Forms\Components\TextInput::make('percent')
                                ->label('Diskon')
                                ->placeholder('Angka')
                                ->live(onBlur: 50)
                                ->afterStateUpdated(
                                    fn($state, Set $set, Get $get) =>
                                    $set(
                                        'price_afterdiscount',
                                        $get('price')
                                        ? number_format(
                                            (int) str_replace(',', '', $get('price')) - ((int) str_replace(',', '', $get('price')) * ((float) str_replace(',', '.', $state) / 100)),
                                            0,
                                            '.',
                                            ','
                                        )
                                        : null
                                    )
                                )
                                ->visible(fn(Get $get): bool => $get('is_promo'))
                                ->dehydratedWhenHidden()
                                ->maxLength(4)
                                ->suffix('%'),

                            Forms\Components\TextInput::make('price_afterdiscount')
                                ->label('Harga Setelah Diskon')
                                ->placeholder('Otomatis Terisi')
                                ->disabled()
                                ->dehydrated()
                                ->dehydratedWhenHidden()
                                ->visible(fn(Get $get): bool => $get('is_promo'))
                                ->maxLength(20)
                                ->prefix('Rp')
                                ->suffix('.00')
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->columnSpan(1),

                    Section::make('Gambar Produk')
                        ->schema([
                            Repeater::make('foodImages')
                                ->label('')
                                ->relationship('foodImages')
                                ->schema([
                                    FileUpload::make('images')
                                        ->label('')
                                        ->image()
                                        ->imageEditor()
                                        ->imageEditorAspectRatios([
                                            '1:1',
                                        ])
                                        ->imageCropAspectRatio('1:1')
                                        ->directory('produk')
                                        ->visibility('public')
                                        ->helperText('Format yang didukung: JPG, PNG, atau GIF.')
                                        ->required(),
                                ])->grid(2)
                        ])
                ])
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foodImages.images')
                    ->label('Gambar')
                    ->circular()
                    ->stacked()
                    ->ring(5)
                    ->limit(3),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_ready')
                    ->label('Ketersediaan')
                    ->badge()
                    ->icon(fn(int $state): string => match ($state) {
                        0 => 'heroicon-o-x-circle',
                        1 => 'heroicon-o-check-circle',
                    })
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        0 => 'Tidak Tersedia',
                        1 => 'Tersedia',
                        default => 'Status Tidak Diketahui',
                    })
                    ->color(fn(int $state): string => match ($state) {
                        0 => 'danger',
                        1 => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_promo')
                    ->label('Promo')
                    ->alignment(Alignment::Center)
                    ->boolean(),
                Tables\Columns\TextColumn::make('percent')
                    ->label('Diskon (%)')
                    ->default(fn($record) => is_null($record->percent) ? '-' : null)
                    ->suffix(fn($record) => is_null($record->percent) ? null : '%')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(
                        fn($record) =>
                        !is_null($record->price) && $record->price !== ''
                        ? 'Rp ' . number_format((float) str_replace(',', '', $record->price), 0, ',', ',')
                        : null
                    )
                    ->suffix('.00')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_afterdiscount')
                    ->label('Harga Setelah Diskon')
                    ->default(fn($record) => is_null($record->price_afterdiscount) ? 'Tidak Ada Diskon' : null)
                    ->prefix(fn($record) => is_null($record->price_afterdiscount) ? null : 'Rp ')
                    ->suffix(fn($record) => is_null($record->price_afterdiscount) ? null : '.00')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->placeholder('Pilih Kategori')
                    ->relationship('category', 'name')
                    ->native(false)
                    ->preload()
                    ->searchable(),
                SelectFilter::make('is_ready')
                    ->label('Ketersediaan')
                    ->placeholder('Pilih Status')
                    ->options([
                        true => 'Tersedia',
                        false => 'Tidak Tersedia',
                    ])
                    ->native(false)
                    ->preload()
                    ->searchable(),
                SelectFilter::make('is_promo')
                    ->label('Promo')
                    ->placeholder('Pilih Status')
                    ->options([
                        true => 'Ada',
                        false => 'Tidak Ada',
                    ])
                    ->native(false)
                    ->preload()
                    ->searchable(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ])
                    ->icon('heroicon-o-ellipsis-horizontal-circle')
                    ->color('info')
                    ->tooltip('Aksi')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('bulk_ketersediaan')
                        ->label('Ubah Ketersediaan')
                        ->icon('heroicon-s-pencil-square')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Ubah Status')
                        ->modalDescription('Apakah Anda yakin ingin mengubah status ketersediaan?')
                        ->modalButton('Ya, Ubah')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'is_ready' => !$record->is_ready
                                ]);
                            }
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFood::route('/'),
            'create' => Pages\CreateFood::route('/create'),
            'view' => Pages\ViewFood::route('/{record}'),
            'edit' => Pages\EditFood::route('/{record}/edit'),
        ];
    }
}
