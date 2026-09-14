<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Bind feature tests to Laravel's application-aware test case.
|
*/

pest()->extend(TestCase::class)->in('Feature');
