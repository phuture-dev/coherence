<?php

declare(strict_types=1);

namespace Advandz\Kernel\Tests;

use Advandz\Kernel\Html;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

/**
 * Test case for the Html utility class.
 */
class HtmlTest extends TestCase
{
    public function testEntityEncode(): void
    {
        $string = '<p>Test & "quotes"</p>';
        Assert::equal(htmlentities($string), Html::entityEncode($string));
    }

    public function testSpecialCharsEncode(): void
    {
        $string = '<p>Test & "quotes"</p>';
        Assert::equal(htmlspecialchars($string), Html::specialCharsEncode($string));
    }

    public function testSpecialCharsDecode(): void
    {
        $string = '&lt;p&gt;Test &amp; &quot;quotes&quot;&lt;/p&gt;';
        Assert::equal(htmlspecialchars_decode($string), Html::specialCharsDecode($string));
    }

    public function testStripTags(): void
    {
        $string = '<p>Test <b>bold</b> text</p>';
        Assert::equal(strip_tags($string), Html::stripTags($string));
    }
}

// Run the test
(new HtmlTest())->run();
