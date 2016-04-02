<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="quotes", schema="quotes")
 */
class Quote
{
    /**
     * @var string
     * @ORM\Column(name="quote_id", type="bigint")
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
     * @ORM\Column(name="channel", type="string")
     */
    public $strChannel;

    /**
     * @var string
     * @ORM\Column(name="author", type="string")
     */
    public $strAuthor;

    /**
     * @var string
     * @ORM\Column(name="message_type", type="string", length=1)
     */
    public $strMessageType;

    /**
     * @var string
     * @ORM\Column(name="body", type="string")
     */
    public $strBody;

    /**
     * @var QuoteVote[]
     * @ORM\OneToMany(targetEntity="QuoteVote", mappedBy="objQuote")
     */
    public $arrVotes;
}
