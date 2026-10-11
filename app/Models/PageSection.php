<?php

namespace App\Models;

use App\Support\SectionTypes;
use Database\Factories\PageSectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    /** @use HasFactory<PageSectionFactory> */
    use HasFactory;

    protected $fillable = ['page_id', 'type', 'data', 'is_enabled', 'sort_order'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_enabled' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /** Saved values on top of the type's defaults, so new options never break old sections. */
    public function values(): array
    {
        return array_replace(SectionTypes::defaults($this->type), $this->data ?? []);
    }

    public function label(): string
    {
        return SectionTypes::get($this->type)['label'] ?? ucfirst($this->type);
    }

    /** A short line to recognise the section by in the admin list. */
    public function summary(): string
    {
        $values = $this->values();
        $layout = $this->type === 'hero' ? (SectionTypes::get('hero')['fields']['layout']['options'][$values['layout']] ?? '').' · ' : '';

        return $layout.($values['title'] ?? '');
    }
}
