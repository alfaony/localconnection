<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64Image implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if it's a valid Base64 string with an image prefix
        if (preg_match('/^data:image\/(\w+);base64,/', $value, $type)) {
            $data = substr($value, strpos($value, ',') + 1);
            $data = base64_decode($data, true);

            if ($data === false) {
                return false;
            }

            $imageType = strtolower($type[1]); // jpg, png, gif, etc.
            if (!in_array($imageType, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'], true)) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function message()
    {
        return 'The :attribute must be a valid base64-encoded image.';
    }
}

