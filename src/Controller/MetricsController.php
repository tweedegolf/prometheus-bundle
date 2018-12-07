<?php

namespace TweedeGolf\PrometheusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use TweedeGolf\PrometheusClient\CollectorRegistry;
use TweedeGolf\PrometheusClient\Format\TextFormatter;

class MetricsController extends Controller
{
    /**
     * @var CollectorRegistry
     */
    protected $registry;

    /**
     * MetricsController constructor.
     *
     * @param CollectorRegistry $registry
     */
    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @return Response
     */
    public function metricsAction()
    {
        $formatter = new TextFormatter();
        return new Response($formatter->format($this->registry->collect()), 200, [
            'Content-Type' => $formatter->getMimeType(),
        ]);
    }
}
