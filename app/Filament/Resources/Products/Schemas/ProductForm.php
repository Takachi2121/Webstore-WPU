<?php

namespace App\Filament\Resources\Products\Schemas;

use Faker\Provider\Lorem;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                SpatieMediaLibraryFileUpload::make('cover')
                    ->collection('cover')
                    ->disk('public'),
                SpatieMediaLibraryFileUpload::make('gallery')
                    ->collection('gallery')
                    ->disk('public')
                    ->multiple(),
                TextInput::make('name')
                    ->label('Product Name'),
                TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true),
                TextInput::make('slug')
                    ->label('Slug')
                    ->unique(ignoreRecord: true),
                SpatieTagsInput::make('tags')
                    ->label('Collection')
                    ->type('Collection'),
                TextInput::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->default(0),
                TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->prefix('Rp.'),
                TextInput::make('weight')
                    ->label('Weight')
                    ->numeric()
                    ->suffix('gram'),
                MarkdownEditor::make('description')
                    ->label('Description'),
            ])->columnSpanFull(),
        ]);
    }
}

