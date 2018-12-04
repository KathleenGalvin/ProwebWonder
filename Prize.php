<?php

/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * AdventPrize.
 *
 * @Gedmo\Loggable(logEntryClass="AppBundle\AdventCalendar\Entity\PrizeHistory")
 *
 * @ORM\Table(name="advent_prize")
 * @ORM\Entity(repositoryClass="AppBundle\AdventCalendar\Repository\PrizeRepository")
 */
class Prize
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     *
     * @Gedmo\Versioned
     */
    private $title = '';

    /**
     * @var null|Campaign
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\AdventCalendar\Entity\Campaign", inversedBy="prizes")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="campaign", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     *
     * @Gedmo\Versioned
     */
    private $campaign = null;

    /**
     * @var null|Attempt
     *
     * @ORM\OneToOne(targetEntity="AppBundle\AdventCalendar\Entity\Attempt")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="attempt", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     *
     * @Gedmo\Versioned
     */
    private $attempt = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_big_prize", type="boolean", nullable=false)
     *
     * @Gedmo\Versioned
     */
    private $isBigPrize = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Gedmo\Versioned
     */
    protected $description = null;

    /**
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", length=15, nullable=false)
     *
     * @Gedmo\Versioned
     */
    private $identifier = '';

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     *
     * @Gedmo\Versioned
     */
    private $picture;

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Prize
     */
    public function setTitle(string $title): Prize
    {
        $this->title = $title;

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
     * @return Prize
     */
    public function setCampaign(?Campaign $campaign): Prize
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @return Attempt|null
     */
    public function getAttempt(): ?Attempt
    {
        return $this->attempt;
    }

    /**
     * @param null|Attempt $attempt
     *
     * @return Prize
     */
    public function setAttempt(?Attempt $attempt): Prize
    {
        $this->attempt = $attempt;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsBigPrize(): bool
    {
        return $this->isBigPrize;
    }

    /**
     * @param bool $isBigPrize
     *
     * @return Prize
     */
    public function setBigPrize(bool $isBigPrize): Prize
    {
        $this->isBigPrize = $isBigPrize;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return Prize
     */
    public function setDescription(?string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @return Prize
     */
    public function setIdentifier(string $identifier): Prize
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return string|UploadedFile
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param string|UploadedFile $picture
     *
     * @return Prize
     */
    public function setPicture($picture): Prize
    {
        $this->picture = $picture;

        return $this;
    }
}
