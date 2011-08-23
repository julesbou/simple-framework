<?php

namespace SimpleFramework;

class Kernel
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->build();
    }

    private function build()
    {
        if (!isset($this->container['templating.directories'])) {
            throw new \ErrorException('you must specifiy at least one template directory');
        }

        if (!isset($this->container['router.routes'])) {
            throw new \ErrorException('you must specifiy at least one route');
        }

        if (isset($this->container['logger.file'])) {
            $this->container['logger'] = new Logger($this->container['logger.file']);
        }

        if (!isset($this->container['templating.vars'])) {
            $this->container['templating.vars'] = array();
        }

        $this->container['router'] = new Router($this->container['router.routes']);
        $this->container['templating'] = new Templating($this->container['templating.directories'], $this->container['templating.vars']);
        $this->container['event_dispatcher'] = new EventDispatcher();
    }

    /**
     * Handle the request and convert it to a response
     *
     * 1. url is parsed to find a route
     * 2. route controller is instanciated
     * 3. controller action is called and return response content
     */
    public function run()
    {
        $this->container->freeze();

        $pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
        list($route, $params) = $this->container['router']->match($pathInfo);

        $controllerClass = $route['controller'];
        $controller = new $controllerClass($this->container);

        if (null !== ($before = $this->container['event_dispatcher']->dispatch('controller.before', array('route' => $route)))) {
            return $before;
        }

        $content = $this->callAction($controller, $route['action'], $params);

        if (null !== ($after = $this->container['event_dispatcher']->dispatch('controller.after', array('content' => $content)))) {
            return $after;
        }

        return $content;
    }

    /**
     * Call an action on a controller
     *
     * @param controller object     an instance of the controller
     * @param actionName string     the action name (eg: homepage)
     */
    private function callAction($controller, $action, $params = array())
    {
        $method = sprintf('%sAction', $action);
        $controllerRefl = new \ReflectionClass($controller);

        if (!$controllerRefl->hasMethod($method)) {
            throw new \InvalidArgumentException(sprintf('action \'%s\' do not exists', $action));
        }

        $reflMethod = $controllerRefl->getMethod($method);

        // if a $_GET['x'] parameter is found with and the action method accepts a $x argument
        // we add the GET parameter to the action call
        $actionParams = array();
        foreach($reflMethod->getParameters() as $reflParam) {
            $actionParams[] = isset($params[$reflParam->name]) ? $params[$reflParam->name] : null;
        }

        return $reflMethod->invokeArgs($controller, $actionParams);
    }
}
