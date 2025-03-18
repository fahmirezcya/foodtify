<?php

namespace App\Filament\Resources\BarcodeResource\Pages;

use App\Filament\Resources\BarcodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBarcode extends ViewRecord
{
    protected static string $resource = BarcodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
