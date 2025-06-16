# LMWPDF

A PHP package for generating PDF documents from HTML and CSS using headless Chrome.

## Installation

```bash
composer require leewood/lmwpdf
```

## Requirements

- PHP 8.0 or higher
- Composer
- Chrome/Chromium browser installed on the system

## Usage

```php
use Leewood\LMWPDF\PDFGenerator;

// Create a new instance with optional configuration
$pdfGenerator = new PDFGenerator([
    'windowSize' => [1024, 768],
    'margins' => [
        'top' => 10,
        'right' => 10,
        'bottom' => 10,
        'left' => 10
    ],
    'scale' => 1.0,
    'printBackground' => true
]);

// Generate PDF from HTML string
$html = '<h1>Hello World</h1><p>This is a test PDF.</p>';
$pdf = $pdfGenerator->generateFromHtml($html);

// Generate PDF from URL
$pdf = $pdfGenerator->generateFromUrl('https://example.com');

// Save the PDF to a file
file_put_contents('output.pdf', $pdf);
```

## Features

- Generate PDFs from HTML strings or URLs
- Full support for modern HTML5 and CSS3
- JavaScript rendering support
- Custom page sizes and margins
- Background graphics support
- Header and footer support
- Configurable options:
  - Page size and margins
  - Scale factor
  - Background printing
  - Headers and footers
  - Window size for rendering

## Configuration Options

```php
$options = [
    // Browser window size for rendering
    'windowSize' => [1024, 768],
    
    // Page margins in pixels
    'margins' => [
        'top' => 10,
        'right' => 10,
        'bottom' => 10,
        'left' => 10
    ],
    
    // Scale factor for the page (1.0 = 100%)
    'scale' => 1.0,
    
    // Whether to print background graphics
    'printBackground' => true,
    
    // Whether to prefer page size from CSS
    'preferCSSPageSize' => true,
    
    // Whether to display header and footer
    'displayHeaderFooter' => false
];
```

## How It Works

The package uses Chrome in headless mode to render HTML content and generate PDFs. This ensures:

1. Perfect rendering of modern web technologies
2. Full CSS support including flexbox and grid
3. JavaScript execution
4. Web fonts support
5. SVG and canvas rendering

## License

MIT License
