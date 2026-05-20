<?php
class Router {
    private array $routes = [];

    public function get(string $path, array $handler): void {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void {
        $this->routes['POST'][$path] = $handler;
    }
public function dispatch(): void {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $uri = preg_replace('#^/Edu/public(?:/index\.php)?#', '', $uri);
    $uri = ($uri === '' || $uri === null) ? '/' : $uri;

    if ($method === 'POST') error_log("POST URI: [$uri] | Routes: " . implode(', ', array_keys($this->routes['POST'] ?? [])));

    if (isset($this->routes[$method][$uri])) {
        [$controller, $action] = $this->routes[$method][$uri];
        (new $controller)->$action();
    } else {
        http_response_code(404);
        echo "<pre>404\nURI reçue : [$uri]\nMéthode : $method\n\nRoutes POST dispo :\n" 
             . implode("\n", array_keys($this->routes['POST'] ?? [])) . "</pre>";
    }
}
}