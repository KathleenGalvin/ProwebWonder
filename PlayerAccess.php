<?php

/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Entity;

use AppBundle\BackOffice\Entity\Player;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class PlayerAccess.
 *
 * @Gedmo\Loggable(logEntryClass="AppBundle\AdventCalendar\Entity\PlayerAccessHistory")
 *
 * @ORM\Table(
 *     name="advent_player_access",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="unique_player_campaign", columns={"campaign", "player"}),
 *     },
 *     indexes={
 *         @ORM\Index(name="fk_advent_player_access_1_idx", columns={"player"}),
 *         @ORM\Index(name="fk_advent_player_access_2_idx", columns={"campaign"}),
 *         @ORM\Index(name="fk_advent_player_access_3_idx", columns={"player_order"}),
 *         @ORM\Index(name="fk_advent_player_access_4_idx", columns={"date"}),
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="AppBundle\AdventCalendar\Repository\PlayerAccessRepository")
 */
class PlayerAccess
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var null|Player
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\BackOffice\Entity\Player", inversedBy="adventPlayerAccesses")
     * @ORM\JoinColumn(name="player", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Gedmo\Versioned
     */
    private $player = null;

    /**
     * @var null|Campaign
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\AdventCalendar\Entity\Campaign")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="campaign", referencedColumnName="id", onDelete="CASCADE")
     * })
     *
     * @Gedmo\Versioned
     */
    private $campaign = null;

    /**
     * @var int playerOrder
     *
     * @ORM\Column(name="player_order", type="integer")
     *
     * @Gedmo\Versioned
     */
    private $playerOrder;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     *
     * @Gedmo\Versioned
     */
    private $date;

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|Player
     */
    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    /**
     * @param null|Player $player
     *
     * @return PlayerAccess
     */
    public function setPlayer(?Player $player): PlayerAccess
    {
        $this->player = $player;

        return $this;
    }

    /**
     * @return null|Campaign
     */
    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    /**
     * @param null|Campaign $campaign
     *
     * @return PlayerAccess
     */
    public function setCampaign(?Campaign $campaign): PlayerAccess
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @return int
     */
    public function getPlayerOrder(): int
    {
        return $this->playerOrder;
    }

    /**
     * @param int $playerOrder
     *
     * @return PlayerAccess
     */
    public function setPlayerOrder(int $playerOrder): PlayerAccess
    {
        $this->playerOrder = $playerOrder;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return PlayerAccess
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }
}
