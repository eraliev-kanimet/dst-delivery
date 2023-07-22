<?php

function filterAvailableLocales(array $locales)
{
    return array_intersect_key(config('app.locales'), array_flip($locales));
}
