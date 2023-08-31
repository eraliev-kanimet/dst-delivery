<?php

use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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

function truncateStr($string, $maxLength = 13)
{
    if (mb_strlen($string) > $maxLength) {
        $string = mb_substr($string, 0, $maxLength) . '...';
    }

    return $string;
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

function getQueryFilamentStore(): Collection
{
    $user = Auth::user();

    if ($user->hasRole('store_manager')) {
        return Store::whereIn('id', $user->permissions)->pluck('name', 'id');
    } else if ($user->hasRole('store_owner')) {
        return Store::where('user_id', $user->id)->pluck('name', 'id');
    }

    return Store::pluck('name', 'id');
}

function getQueryFilamentQuery(Builder $builder): Builder
{
    $user = Auth::user();

    if ($user->hasRole('store_manager')) {
        return $builder->whereIn('store_id', $user->permissions);
    } else if ($user->hasRole('store_owner')) {
        return $builder->whereRelation('store','user_id', $user->id);
    }

    return $builder;
}
