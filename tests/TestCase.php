<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;

abstract class TestCase extends BaseTestCase
{
    /** Tests never send real email; assert on the fake instead. */
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }
}
