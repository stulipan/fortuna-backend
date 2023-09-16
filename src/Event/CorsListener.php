<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorsListener implements EventSubscriberInterface
{
    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');

        // Ez azert kell, mert amikor a localhost-rol inditok API 'PUT' hivast a fortunaai.hu domainre, akkor
        // elobb egy 'OPTIONS' hivast indit, aminek kotelezoen 204-es statuszt kell visszaadjon!
        if ($event->getRequest()->getRealMethod() == 'OPTIONS') {
            $response->setStatusCode(204);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
