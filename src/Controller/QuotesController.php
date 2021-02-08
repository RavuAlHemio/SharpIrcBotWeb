<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use RavuAlHemio\SharpIrcBotWebBundle\Entity\Quote;
use RavuAlHemio\SharpIrcBotWebBundle\Entity\QuoteVote;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class QuotesController extends AbstractController
{
    public function topQuotesAction(EntityManagerInterface $objEM)
    {
        $objQuery = $objEM->createQuery('
            SELECT
                q,
                COALESCE(SUM(qv.intPoints), 0) points
            FROM
                RavuAlHemioSharpIrcBotWebBundle:Quote q
                LEFT OUTER JOIN q.arrVotes qv
            GROUP BY
                q
            ORDER BY
                points DESC,
                q.dtmTimestamp DESC
        ');
        $arrQuotesAndPoints = $objQuery->getResult();
        $arrTemplateQuotes = [];
        $intLastPoints = null;

        foreach ($arrQuotesAndPoints as $arrQuoteAndPoints)
        {
            /** @var Quote $objQuote */
            $objQuote = $arrQuoteAndPoints[0];
            $intPoints = $arrQuoteAndPoints['points'];
            $strBody = static::formatQuote($objQuote);

            $arrTemplateQuotes[] = [
                'score' => $intPoints,
                'scoreChanged' => ($intLastPoints !== $intPoints),
                'body' => $strBody
            ];
            $intLastPoints = $intPoints;
        }

        return $this->render('@RavuAlHemioSharpIrcBotWeb/quotes/topquotes.html.twig', [
            'topQuotes' => $arrTemplateQuotes
        ]);
    }

    public function quotesVotesAction(EntityManagerInterface $objEM)
    {
        $objQuery = $objEM->createQuery('
            SELECT
                q,
                qv
            FROM
                RavuAlHemioSharpIrcBotWebBundle:Quote q
                LEFT OUTER JOIN q.arrVotes qv
            ORDER BY
                q.dtmTimestamp DESC,
                qv.strVoterLowercase ASC
        ');
        /** @var Quote[] $arrQuotesWithVotes */
        $arrQuotesWithVotes = $objQuery->getResult();
        $arrTemplateQuotes = [];
        $intLastPoints = null;

        foreach ($arrQuotesWithVotes as $objQuote)
        {
            $strBody = static::formatQuote($objQuote);
            $strVotes = static::votesForTemplate($objQuote->arrVotes);
            $intScore = static::sumVotes($objQuote->arrVotes);

            $arrTemplateQuotes[] = [
                'body' => $strBody,
                'votes' => $strVotes,
                'score' => $intScore
            ];
        }

        return $this->render('@RavuAlHemioSharpIrcBotWeb/quotes/quotesvotes.html.twig', [
            'quotesVotes' => $arrTemplateQuotes
        ]);
    }

    /**
     * @param Quote $objQuote
     * @return string
     */
    public static function formatQuote($objQuote)
    {
        switch ($objQuote->strMessageType)
        {
            case 'M':
                return "<{$objQuote->strAuthor}> {$objQuote->strBody}";
            case 'A':
                return "* {$objQuote->strAuthor} {$objQuote->strBody}";
            case 'F':
            default:
                return $objQuote->strBody;
        }
    }

    /**
     * @param QuoteVote[] $arrVotes
     * @return array
     */
    public static function votesForTemplate($arrVotes)
    {
        $arrRet = [];
        foreach ($arrVotes as $objVote)
        {
            $arrRet[] = [
                'voter' => $objVote->strVoterLowercase,
                'points' => $objVote->intPoints
            ];
        }
        return $arrRet;
    }

    /**
     * @param QuoteVote[] $arrVotes
     * @return int
     */
    public static function sumVotes($arrVotes)
    {
        $intScore = 0;
        foreach ($arrVotes as $objVote)
        {
            $intScore += $objVote->intPoints;
        }
        return $intScore;
    }
}
