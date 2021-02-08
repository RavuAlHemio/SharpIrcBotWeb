<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;


class ThanksController extends BaseController
{
    public function thanksGridAction(EntityManagerInterface $objEM, Environment $objTwig)
    {
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
        $arrThankersToTotalCounts = [];
        $arrThankeesToTotalCounts = [];

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
            $arrThankersToTotalCounts[$strThanker] = 0;
            $arrThankeesToTotalCounts[$strThanker] = 0;
        }
        $intTotalSum = 0;

        // populate with actual data
        foreach ($arrPairCounts as $arrPairCount)
        {
            $strThanker = $arrPairCount['thanker'];
            $strThankee = $arrPairCount['thankee'];
            $intThankCount = $arrPairCount['thankcount'];

            $arrThankersToThankeesToCounts[$strThanker][$strThankee] = $intThankCount;
            $arrThankersToTotalCounts[$strThanker] += $intThankCount;
            $arrThankeesToTotalCounts[$strThankee] += $intThankCount;
            $intTotalSum += $intThankCount;
        }

        return $this->render(
            $objTwig,
            '@RavuAlHemioSharpIrcBotWeb/thanks/thanksgrid.html.twig',
            [
                'thankers_thankees_counts' => $arrThankersToThankeesToCounts,
                'thankers_totals' => $arrThankersToTotalCounts,
                'thankees_totals' => $arrThankeesToTotalCounts,
                'grand_total' => $intTotalSum
            ]
        );
    }
}
