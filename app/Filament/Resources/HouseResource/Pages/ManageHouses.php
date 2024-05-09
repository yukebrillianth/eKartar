<?php

namespace App\Filament\Resources\HouseResource\Pages;

use App\Filament\Resources\HouseResource;
use App\Filament\Resources\HouseResource\Widgets\HouseOverview;
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

    protected function getHeaderWidgets(): array
    {
        return [
            HouseOverview::class,
        ];
    }
}
