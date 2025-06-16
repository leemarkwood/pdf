<?php

namespace Leemarkwood\PDF;

use HeadlessChromium\Browser;
use HeadlessChromium\BrowserFactory;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PDFGenerator
{
    /**
     * Standard page sizes in points (1/72 inch)
     */
    private const PAGE_SIZES = [
        'letter' => [612, 792],
        'legal' => [612, 1008],
        'tabloid' => [792, 1224],
        'ledger' => [1224, 792],
        'a0' => [2384, 3370],
        'a1' => [1684, 2384],
        'a2' => [1191, 1684],
        'a3' => [842, 1191],
        'a4' => [595, 842],
        'a5' => [420, 595],
        'a6' => [297, 420],
    ];
    private ?Browser $browser = null;
    private array $options;
    private ?string $headerView = null;
    private ?string $footerView = null;
    private array $headerData = [];
    private array $footerData = [];
    private string $pdf = '';

    /**
     * Set the page size and orientation
     */
    public function page(string $size = 'a4', string $orientation = 'portrait'): self
    {
        $size = strtolower($size);
        if (!isset(self::PAGE_SIZES[$size])) {
            throw new \InvalidArgumentException("Invalid page size: {$size}");
        }

        $orientation = strtolower($orientation);
        if (!in_array($orientation, ['portrait', 'landscape'])) {
            throw new \InvalidArgumentException("Invalid orientation: {$orientation}");
        }

        [$width, $height] = self::PAGE_SIZES[$size];
        if ($orientation === 'landscape') {
            [$width, $height] = [$height, $width];
        }

        $this->options['format'] = [
            'width' => $width,
            'height' => $height
        ];

        return $this;
    }

    public function __construct(array $options = [])
    {
        $defaultConfig = config('pdf');
        
        $this->options = array_merge([
            'windowSize' => [1024, 768],
            'margins' => [
                'top' => 10,
                'right' => 10,
                'bottom' => 10,
                'left' => 10
            ],
            'scale' => 1.0,
            'printBackground' => true,
            'preferCSSPageSize' => true,
            'displayHeaderFooter' => true,
        ], $options);

        // Set default page size from config
        if (isset($defaultConfig['page'])) {
            $this->page(
                $defaultConfig['page']['size'] ?? 'a4',
                $defaultConfig['page']['orientation'] ?? 'portrait'
            );
        }
    }

    /**
     * Set the header Blade view
     */
    public function header(string $view, array $data = []): self
    {
        $this->headerView = $view;
        $this->headerData = $data;
        return $this;
    }

    /**
     * Set the footer Blade view
     */
    public function footer(string $view, array $data = []): self
    {
        $this->footerView = $view;
        $this->footerData = $data;
        return $this;
    }

    /**
     * Generate PDF from a Blade view
     */
    public function view(string $view, array $data = [], array $options = []): self
    {
        $html = View::make($view, $data)->render();
        return $this->generateFromHtml($html, $options);
    }

    /**
     * Generate PDF from a Blade component
     */
    public function component(string $component, array $data = [], array $options = []): self
    {
        $html = View::make('components.' . $component, $data)->render();
        return $this->generateFromHtml($html, $options);
    }

    /**
     * Generate inline response for browser viewing
     */
    public function inline(string $filename = 'document.pdf'): Response
    {
        $content = $this->pdf;
        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate download response
     */
    public function download(string $filename = 'document.pdf'): Response
    {
        $content = $this->pdf;
        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate raw PDF content
     */
    public function raw(): string
    {
        return $this->pdf;
    }

    /**
     * Generate PDF from HTML content
     */
    protected function generateFromHtml(string $html, array $options = []): self
    {
        $mergedOptions = array_merge($this->options, $options);
        
        try {
            $browser = $this->getBrowser();
            $page = $browser->createPage();

            // Create a temporary file for the HTML content with header and footer
            $tempFile = $this->createTempHtml($this->wrapContent($html));
            $page->navigate('file://' . $tempFile)->waitForNavigation();

            // Generate PDF
            $pdf = $page->pdf([
                'printBackground' => $mergedOptions['printBackground'],
                'preferCSSPageSize' => $mergedOptions['preferCSSPageSize'],
                'marginTop' => $mergedOptions['margins']['top'],
                'marginRight' => $mergedOptions['margins']['right'],
                'marginBottom' => $mergedOptions['margins']['bottom'],
                'marginLeft' => $mergedOptions['margins']['left'],
                'scale' => $mergedOptions['scale'],
                'displayHeaderFooter' => $mergedOptions['displayHeaderFooter'],
                'headerTemplate' => $this->renderHeaderTemplate(),
                'footerTemplate' => $this->renderFooterTemplate(),
                'width' => $mergedOptions['format']['width'] ?? null,
                'height' => $mergedOptions['format']['height'] ?? null,
            ]);

            // Clean up
            unlink($tempFile);
            $page->close();

            $this->pdf = $pdf;
            return $this;
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate PDF: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Wrap content with necessary HTML structure
     */
    private function wrapContent(string $content): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: {$this->options['margins']['top']}px {$this->options['margins']['right']}px {$this->options['margins']['bottom']}px {$this->options['margins']['left']}px;
            size: A4;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    $content
</body>
</html>
HTML;
    }

    /**
     * Render header template
     */
    private function renderHeaderTemplate(): string
    {
        if (!$this->headerView) {
            return '';
        }

        return View::make($this->headerView, $this->headerData)->render();
    }

    /**
     * Render footer template
     */
    private function renderFooterTemplate(): string
    {
        if (!$this->footerView) {
            return '';
        }

        return View::make($this->footerView, $this->footerData)->render();
    }

    /**
     * Create a temporary HTML file
     */
    private function createTempHtml(string $html): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_') . '.html';
        file_put_contents($tempFile, $html);
        return $tempFile;
    }

    /**
     * Get or create browser instance
     */
    private function getBrowser(): Browser
    {
        if ($this->browser === null) {
            $browserFactory = new BrowserFactory();
            $this->browser = $browserFactory->createBrowser([
                'windowSize' => $this->options['windowSize'],
                'enableImages' => true,
                'ignoreCertificateErrors' => true,
                'keepAlive' => true,
                'chromePath' => config('pdf.chromePath'),
            ]);
        }
        return $this->browser;
    }

    public function __destruct()
    {
        if ($this->browser !== null) {
            $this->browser->close();
        }
    }
}
}
