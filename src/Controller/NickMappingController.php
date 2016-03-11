<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Controller;

use RavuAlHemio\SharpIrcBotWebBundle\Entity\Quote;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class NickMappingController extends Controller
{
    public function nicksAliasesAction()
    {
        $objEM = $this->getDoctrine()->getManager();
        $objQuery = $objEM->createQuery('
            SELECT
                bn.strNickname baseNick,
                mn.strMappedNicknameLowercase mappedNick
            FROM
                RavuAlHemioSharpIrcBotWebBundle:BaseNickname bn
                LEFT JOIN bn.arrMappings mn
            ORDER BY
                baseNick,
                mappedNick
        ');
        $arrNicksAndAliases = $objQuery->getResult();
        $strLastBaseNick = null;
        $arrTemplateNicksAndAliases = [];

        foreach ($arrNicksAndAliases as $arrNickAndAlias)
        {
            $strBaseNick = $arrNickAndAlias['baseNick'];
            $strMappedNick = $arrNickAndAlias['mappedNick'];

            $arrTemplateNicksAndAliases[] = [
                'nick' => $strBaseNick,
                'nickChanged' => ($strLastBaseNick !== $strBaseNick),
                'alias' => $strMappedNick,
                'noAlias' => ($strMappedNick === null)
            ];
            $strLastBaseNick = $strBaseNick;
        }

        return $this->render('@RavuAlHemioSharpIrcBotWeb/quotes/nicks_aliases.html.twig', [
            'nicksAndAliases' => $arrTemplateNicksAndAliases
        ]);
    }
}
