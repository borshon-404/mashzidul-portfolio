<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Router / Front Controller Foundation
 * Milestone 3: Foundation
 * 
 * Supports:
 * - Clean URLs: /, /about/, /services/, /projects/, /pricing/, /blog/, /contact/, /faq/, /booking/, /terms/, /privacy/
 * - Projects: /projects/{slug}/
 * - Blog: /{blog-slug}/ and legacy /blog/{slug}/ → 301 to /{slug}/
 * - No redirect loops
 * - Static assets bypass (handled by .htaccess)
 */
class Router
{
    private array $routes = [];
    private array $patterns = [
        '{slug}' => '([a-z0-9-]+)',
        '{id}' => '(\d+)',
    ];

    public function get(string $path, $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'regex' => $this->pathToRegex($path),
        ];
    }

    private function pathToRegex(string $path): string
    {
        // Replace {slug} placeholders
        $regex = $path;
        foreach ($this->patterns as $placeholder => $pattern) {
            $regex = str_replace($placeholder, $pattern, $regex);
        }
        // Ensure leading slash, optional trailing slash
        $regex = '#^' . rtrim($regex, '/') . '/?$#';
        return $regex;
    }

    public function match(string $method, string $uri): ?array
    {
        $method = strtoupper($method);
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path = '/' . ltrim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                continue;
            }

            if (preg_match($route['regex'], $path, $matches)) {
                array_shift($matches); // Remove full match
                return [
                    'handler' => $route['handler'],
                    'params' => $matches,
                    'path' => $route['path'],
                ];
            }
        }

        return null;
    }

    public function dispatch(string $method, string $uri): void
    {
        $match = $this->match($method, $uri);

        if (!$match) {
            // Check legacy blog route: /blog/{slug}/ → 301 to /{slug}/
            if (preg_match('#^/blog/([a-z0-9-]+)/?$#', $uri, $m)) {
                $slug = $m[1];
                // Verify slug exists in blog_posts to avoid open redirect
                // For now, redirect if slug looks valid, final check in controller
                Response::redirect301('/' . $slug . '/');
            }

            // 404
            http_response_code(404);
            $view = new View();
            echo $view->renderError(404);
            exit;
        }

        $handler = $match['handler'];
        $params = $match['params'];

        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } elseif (is_string($handler) && strpos($handler, '@') !== false) {
            [$controller, $method] = explode('@', $handler, 2);
            $controllerClass = "App\\Controllers\\$controller";
            if (!class_exists($controllerClass)) {
                throw new \RuntimeException("Controller not found: $controllerClass");
            }
            $instance = new $controllerClass();
            if (!method_exists($instance, $method)) {
                throw new \RuntimeException("Method not found: $controllerClass@$method");
            }
            call_user_func_array([$instance, $method], $params);
        } else {
            throw new \RuntimeException("Invalid route handler");
        }
    }

    // For testing without full dispatch
    public static function isLegacyBlogRoute(string $uri): bool
    {
        return preg_match('#^/blog/([a-z0-9-]+)/?$#', $uri) === 1;
    }

    public static function getCleanBlogUrl(string $uri): ?string
    {
        if (preg_match('#^/blog/([a-z0-9-]+)/?$#', $uri, $m)) {
            return '/' . $m[1] . '/';
        }
        return null;
    }

    // Define all current clean URLs (for future frontend)
    public static function getPublicRoutes(): array
    {
        return [
            '/',
            '/about/',
            '/services/',
            '/projects/',
            '/pricing/',
            '/blog/',
            '/contact/',
            '/faq/',
            '/booking/',
            '/terms/',
            '/privacy/',
            '/projects/{slug}/',
            '/{slug}/', // blog post clean
        ];
    }
}
