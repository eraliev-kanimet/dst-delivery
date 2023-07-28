<?php

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

function truncateStr($string, $maxLength = 13) {
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
