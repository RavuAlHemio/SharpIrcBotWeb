<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="entries", schema="counters")
 */
class BaseNickname
{
    /**
     * @var string
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     */
    public $strID;

    /**
     * @var string
     * @ORM\Column(name="command", type="string", length=255)
     */
    public $strCommand;

    /**
     * @var \DateTime
     * @ORM\Column(name="happened_timestamp", type="datetimetz")
     */
    public $dtmHappened;

    /**
     * @var \DateTime
     * @ORM\Column(name="counted_timestamp", type="datetimetz")
     */
    public $dtmCounted;

    /**
     * @var string
     * @ORM\Column(name="channel", type="string", length=255)
     */
    public $strChannel;

    /**
     * @var string
     * @ORM\Column(name="perp_nickname", type="string", length=255)
     */
    public $strPerpNickname;

    /**
     * @var string
     * @ORM\Column(name="perp_username", type="string", length=255, nullable=true)
     */
    public $strPerpUsername;

    /**
     * @var string
     * @ORM\Column(name="counter_nickname", type="string", length=255)
     */
    public $strCounterNickname;

    /**
     * @var string
     * @ORM\Column(name="counter_username", type="string", length=255, nullable=true)
     */
    public $strCounterUsername;

    /**
     * @var string
     * @ORM\Column(name="message", type="text")
     */
    public $strMessage;

    /**
     * @var boolean
     * @ORM\Column(name="expunged", type="boolean")
     */
    public $blnExpunged;
}
