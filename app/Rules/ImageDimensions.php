<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ImageDimensions implements ValidationRule
{
    /**
     * @param  int  $minWidth  The smallest accepted width in pixels.
     * @param  int  $minHeight  The smallest accepted height in pixels.
     * @param  int  $maxWidth  The largest accepted width in pixels.
     * @param  int  $maxHeight  The largest accepted height in pixels.
     */
    public function __construct(
        private readonly int $minWidth = 1,
        private readonly int $minHeight = 1,
        private readonly int $maxWidth = 4000,
        private readonly int $maxHeight = 4000,
    ) {}

    /**
     * Check that an uploaded image stays inside the accepted pixel bounds.
     *
     * The dimensions are read from the file header, so no image extension is
     * needed and oversized uploads are rejected before they are stored.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            return;
        }

        $path = $value->getRealPath();

        if ($path === false || ! is_file($path)) {
            $fail('The :attribute could not be read.');

            return;
        }

        $dimensions = @getimagesize($path);

        if ($dimensions === false) {
            $fail('The :attribute could not be read as an image.');

            return;
        }

        [$width, $height] = $dimensions;

        if ($width < $this->minWidth || $height < $this->minHeight) {
            $fail("The :attribute must be at least {$this->minWidth} x {$this->minHeight} pixels.");

            return;
        }

        if ($width > $this->maxWidth || $height > $this->maxHeight) {
            $fail("The :attribute must be no larger than {$this->maxWidth} x {$this->maxHeight} pixels.");
        }
    }
}
