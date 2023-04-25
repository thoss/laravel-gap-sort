<?php

namespace Thoss\GapSort\Resources;

use Illuminate\Routing\ResourceRegistrar as OriginalRegistrar;

class SortResourceRegistrar extends OriginalRegistrar
{
    /**
     * Route a resource to a controller.
     *
     * @param string $name
     * @param string $controller
     *
     * @return \Illuminate\Routing\RouteCollection
     */
    public function register($name, $controller, array $options = [])
    {
        // Werden weitere Routen benötigt, müssen die mit dem Parameter 'with' gesetzt werden
        $this->resourceDefaults = ['index', 'show', 'store', 'update', 'destroy'];

        if (isset($options['with'])) {
            if (!is_array($options['with'])) {
                $options['with'] = [$options['with']];
            }

            foreach ($options['with'] as $option) {
                array_push($this->resourceDefaults, $option);
            }
        }

        return parent::register($name, $controller, $options);
    }

    /**
     * Add the data method for a resourceful route.
     *
     * @param string $name
     * @param string $base
     * @param string $controller
     * @param array  $options
     *
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceSort($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/sort';

        $action = $this->getResourceAction($name, $controller, 'sort', $options);

        return $this->router->post($uri, $action);
    }
}
