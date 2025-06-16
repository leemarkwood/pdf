<?php

namespace Leewood\LMWPDF\Tests;

use PHPUnit\Framework\TestCase;
use Leewood\LMWPDF\PDFGenerator;

class PDFGeneratorTest extends TestCase
{
    private PDFGenerator $pdfGenerator;

    protected function setUp(): void
    {
        $this->pdfGenerator = new PDFGenerator();
    }

    public function testGenerateFromHtml()
    {
        $html = '<h1>Test Document</h1><p>This is a test.</p>';
        $output = $this->pdfGenerator->generateFromHtml($html);
        
        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }
}
