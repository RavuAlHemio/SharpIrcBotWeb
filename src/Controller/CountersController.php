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

        $objQuery = $objEM->createQuery('
            SELECT
                ce.strCommand command,
                ce.dtmHappened happened,
                ce.strPerpNickname perpNick,
                ce.strMessage message
            FROM
                RavuAlHemioSharpIrcBotWebBundle:CounterEntry ce1
                LEFT OUTER JOIN RavuAlHemioSharpIrcBotWebBundle:CounterEntry ce2 ON (
                    ce1.strCommand = ce2.strCommand
                    AND ce1.dtmHappened < ce2.dtmHappened
                )
            GROUP BY
                command
            HAVING
                COUNT(*) < 5
            WHERE
                ce.blnExpunged = FALSE
            ORDER BY
                command,
                ce.dtmHappened DESC
        ');
        $arrCommandsAndRecentEntries = $objQuery->getResult();
        $arrCommandToRecentEntries = [];

        foreach ($arrCommandsAndRecentEntries as $arrTopEntry)
        {
            if (!array_key_exists($arrTopEntry['command'], $arrCommandToRecentEntries))
            {
                $arrCommandToRecentEntries[$arrTopEntry['command']] = [];
            }

            $arrCommandToRecentEntries[$arrTopEntry['command']][] = [
                'perpNick' => $arrTopEntry['perpNick'],
                'message' => $arrTopEntry['message'],
                'happened' => $arrTopEntry['happened']
            ];
        }

        $arrTemplateCommandsAndCounts = [];

        foreach ($arrCommandsAndCounts as $arrCommandAndCount)
        {
            $arrTemplateCommandsAndCounts[] = [
                'command' => $arrCommandAndCount['command'],
                'count' => $arrCommandAndCount['counterValue'],
                'recentEntries' => $arrCommandToRecentEntries[$arrCommandAndCount['command']]
            ];
        }

        return $this->render('@RavuAlHemioSharpIrcBotWeb/counters/overview.html.twig', [
            'counters' => $arrTemplateCommandsAndCounts
        ]);
    }
}
