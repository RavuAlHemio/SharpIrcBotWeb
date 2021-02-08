<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;


class BaseController
{
    protected function generateUrl(RouterInterface $objRouter, string $strRoute, array $arrParameters = [])
    {
        return $objRouter->generate($strRoute, $arrParameters, UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    protected function render(Environment $objTwig, string $strView, array $arrParameters = [], Response $objResponse = null): Response
    {
        $strContent = $objTwig->render($strView, $arrParameters);

        if ($objResponse === null) {
            $objResponse = new Response();
        }

        $objResponse->setContent($strContent);
        return $objResponse;
    }
}
