<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Controller;

use RavuAlHemio\SharpIrcBotWebBundle\Entity\Quote;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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

        return $this->render('@RavuAlHemioSharpIrcBotWeb/nick_mapping/nicks_aliases.html.twig', [
            'nicksAndAliases' => $arrTemplateNicksAndAliases
        ]);
    }

    public function aliasesForNickAction(Request $objRequest)
    {
        $strNickname = $objRequest->query->get('nick');
        if ($strNickname === null || $strNickname === '')
        {
            return static::makePlainTextResponse('GET parameter "nick" required.', 400);
        }
        $strLowercaseNickname = mb_strtolower($strNickname, 'UTF-8');

        /** @var \Doctrine\ORM\EntityManager $objEM */
        $objEM = $this->getDoctrine()->getManager();
        $objAsBaseNickQuery = $objEM->createQuery('
            SELECT
                bn.strNickname
            FROM
                RavuAlHemioSharpIrcBotWebBundle:BaseNickname bn
            WHERE
                LOWER(bn.strNickname) = :lcnick
        ');
        $objAsBaseNickQuery->setParameter('lcnick', $strLowercaseNickname);
        $arrBaseNicks = $objAsBaseNickQuery->getScalarResult();
        if (count($arrBaseNicks) == 0)
        {
            $objAsMappedNickQuery = $objEM->createQuery('
                SELECT
                    bn.strNickname baseNick
                FROM
                    RavuAlHemioSharpIrcBotWebBundle:BaseNickname bn
                    JOIN bn.arrMappings mn
                WHERE
                    mn.strMappedNicknameLowercase = :lcnick
            ');
            $objAsMappedNickQuery->setParameter('lcnick', $strLowercaseNickname);
            $arrBaseNicks = $objAsMappedNickQuery->getScalarResult();
            if (count($arrBaseNicks) == 0)
            {
                // nothing
                return static::makePlainTextResponse('');
            }
        }

        $strBaseNick = $arrBaseNicks[0]['strNickname'];

        $objMappedNicksQuery = $objEM->createQuery('
            SELECT
                mn.strMappedNicknameLowercase mappedNick
            FROM
                RavuAlHemioSharpIrcBotWebBundle:BaseNickname bn
                JOIN bn.arrMappings mn
            WHERE
                bn.strNickname = :basenick
            ORDER BY
                mappedNick
        ');
        $objMappedNicksQuery->setParameter('basenick', $strBaseNick);

        $arrMappedNicks = $objMappedNicksQuery->getResult();

        $arrNicksToReturn = [$strBaseNick . "\n"];
        foreach ($arrMappedNicks as $arrMappedNick)
        {
            $arrNicksToReturn[] = $arrMappedNick['mappedNick'] . "\n";
        }

        $strNicksToReturn = implode('', $arrNicksToReturn);

        return static::makePlainTextResponse($strNicksToReturn);
    }

    public static function makePlainTextResponse($strText, $intResponseCode=200)
    {
        return new Response($strText, $intResponseCode, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
