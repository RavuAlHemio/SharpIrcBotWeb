<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Controller;

use RavuAlHemio\SharpIrcBotWebBundle\Entity\CounterEntry;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CountersController extends Controller
{
    const QUERY_GET_ALL_COUNTER_VALUES = '
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
    ';

    const QUERY_POSTGRES_GET_ALL_TOP_FIVE_MESSAGES = '
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
                WHERE
                    e.expunged = FALSE
            ) AS ranked
        WHERE
            rank <= 5
    ';

    const QUERY_GET_COUNT_BY_COMMAND = '
        SELECT
            COUNT(ce.strID) counterValue
        FROM
            RavuAlHemioSharpIrcBotWebBundle:CounterEntry ce
        WHERE
            ce.strCommand = :command
            AND ce.blnExpunged = FALSE
    ';

    const QUERY_GET_TOP_FIVE_MESSAGES = '
        SELECT
            ce
        FROM
            RavuAlHemioSharpIrcBotWebBundle:CounterEntry ce
        WHERE
            ce.strCommand = :command
            AND ce.blnExpunged = FALSE
        ORDER BY
            ce.dtmHappened DESC
    ';

    const QUERY_POSTGRES_GET_WEEKDAY_STATS_BY_COMMAND = '
        SELECT
            EXTRACT(DOW FROM happened_timestamp) AS day_of_week,
            COUNT(*) AS count
        FROM
            counters.entries
        GROUP BY
            EXTRACT(DOW FROM happened_timestamp)
        WHERE
            command = :command
            AND expunged = FALSE
    ';

    const QUERY_POSTGRES_GET_DAYHOUR_STATS_BY_COMMAND = '
        SELECT
            EXTRACT(HOUR FROM happened_timestamp) AS hour_of_day,
            COUNT(*) AS count
        FROM
            counters.entries
        GROUP BY
            EXTRACT(HOUR FROM happened_timestamp)
        WHERE
            command = :command
            AND expunged = FALSE
    ';

    public function overviewAction()
    {
        $objEM = $this->getDoctrine()->getManager();

        $objQuery = $objEM->createQuery(static::QUERY_GET_ALL_COUNTER_VALUES);
        $arrCommandsAndCounts = $objQuery->getResult();

        $objConn = $objEM->getConnection();
        $objStmt = $objConn->executeQuery(static::QUERY_POSTGRES_GET_ALL_TOP_FIVE_MESSAGES);

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
            $strDetailsLink = $this->generateUrl(
                'ravuAlHemioSharpIrcBot_counters_details',
                [
                    'strCommand' => $arrCommandAndCount['command']
                ]
            );
            $arrTemplateCommandsAndCounts[] = [
                'command' => $arrCommandAndCount['command'],
                'count' => $arrCommandAndCount['counterValue'],
                'recentEntries' => $arrCommandToRecentEntries[$arrCommandAndCount['command']],
                'detailsLink' => $strDetailsLink
            ];
        }

        return $this->render('@RavuAlHemioSharpIrcBotWeb/counters/overview.html.twig', [
            'counters' => $arrTemplateCommandsAndCounts
        ]);
    }

    public function detailsAction($strCommand)
    {
        $objEM = $this->getDoctrine()->getManager();

        $objQuery = $objEM->createQuery(static::QUERY_GET_COUNT_BY_COMMAND);
        $objQuery->setParameter('command', $strCommand);
        $intCount = $objQuery->getSingleScalarResult();

        $objQuery = $objEM->createQuery(static::QUERY_GET_TOP_FIVE_MESSAGES);
        $objQuery->setParameter('command', $strCommand);
        $arrTopFive = $objQuery->getResult();
        $arrTemplateRecent = [];
        foreach ($arrTopFive as $objEntry)
        {
            /** @var CounterEntry $objEntry */
            $arrTemplateRecent[] = [
                'perpNick' => $objEntry->strPerpNickname,
                'message' => $objEntry->strMessage,
                'happened' => $objEntry->dtmHappened
            ];
        }

        $objConn = $objEM->getConnection();

        $objStmt = $objConn->prepare(static::QUERY_POSTGRES_GET_WEEKDAY_STATS_BY_COMMAND);
        $objStmt->bindValue('command', $strCommand);
        $objStmt->execute();
        $arrWeekDayStats = $objStmt->fetchAll();

        $arrWeekDayToCount = array_fill(0, 7, 0);
        foreach ($arrWeekDayStats as $arrWeekDayStat)
        {
            $arrWeekDayToCount[(int)$arrWeekDayStat['day_of_week']] = $arrWeekDayStat['count'];
        }

        $objStmt = $objConn->prepare(static::QUERY_POSTGRES_GET_DAYHOUR_STATS_BY_COMMAND);
        $objStmt->bindValue('command', $strCommand);
        $objStmt->execute();
        $arrDayHourStats = $objStmt->fetchAll();

        $arrDayHourToCount = array_fill(0, 24, 0);
        foreach ($arrDayHourStats as $arrDayHourStat)
        {
            $arrDayHourToCount[(int)$arrDayHourStat['hour_of_day']] = $arrWeekDayStat['count'];
        }

        return $this->render('@RavuAlHemioSharpIrcBotWeb/counters/counter.html.twig', [
            'command' => $strCommand,
            'count' => $intCount,
            'recentEntries' => $arrTemplateRecent,
            'weekDayStats' => $arrWeekDayToCount,
            'dayHourStats' => $arrDayHourToCount
        ]);
    }
}