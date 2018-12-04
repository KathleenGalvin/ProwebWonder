<?php

/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Entity;

use AppBundle\BackOffice\Entity\Player;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AdventAttempt.
 *
 * @Gedmo\Loggable(logEntryClass="AppBundle\AdventCalendar\Entity\AttemptHistory")
 *
 * @ORM\Table(
 *     name="advent_attempt",
 *     indexes={
 *         @ORM\Index(name="fk_advent_attempt_1_idx", columns={"player"}),
 *         @ORM\Index(name="fk_advent_attempt_2_idx", columns={"campaign"}),
 *         @ORM\Index(name="fk_advent_attempt_3_idx", columns={"prize"}),
 *         @ORM\Index(name="fk_advent_attempt_4_idx", columns={"date"}),
 *     },
 * )
 * @ORM\Entity(repositoryClass="AppBundle\AdventCalendar\Repository\AttemptRepository")
 */
class Attempt
{
    const WINNING_ATTEMPT = 'winning_attempt';
    const LOSING_ATTEMPT = 'losing_attempt';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Player
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\BackOffice\Entity\Player", inversedBy="attempts")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="player", referencedColumnName="id", onDelete="CASCADE")
     * })
     *
     * @Gedmo\Versioned
     */
    private $player;

    /**
     * @var Prize
     *
     * @ORM\OneToOne(targetEntity="AppBundle\AdventCalendar\Entity\Prize")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="prize", referencedColumnName="id", onDelete="CASCADE")
     * })
     *
     * @Gedmo\Versioned
     */
    private $prize;

    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\AdventCalendar\Entity\Campaign")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="campaign", referencedColumnName="id", onDelete="CASCADE")
     * })
     *
     * @Gedmo\Versioned
     */
    private $campaign;

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
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param Player $player
     *
     * @return Attempt
     */
    public function setPlayer(Player $player): Attempt
    {
        $this->player = $player;

        return $this;
    }

    /**
     * @return Campaign
     */
    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    /**
     * @param Campaign $campaign
     *
     * @return Attempt
     */
    public function setCampaign(Campaign $campaign): Attempt
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @return Prize|null
     */
    public function getPrize(): ?Prize
    {
        return $this->prize;
    }

    /**
     * @param Prize $prize
     *
     * @return Attempt
     */
    public function setPrize(Prize $prize): Attempt
    {
        $this->prize = $prize;

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
     * @return Attempt
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }
}
