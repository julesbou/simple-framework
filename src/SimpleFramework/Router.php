<?php

namespace SimpleFramework;

class Router
{
    private $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function match($url)
    {
        $routeFound = false;

        foreach ($this->routes as $routeName => $route) {
            $repl = array('/' => '\/', '{' => '(?P<', '}' => '>[^\/]+)');
            $pattern = sprintf('/^%s$/', str_replace(array_keys($repl), array_values($repl), $route['pattern']));
            if (
                preg_match($pattern, $url, $params)
                && (!isset($route['method']) || $route['method'] == $_SERVER['REQUEST_METHOD'])
            ) {
                $routeFound = $route;
                break;
            }
        }

        if (false === $routeFound) {
            throw new HttpException($url, 404);
        }

        // sanitize parameters
        foreach ($params as $key => $val) {
            if (is_int($key)) {
                unset($params[$key]);
            }
        }

        $route['name'] = $routeName;
        return array($route, $params);
    }

    public function generate($route, $params = array())
    {
        if (!isset($this->routes[$route])) {
            throw new \InvalidArgumentException("Route '$route' not found");
        }

        $route = $this->routes[$route];

        // route do not accepts parameters
        if (empty($params)) {
            return $route['pattern'];
        }

        $pattern = sprintf('/^%s$/', str_replace('/', '\/', $route['pattern']));
        preg_match_all($pattern, '', $matches);

        // sanitize matches
        foreach ($matches as $key => $val) {
            if (is_int($key)) unset($matches[$key]);
        }

        $url = $route['pattern'];

        foreach ($params as $key => $val) {
            $url = str_replace('{'.$key.'}', $val, $url);
        }

        return $url;
    }
}
