<?php

use App\Domains\TitanMoney\ValueObjects\Money;

use App\Helpers\FileUploader;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;


if (!function_exists('apiResponse')) {
    function apiResponse(
        bool   $success,
        string $message,
               $data,
        int    $statusCode = 200
    )
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if ($success) {
            $response['data'] = $data;
        } else {
            $response['errors'] = $data;
        }

        return response()->json($response, $statusCode);
    }
}

if (!function_exists('responseSuccess')) {
    function responseSuccess(string $message = 'Success', $data = [], int $statusCode = 200)
    {
        return apiResponse(true, $message, $data, $statusCode);
    }
}

if (!function_exists('responseError')) {
    function responseError(string $message = 'Error', $errors = [], int $statusCode = 400)
    {
        return apiResponse(false, $message, $errors, $statusCode);
    }
}


if (!function_exists('uploadFile')) {
    function uploadFile($file, $path, $width = null, $height = null, $old = null)
    {
        return (new FileUploader)->uploadFile($file, $path, $width, $height, $old);
    }
}

if (!function_exists('deleteFile')) {
    function deleteFile($filePath)
    {
        return (new FileUploader)->deleteFile($filePath);
    }
}

if (!function_exists('uploadThumbnail')) {
    function uploadThumbnail($file, $path)
    {
        return (new FileUploader)->uploadThumbnail($file, $path);
    }
}

if (!function_exists('deleteThumbnail')) {
    function deleteThumbnail($filePath)
    {
        return (new FileUploader)->deleteThumbnail($filePath);
    }
}

if (!function_exists('getImageUrl')) {
    function getImageUrl($filePath, $default = null)
    {
        $default = $default ?? asset('images/default.png');
        if (!$filePath) {
            return $default;
        }
        return asset('storage/' . $filePath) ?? $default;
    }
}


// date format
if (!function_exists('dateFormat')) {
    function dateFormat($date)
    {
        return date('d-m-Y', strtotime($date));
    }
}


if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        $setting = Setting::where('name', $key)->first();
        if (! $setting) {
            return $default;
        }

        $value = $setting->value;
        if (is_string($value) && str_starts_with($value, 'encrypted:')) {
            try {
                return Crypt::decryptString(substr($value, strlen('encrypted:')));
            } catch (Throwable $exception) {
                Log::warning('Unable to decrypt a protected setting.', [
                    'setting' => $key,
                    'exception' => $exception::class,
                ]);

                return $default;
            }
        }

        return $value;
    }
}


if (!function_exists('avatar')) {
    function avatar($name = 'o', $imageUrl = null): string
    {
        if (!blank($imageUrl)) {
            return getImageUrl($imageUrl);
        }

        $themeColor = setting('theme_secondary_color', '#724ff9');
        $colorCode = str_replace('#', '', $themeColor);
        return "https://ui-avatars.com/api/?name=$name&background=$colorCode&color=fff";
    }
}




if (! function_exists('formatMoneyMinor')) {
    function formatMoneyMinor(int|string|null $minor, ?string $currency = 'AUD', ?string $locale = null): string
    {
        $currencyCode = strtoupper(trim((string) ($currency ?: 'AUD')));
        if (! preg_match('/^[A-Z]{3}$/', $currencyCode)) {
            $currencyCode = 'AUD';
        }

        $resolvedLocale = str_replace('-', '_', $locale ?: (string) config('app.locale', 'en_AU'));

        return (new Money((int) ($minor ?? 0), $currencyCode))->format($resolvedLocale);
    }
}
