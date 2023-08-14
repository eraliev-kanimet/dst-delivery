<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

function filterAvailableLocales(array $locales)
{
    return array_intersect_key(config('app.locales'), array_flip($locales));
}

function errors(string $message, array $errors = []): array
{
    return [
        'message' => $message,
        'errors' => $errors
    ];
}

function ttt(string $value): array
{
    $array = [];

    foreach (array_keys(config('app.locales')) as $key) {
        $array[$key] = $value;
    }

    return $array;
}

function truncateStr($string, $maxLength = 13)
{
    if (mb_strlen($string) > $maxLength) {
        $string = mb_substr($string, 0, $maxLength) . '...';
    }

    return $string;
}

function removeEmptyElements($array): array
{
    $keysToRemove = [];

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $keysToRemove = array_merge($keysToRemove, array_keys($value, null, true));
            $keysToRemove = array_merge($keysToRemove, array_keys($value, ''));
        }

        if ($value === null || $value === '' || $key === null || $key === '') {
            $keysToRemove[] = $key;
        }
    }

    foreach ($keysToRemove as $key) {
        unset($array[$key]);
    }

    return $array;
}

function getSupportedLocale($acceptLanguage, $supportedLocales): bool|string|null
{
    $acceptLanguage = explode(',', $acceptLanguage);

    foreach ($acceptLanguage as $language) {
        $locale = strtok($language, ';');

        if (in_array($locale, $supportedLocales)) {
            return $locale;
        }
    }

    return null;
}

function getImages(?array $images): array
{
    $array = [];

    foreach ($images ?? [] as $image) {
        $array[] = asset('storage/' . $image);
    }

    return $array;
}

function fakeImage(string $model): string
{
    $dir = storage_path("app/public/$model");

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $image = rand(1, 30) . '.jpg';

    if (!file_exists("$dir/$image")) {
        File::copy(storage_path("fake/images/$image"), "$dir/$image");
    }

    return "$model/$image";
}

function fakeImages(string $model): array
{
    $images = [];

    for ($i = 0; $i < rand(1, 3); $i++) {
        $images[] = fakeImage($model);
    }

    return $images;
}

function dbQueryLog(int $offset = 6): JsonResponse
{
    $logs = array_slice(DB::getQueryLog(), $offset);

    return response()->json([
        count($logs),
        ...$logs
    ]);
}
