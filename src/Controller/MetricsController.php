<?php

namespace TweedeGolf\PrometheusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use TweedeGolf\PrometheusClient\CollectorRegistry;
use TweedeGolf\PrometheusClient\Format\TextFormatter;

class MetricsController extends Controller
{
    /**
     * @return Response
     */
    public function metricsAction()
    {
        $registry = $this->get(CollectorRegistry::class);
        $formatter = new TextFormatter();
        $registry->getCounter('planviewer_metrics_hits')->inc();

        return new Response($formatter->format($registry->collect()), 200, [
            'Content-Type' => $formatter->getMimeType(),
        ]);
    }
}
