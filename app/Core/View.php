<?php
declare(strict_types=1);

namespace App\Core;

use App\Config\Config;

/**
 * View / Error Handling Foundation
 * Milestone 3
 */
class View
{
    private string $viewPath;

    public function __construct(string $viewPath = null)
    {
        $this->viewPath = $viewPath ?? __DIR__ . '/../Views';
    }

    public function render(string $template, array $data = []): string
    {
        $file = $this->viewPath . '/' . ltrim($template, '/') . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: $file");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return ob_get_clean();
    }

    public function renderError(int $code, string $message = ''): string
    {
        $messages = [
            404 => 'Page Not Found',
            403 => 'Forbidden',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ];

        $title = $messages[$code] ?? 'Error';
        $msg = $message ?: $title;

        // Try to use existing 404.html visual language if available
        $existing404 = dirname(__DIR__, 2) . '/404.html';
        if ($code === 404 && file_exists($existing404) && Config::isProduction()) {
            // For M3, we keep simple, but in future we can wrap existing 404 design
            // For now, return simple error that preserves black+gold via inline style
        }

        $isDev = Config::isDevelopment() || Config::isDebug();

        $html = <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$code} — {$this->e($title)}</title>
<style>
body{margin:0;font-family:system-ui,sans-serif;background:#060605;color:#f5f3ef;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center}
.container{max-width:600px;padding:40px}
h1{font-size:72px;margin:0;background:linear-gradient(90deg,#ECBD61,#f5d48a);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
p{color:#a8a29e;line-height:1.6}
a{color:#ECBD61;text-decoration:none;border:1px solid rgba(236,189,97,.3);padding:10px 20px;border-radius:8px;display:inline-block;margin-top:20px}
a:hover{background:rgba(236,189,97,.1)}
code{background:rgba(255,255,255,.06);padding:2px 6px;border-radius:4px;font-size:13px}
</style>
</head>
<body>
<div class="container">
<h1>{$code}</h1>
<h2>{$this->e($title)}</h2>
<p>{$this->e($msg)}</p>
<p><a href="/">Back to Home</a></p>
HTML;

        if ($isDev && $message) {
            $html .= "<p><code>" . $this->e($message) . "</code></p>";
        }

        $html .= "</div></body></html>";

        return $html;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // For future frontend integration (M5+)
    public function renderLayout(string $content, array $data = []): string
    {
        // Will include header/footer from templates preserving existing design
        // For M3 foundation, just return content
        return $content;
    }
}
