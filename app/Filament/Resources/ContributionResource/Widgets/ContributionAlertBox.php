<?php

namespace App\Filament\Resources\ContributionResource\Widgets;

use KoalaFacade\FilamentAlertBox\Widgets\AlertBoxWidget;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ContributionAlertBox extends AlertBoxWidget
{
    public string | Closure | null $icon = 'heroicon-o-exclamation-triangle';

    /** success, warning, danger, primary */
    public string $type = 'danger';

    protected int | string | array $columnSpan = 'full';

    public Htmlable | Closure | string | null  $label = 'Perhatian!';

    public Htmlable | Closure | string | null $helperText = 'Data ini berada di tempat sampah';

    public function getHelperText(): string | HtmlString | null
    {
        return $this->helperText;
    }

    public function getLabel(): string
    {
        $label = $this->evaluate($this->label) ?? (string) Str::of($this->getName())
            ->beforeLast('.')
            ->afterLast('.')
            ->kebab()
            ->replace(['-', '_'], ' ')
            ->ucfirst();

        return $this->shouldTranslateLabel ? __($label) : $label;
    }
}
