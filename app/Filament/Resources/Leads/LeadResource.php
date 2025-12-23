<?php

namespace App\Filament\Resources\Leads;

use App\Filament\Resources\Leads\Pages\CreateLead;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Filament\Resources\Leads\RelationManagers\InquiriesRelationManager;
use App\Filament\Resources\Leads\RelationManagers\MaintenanceContractRelationManager;
use App\Filament\Resources\Leads\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Leads\RelationManagers\QuotesRelationManager;
use App\Filament\Resources\Leads\Schemas\LeadForm;
use App\Filament\Resources\Leads\Schemas\LeadInfolist;
use App\Filament\Resources\Leads\Tables\LeadsTable;
use App\Models\Lead;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeadInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InquiriesRelationManager::class,
            QuotesRelationManager::class,
            PaymentsRelationManager::class,
            MaintenanceContractRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'create' => CreateLead::route('/create'),
            'view' => ViewLead::route('/{record}'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }

    public static function statuses(): array
    {
        return [
            'new' => 'New',
            'contacted' => 'Contacted',
            'quoted' => 'Quoted',
            'won' => 'Won',
            'lost' => 'Lost',
        ];
    }

    public static function sources(): array
    {
        return [
            'manual' => 'Manual',
            'website' => 'Website',
            'product' => 'Product',
        ];
    }
}
