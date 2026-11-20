<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests check the HTML from the server; they don't need the compiled CSS/JS (npm run build).
        $this->withoutVite();
    }
}
