<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="base_nicknames", schema="nick_mapping")
 */
class BaseNickname
{
    /**
     * @var string
     * @ORM\Column(name="nickname", type="string")
     * @ORM\Id
     */
    public $strNickname;

    /**
     * @var string
     * @ORM\Column(name="nickname_lowercase", type="string")
     */
    public $strNicknameLowercase;

    /**
     * @var NickMapping[]
     * @ORM\OneToMany(targetEntity="NickMapping", mappedBy="objBaseNickname")
     */
    public $arrMappings;
}
