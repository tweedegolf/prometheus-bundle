<?php

namespace TweedeGolf\PrometheusBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use TweedeGolf\PrometheusBundle\DependencyInjection\CollectorCompilerPass;
use TweedeGolf\PrometheusBundle\DependencyInjection\StorageAdapterCompilerPass;

class TweedeGolfPrometheusBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CollectorCompilerPass());
        $container->addCompilerPass(new StorageAdapterCompilerPass());
    }

}
