<?php

use Illuminate\Foundation\Application;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Trevorpe\LaravelSymfonyCache\Cache\SymfonyCacheFactory;

describe('invalid Symfony adapter class', function () {
    it('throws exception', function () {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessageMatches('/.*a valid Symfony adapter.*/');

        app(SymfonyCacheFactory::class)->repositoryFromConfig([
            'driver' => 'symfony',
            'adapter' => Application::class,
        ]);
    });
});

describe('unsupported Symfony adapter class', function () {
    it('throws exception', function () {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessageMatches('/.*is not a supported Symfony adapter.*/');

        app(SymfonyCacheFactory::class)->repositoryFromConfig([
            'driver' => 'symfony',
            'adapter' => ApcuAdapter::class,
        ]);
    });
});
