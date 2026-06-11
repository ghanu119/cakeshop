<?php

namespace App\Models\Concerns;

trait ReleasesUniqueSlugOnSoftDelete
{
    protected static function bootReleasesUniqueSlugOnSoftDelete(): void
    {
        static::deleting(function ($model) {
            if (! $model->isForceDeleting()) {
                $model->releaseSlugForSoftDelete();
            }
        });
    }

    public function releaseSlugForSoftDelete(): void
    {
        $suffix = '-deleted-'.$this->id;

        if (! str_ends_with($this->slug, $suffix)) {
            $this->slug = $this->slug.$suffix;
            $this->saveQuietly();
        }
    }
}
