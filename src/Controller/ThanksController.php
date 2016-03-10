<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class ThanksController extends Controller
{
    public function thanksGridAction()
    {
        $objEM = $this->getDoctrine()->getManager();
        $objQuery = $objEM->createQuery('
            SELECT
                te.strThankerLowercase thanker,
                te.strThankeeLowercase thankee,
                COUNT(te.numID) thankcount
            FROM
                RavuAlHemioSharpIrcBotWebBundle:ThanksEntry te
            WHERE
                te.blnDeleted = FALSE
            GROUP BY
                thanker,
                thankee
            ORDER BY
                thanker,
                thankee
        ');
        $arrPairCounts = $objQuery->getResult();

        $arrThankersToThankeesToCounts = [];

        // find all nicks
        $arrNicks = [];
        foreach ($arrPairCounts as $arrPairCount)
        {
            if (!in_array($arrPairCount['thanker'], $arrNicks))
            {
                $arrNicks[] = $arrPairCount['thanker'];
            }
            if (!in_array($arrPairCount['thankee'], $arrNicks))
            {
                $arrNicks[] = $arrPairCount['thankee'];
            }
        }
        sort($arrNicks);

        // prepare com-con
        foreach ($arrNicks as $strThanker)
        {
            $arrThankersToThankeesToCounts[$strThanker] = [];
            foreach ($arrNicks as $strThankee)
            {
                $arrThankersToThankeesToCounts[$strThanker][$strThankee] = 0;
            }
        }

        // populate with actual data
        foreach ($arrPairCounts as $arrPairCount)
        {
            $arrThankersToThankeesToCounts[$arrPairCount['thanker']][$arrPairCount['thankee']] = $arrPairCount['thankcount'];
        }

        return $this->render('@RavuAlHemioSharpIrcBotWeb/thanks/thanksgrid.html.twig', [
            'thankers_thankees_counts' => $arrThankersToThankeesToCounts
        ]);
    }
}