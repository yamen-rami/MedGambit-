<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasPublicImage
{
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $image = $this->attributes['image'] ?? null;

            if (! is_string($image) || $image === '') {
                return null;
            }

            if (Str::startsWith($image, ['http://', 'https://', '//'])) {
                return $image;
            }

            $path = ltrim($image, '/');

            if (Str::startsWith($path, ['assets/', 'storage/'])) {
                return asset($path);
            }

            return Storage::disk('public')->url($path);
        });
    }
}
