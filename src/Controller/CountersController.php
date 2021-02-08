<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Controller;

use RavuAlHemio\SharpIrcBotWebBundle\Entity\CounterEntry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CountersController extends AbstractController
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

    const QUERY_GET_PER_USER_COUNT_BY_COMMAND = '
        SELECT
            COALESCE(ce.strPerpUsername, ce.strPerpNickname) perp,
            COUNT(ce.strID) counterValue
        FROM
            RavuAlHemioSharpIrcBotWebBundle:CounterEntry ce
        WHERE
            ce.strCommand = :command
            AND ce.blnExpunged = FALSE
        GROUP BY
            perp
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

    const QUERY_POSTGRES_GET_WEEKDAY_STATS_PER_USER_BY_COMMAND = '
        SELECT
            COALESCE(perp_username, perp_nickname) AS perp,
            EXTRACT(DOW FROM happened_timestamp) AS day_of_week,
            COUNT(*) AS count
        FROM
            counters.entries
        WHERE
            command = :command
            AND expunged = FALSE
        GROUP BY
            COALESCE(perp_username, perp_nickname),
            EXTRACT(DOW FROM happened_timestamp)
    ';

    const QUERY_POSTGRES_GET_DAYHOUR_STATS_PER_USER_BY_COMMAND = '
        SELECT
            COALESCE(perp_username, perp_nickname) AS perp,
            EXTRACT(HOUR FROM happened_timestamp) AS hour_of_day,
            COUNT(*) AS count
        FROM
            counters.entries
        WHERE
            command = :command
            AND expunged = FALSE
        GROUP BY
            COALESCE(perp_username, perp_nickname),
            EXTRACT(HOUR FROM happened_timestamp)
    ';

    const QUERY_POSTGRES_GET_YEARMONTH_STATS_PER_USER_BY_COMMAND = '
        SELECT
            COALESCE(perp_username, perp_nickname) AS perp,
            EXTRACT(MONTH FROM happened_timestamp) AS month_of_year,
            COUNT(*) AS count
        FROM
            counters.entries
        WHERE
            command = :command
            AND expunged = FALSE
        GROUP BY
            COALESCE(perp_username, perp_nickname),
            EXTRACT(MONTH FROM happened_timestamp)
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

        $objQuery = $objEM->createQuery(static::QUERY_GET_PER_USER_COUNT_BY_COMMAND);
        $objQuery->setParameter('command', $strCommand);
        $arrUsersAndCounts = $objQuery->getResult();

        $arrUsernameToUser = [];
        $arrTotals = [
            'users' => 0,
            'count' => 0,
            'weekDayToCount' => array_fill(0, 7, 0),
            'dayHourToCount' => array_fill(0, 24, 0),
            'yearMonthToCount' => array_fill(0, 12, 0)
        ];
        foreach ($arrUsersAndCounts as $arrUserAndCount)
        {
            $strPerp = $arrUserAndCount['perp'];

            // prepare here
            $arrUsernameToUser[$strPerp] = [
                'username' => $strPerp,
                'count' => $arrUserAndCount['counterValue'],
                'weekDayToCount' => array_fill(0, 7, 0),
                'dayHourToCount' => array_fill(0, 24, 0),
                'yearMonthToCount' => array_fill(0, 12, 0)
            ];

            $arrTotals['users'] += 1;
            $arrTotals['count'] += $arrUserAndCount['counterValue'];
        }

        // calculate percentage
        foreach ($arrUsernameToUser as $strUsername => &$arrUser)
        {
            $numHundreds = \bcmul("{$arrUser['count']}", '100', 2);
            $numPercentage = \bcdiv($numHundreds, "{$arrTotals['count']}", 2);

            $arrUser['percentage'] = $numPercentage;
        }
        unset($arrUser);  // reference might dangle otherwise

        $objQuery = $objEM->createQuery(static::QUERY_GET_TOP_FIVE_MESSAGES);
        $objQuery->setParameter('command', $strCommand);
        $objQuery->setMaxResults(10);
        $arrLatestResults = $objQuery->getResult();
        $arrTemplateRecent = [];
        foreach ($arrLatestResults as $objEntry)
        {
            /** @var CounterEntry $objEntry */
            $arrTemplateRecent[] = [
                'perpNick' => $objEntry->strPerpNickname,
                'message' => $objEntry->strMessage,
                'happened' => $objEntry->dtmHappened
            ];
        }

        $objConn = $objEM->getConnection();

        $objStmt = $objConn->prepare(static::QUERY_POSTGRES_GET_WEEKDAY_STATS_PER_USER_BY_COMMAND);
        $objStmt->bindValue('command', $strCommand);
        $objStmt->execute();
        $arrWeekDayStats = $objStmt->fetchAll();

        foreach ($arrWeekDayStats as $arrWeekDayStat)
        {
            $strPerp = $arrWeekDayStat['perp'];
            $intDOW = (int)$arrWeekDayStat['day_of_week'];
            $arrUsernameToUser[$strPerp]['weekDayToCount'][$intDOW] = $arrWeekDayStat['count'];
            $arrTotals['weekDayToCount'][$intDOW] += $arrWeekDayStat['count'];
        }

        $objStmt = $objConn->prepare(static::QUERY_POSTGRES_GET_DAYHOUR_STATS_PER_USER_BY_COMMAND);
        $objStmt->bindValue('command', $strCommand);
        $objStmt->execute();
        $arrDayHourStats = $objStmt->fetchAll();

        foreach ($arrDayHourStats as $arrDayHourStat)
        {
            $strPerp = $arrDayHourStat['perp'];
            $intHour = (int)$arrDayHourStat['hour_of_day'];
            $arrUsernameToUser[$strPerp]['dayHourToCount'][$intHour] = $arrDayHourStat['count'];
            $arrTotals['dayHourToCount'][$intHour] += $arrDayHourStat['count'];
        }

        $objStmt = $objConn->prepare(static::QUERY_POSTGRES_GET_YEARMONTH_STATS_PER_USER_BY_COMMAND);
        $objStmt->bindValue('command', $strCommand);
        $objStmt->execute();
        $arrYearMonthStats = $objStmt->fetchAll();

        foreach ($arrYearMonthStats as $arrYearMonthStat)
        {
            $strPerp = $arrYearMonthStat['perp'];
            $intMonth = (int)$arrYearMonthStat['month_of_year'] - 1;
            $arrUsernameToUser[$strPerp]['yearMonthToCount'][$intMonth] = $arrYearMonthStat['count'];
            $arrTotals['yearMonthToCount'][$intMonth] += $arrYearMonthStat['count'];
        }

        ksort($arrUsernameToUser);

        return $this->render('@RavuAlHemioSharpIrcBotWeb/counters/counter.html.twig', [
            'command' => $strCommand,
            'recentEntries' => $arrTemplateRecent,
            'totals' => $arrTotals,
            'users' => array_values($arrUsernameToUser),
            'weekdayOrder' => [1, 2, 3, 4, 5, 6, 0]
        ]);
    }
}
