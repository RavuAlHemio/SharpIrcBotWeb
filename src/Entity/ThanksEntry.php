<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="thanks", schema="thanks")
 */
class ThanksEntry
{
    /**
     * @var string
     * @ORM\Column(name="thanks_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $numID;

    /**
     * @var \DateTime
     * @ORM\Column(name="timestamp", type="datetime")
     */
    public $dtmTimestamp;

    /**
     * @var string
     * @ORM\Column(name="thanker_lowercase", type="string")
     */
    public $strThankerLowercase;

    /**
     * @var string
     * @ORM\Column(name="thankee_lowercase", type="string")
     */
    public $strThankeeLowercase;

    /**
     * @var string
     * @ORM\Column(name="channel", type="string")
     */
    public $strChannel;

    /**
     * @var bool
     * @ORM\Column(name="deleted", type="boolean")
     */
    public $blnDeleted;
}