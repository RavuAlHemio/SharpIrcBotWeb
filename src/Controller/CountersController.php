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

        $objConn = $objEM->getConnection();
        $objStmt = $objConn->executeQuery('
            SELECT
                ranked.command AS command,
                ranked.happened_timestamp AS happened,
                ranked.perp_nickname AS perp_nick,
                ranked.message AS message
            FROM
                (
                    SELECT
                        e.*,
                        rank() OVER (
                            PARTITION BY command
                            ORDER BY happened_timestamp DESC
                        )
                    FROM
                        counters.entries AS e
                ) AS ranked
            WHERE
                rank <= 5
        ');

        $arrCommandToRecentEntries = [];
        while (($arrResult = $objStmt->fetch()) != false)
        {
            if (!array_key_exists($arrResult['command'], $arrCommandToRecentEntries))
            {
                $arrCommandToRecentEntries[$arrResult['command']] = [];
            }

            $arrCommandToRecentEntries[$arrResult['command']][] = [
                'perpNick' => $arrResult['perp_nick'],
                'message' => $arrResult['message'],
                'happened' => $arrResult['happened']
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
