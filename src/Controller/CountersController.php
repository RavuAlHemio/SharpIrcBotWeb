<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CountersController extends Controller
{
    public function overviewAction()
    {
        $objEM = $this->getDoctrine()->getManager();
        $objQuery = $objEM->createQuery('
            SELECT
                ce.strCommand command,
                COUNT(ce.strID) counterValue
            FROM
                RavuAlHemioSharpIrcBotWebBundle:CounterEntry ce
            WHERE
                ce.blnExpunged = FALSE
            GROUP BY
                command
            ORDER BY
                command
        ');
        $arrCommandsAndCounts = $objQuery->getResult();
        $arrTemplateCommandsAndCounts = [];

        foreach ($arrCommandsAndCounts as $arrCommandAndCount)
        {
            $arrTemplateCommandsAndCounts[] = [
                'command' => $arrCommandAndCount['command'],
                'count' => $arrCommandAndCount['counterValue']
            ];
        }

        return $this->render('@RavuAlHemioSharpIrcBotWeb/counters/overview.html.twig', [
            'commandsAndCounts' => $arrTemplateCommandsAndCounts
        ]);
    }
}
