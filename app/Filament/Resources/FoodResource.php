<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FoodResource\Pages;
use App\Filament\Resources\FoodResource\RelationManagers;
use App\Models\Food;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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

                Section::make('Promo')
                    ->schema([
                        Forms\Components\Toggle::make('is_promo')
                            ->label('Apakah Promo?')
                            ->inline(false)
                            ->live()
                            ->default(false)
                            ->required(),

                        Forms\Components\TextInput::make('percent')
                            ->label('Persentase Diskon')
                            ->placeholder('Masukkan Angka')
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
                            ->maxLength(4)
                            ->suffix('%'),

                        Forms\Components\TextInput::make('price_afterdiscount')
                            ->label('Harga Setelah Diskon')
                            ->placeholder('Otomatis Terisi')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn(Get $get): bool => $get('is_promo'))
                            ->maxLength(20)
                            ->prefix('Rp')
                            ->suffix('.00')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(1),

                Section::make('Deskripsi Produk')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('')
                            ->placeholder('Masukkan Deskripsi')
                            ->minLength(10)
                            ->columnSpanFull()
                            ->required(),
                    ])
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->numeric()
                    ->sortable(),
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
                Tables\Columns\IconColumn::make('is_promo')
                    ->label('Promosi')
                    ->alignment(Alignment::Center)
                    ->boolean(),
                Tables\Columns\TextColumn::make('percent')
                    ->label('Diskon (%)')
                    ->default(fn($record) => is_null($record->percent) ? '-' : null)
                    ->suffix(fn($record) => is_null($record->percent) ? null : '%')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price_afterdiscount')
                    ->label('Harga Setelah Diskon')
                    ->default(fn($record) => is_null($record->price_afterdiscount) ? 'Tidak Ada Diskon' : null)
                    ->prefix(fn($record) => is_null($record->price_afterdiscount) ? null : 'Rp ')
                    ->suffix(fn($record) => is_null($record->price_afterdiscount) ? null : '.00')
                    ->searchable(),
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
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
