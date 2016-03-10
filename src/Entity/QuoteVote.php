<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="quote_votes", schema="quotes")
 */
class QuoteVote
{
    /**
     * @var string
     * @ORM\Column(name="vote_id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $numID;

    /**
     * @var Quote
     * @ORM\ManyToOne(targetEntity="Quote", inversedBy="arrVotes")
     * @ORM\JoinColumn(name="quote_id", referencedColumnName="quote_id")
     */
    public $objQuote;

    /**
     * @var string
     * @ORM\Column(name="voter_lowercase", type="string")
     */
    public $strVoterLowercase;

    /**
     * @var int
     * @ORM\Column(name="points", type="smallint")
     */
    public $intPoints;
}
