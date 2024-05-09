<?php

namespace App\Filament\Resources\HouseResource\Pages;

use App\Filament\Resources\HouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageHouses extends ManageRecords
{
    protected static string $resource = HouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
