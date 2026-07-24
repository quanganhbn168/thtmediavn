<?php

namespace Tests\Unit;

use App\Services\Imports\MtdHtmlSanitizer;
use PHPUnit\Framework\TestCase;

class MtdHtmlSanitizerTest extends TestCase
{
    public function test_it_removes_scripts_event_handlers_and_unsafe_urls(): void
    {
        $html = <<<'HTML'
<div class="source" onclick="alert(1)">
    <script>alert('xss')</script>
    <p style="color:red">Nội dung <strong>hợp lệ</strong></p>
    <a href="javascript:alert(1)" target="_blank">Không an toàn</a>
    <a href="https://example.com" target="_blank">An toàn</a>
</div>
HTML;

        $clean = (new MtdHtmlSanitizer)->sanitize($html);

        $this->assertNotNull($clean);
        $this->assertStringNotContainsStringIgnoringCase('<script', $clean);
        $this->assertStringNotContainsStringIgnoringCase('onclick', $clean);
        $this->assertStringNotContainsStringIgnoringCase('javascript:', $clean);
        $this->assertStringNotContainsStringIgnoringCase('style=', $clean);
        $this->assertStringContainsString('<strong>hợp lệ</strong>', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }
}
