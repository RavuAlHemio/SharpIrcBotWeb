<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Controller;

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
                COUNT(qv.numID)
            FROM
                SharpIrcBotWebBundle:Quote q
                JOIN q.arrVotes qv
            ORDER BY
                COUNT(qv.numID)
        ');
        $arrQuotes = $objQuery->getResult();

        // DEBUG
        ob_start();
        var_dump($arrQuotes);
        $strRet = ob_get_clean();
        return $strRet;

        /*
        return $this->render('quotes/topquotes.html.twig', [

        ]);
        */
    }
}
