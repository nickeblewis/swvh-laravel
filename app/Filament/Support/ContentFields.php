<?php

namespace App\Filament\Support;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\NotIn;

/**
 * Form fields activities and pages have in common.
 */
class ContentFields
{
    /**
     * A title field that fills in the slug as you type, on new records only.
     * The slug is not changed when the title changes later.
     */
    public static function title(): TextInput
    {
        return TextInput::make('title')
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(function (string $operation, ?string $state, callable $set): void {
                if ($operation === 'create') {
                    $set('slug', Str::slug((string) $state));
                }
            });
    }

    /**
     * @param  list<string>  $reserved  slugs the site already routes
     */
    public static function slug(string $prefix, array $reserved = []): TextInput
    {
        $field = TextInput::make('slug')
            ->label('Web address')
            ->prefix($prefix)
            ->required()
            ->regex('/^[a-z0-9-]+$/')
            ->validationMessages([
                'regex' => 'Use lower-case letters, numbers and dashes only.',
                'not_in' => 'This address is used by a built-in page.',
            ])
            ->maxLength(255)
            ->unique(ignoreRecord: true)
            ->helperText('Changing this breaks links to the page.');

        return $reserved === [] ? $field : $field->rule(new NotIn($reserved));
    }

    public static function body(): MarkdownEditor
    {
        return MarkdownEditor::make('body')
            ->required()
            ->columnSpanFull();
    }

    public static function heroImage(): Section
    {
        return Section::make('Image')->columns(2)->schema([
            FileUpload::make('hero_image')
                ->label('Image')
                ->image()
                // SVG is left out on purpose: it can carry script.
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'])
                ->maxSize(5 * 1024)
                ->disk('public')
                ->directory('heroes')
                ->imageEditor()
                ->helperText(fn ($record): ?string => blank($record?->hero_image) && filled($record?->hero_image_path)
                    ? "Currently showing {$record->hero_image_path}. Upload an image to replace it."
                    : null),
            TextInput::make('hero_image_alt')
                ->label('Describe the image')
                ->helperText('For people who cannot see it. Uses the title if left empty.')
                ->maxLength(255),
        ]);
    }

    public static function publishing(): Section
    {
        return Section::make('Publishing')->columns(3)->schema([
            Toggle::make('draft')
                ->helperText('Drafts are not shown on the site.')
                ->inline(false),
            DateTimePicker::make('published_at')
                ->label('Publish from')
                ->helperText('Optional. Leave empty to publish straight away.')
                ->seconds(false),
            TextInput::make('position')
                ->numeric()
                ->integer()
                ->default(0)
                ->required()
                ->helperText('Lower numbers come first.'),
        ]);
    }

    public static function sourceUrl(): TextInput
    {
        return TextInput::make('source_url')
            ->label('Source URL')
            ->url()
            ->helperText('For reference only; not shown on the site.');
    }
}
