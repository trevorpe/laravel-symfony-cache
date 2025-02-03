<?php

use Illuminate\Support\Facades\Cache;

describe('invalid Symfony adapter class', function () {
    it('throws exception', function () {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessageMatches('/.*a valid Symfony adapter.*/');

        Cache::store('symfony_non_cache');
    });
});

describe('unsupported Symfony adapter class', function () {
    it('throws exception', function () {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessageMatches('/.*is not a supported Symfony adapter.*/');

        Cache::store('symfony_unsupported');
    });
});
