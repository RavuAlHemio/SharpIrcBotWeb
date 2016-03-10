<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Controller;

use RavuAlHemio\SharpIrcBotWebBundle\Entity\Quote;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class QuotesController extends Controller
{
    public function topQuotesAction()
    {
        $objEM = $this->getDoctrine()->getManager();
        $objQuery = $objEM->createQuery('
            SELECT
                q,
                SUM(qv.intPoints) points
            FROM
                RavuAlHemioSharpIrcBotWebBundle:Quote q
                JOIN q.arrVotes qv
            GROUP BY
                q
            ORDER BY
                points DESC
        ');
        $arrQuotesAndPoints = $objQuery->getResult();
        $arrTemplateQuotes = [];
        $intLastPoints = null;

        foreach ($arrQuotesAndPoints as $arrQuoteAndPoints)
        {
            /** @var Quote $objQuote */
            $objQuote = $arrQuoteAndPoints[0];
            $intPoints = $arrQuoteAndPoints['points'];

            switch ($objQuote->strMessageType)
            {
                case 'M':
                    $strBody = "<{$objQuote->strAuthor}> {$objQuote->strBody}";
                    break;
                case 'A':
                    $strBody = "* {$objQuote->strAuthor} {$objQuote->strBody}";
                    break;
                case 'F':
                default:
                    $strBody = $objQuote->strBody;
                    break;
            }

            $arrTemplateQuotes[] = [
                'score' => $intPoints,
                'scoreChanged' => ($intPoints !== $intLastPoints),
                'body' => $strBody
            ];
        }

        return $this->render('quotes/topquotes.html.twig', [
            'topQuotes' => $arrTemplateQuotes
        ]);
    }
}
