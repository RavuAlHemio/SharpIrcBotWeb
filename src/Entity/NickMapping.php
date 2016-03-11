<?php

namespace RavuAlHemio\SharpIrcBotWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="nick_mappings", schema="nick_mapping")
 */
class NickMapping
{
    /**
     * @var BaseNickname
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="NickMapping", inversedBy="arrMappings")
     * @ORM\JoinColumn(name="base_nickname", referencedColumnName="nickname")
     */
    public $objBaseNickname;

    /**
     * @var string
     * @ORM\Column(name="mapped_nickname_lower", type="string")
     */
    public $strMappedNicknameLowercase;
}
