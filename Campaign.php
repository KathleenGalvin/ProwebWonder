<?php
/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Entity;

use AppBundle\BackOffice\Entity\AbstractCampaign;
use AppBundle\BackOffice\Validator\Constraints as AppBundleAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AdventCampaign.
 *
 * @ORM\Table(
 *     name="advent_campaign",
 *     indexes={
 *         @ORM\Index(name="fk_customer_access_1_idx", columns={"customer"}),
 *         @ORM\Index(name="fk_customer_access_2_idx", columns={"board"}),
 *     },
 * )
 * @ORM\Entity(repositoryClass="AppBundle\AdventCalendar\Repository\CampaignRepository")
 *
 * @Gedmo\Loggable(logEntryClass="AppBundle\AdventCalendar\Entity\CampaignHistory")
 */
class Campaign extends AbstractCampaign
{
    /**
     * @var Board
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\AdventCalendar\Entity\Board", inversedBy="campaigns")
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(name="board", referencedColumnName="id", nullable=false)
     * })
     *
     * @Gedmo\Versioned
     */
    protected $board;

    /**
     * @var null|string
     *
     * @ORM\Column(name="day_potential_winners", type="string", nullable=true)
     */
    private $dayPotentialWinners = null;

    /**
     * @var null|string
     *
     * @ORM\Column(name="email_frequency", type="string", nullable=true)
     *
     * @Gedmo\Versioned
     */
    private $emailFrequency = null;

    /**
     * @var null|string
     *
     * @ORM\Column(name="email_address", type="string", nullable=true)
     *
     * @AppBundleAssert\InvalidEmailAddress
     *
     * @Assert\Expression(
     *     "this.getEmailFrequency() === null or (this.getEmailFrequency() !== null and this.getEmailAddress() !== null)",
     *     message="campaign.emailAddress.constraint.notBlank.message",
     * )
     *
     * @Gedmo\Versioned
     */
    private $emailAddress = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="send_email_time", type="integer", options={"default": 8})
     *
     * @Gedmo\Versioned
     */
    private $sendEmailTime = 8;

    /**
     * @var string|null
     *
     * @ORM\Column(name="personalized_CSS", type="text", nullable=true)
     *
     * @Gedmo\Versioned
     */
    private $personalizedCSS = null;

    /**
     * @var bool
     *
     * @ORM\Column(name="use_personalized_CSS", type="boolean")
     *
     * @Gedmo\Versioned
     */
    private $usePersonalizedCSS = false;

    /**
     * @var int|null
     *
     * @ORM\Column(name="players_number_estimated", type="integer", nullable=true)
     *
     * @Assert\NotBlank(message = "campaign.playersNumberEstimated.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $playersNumberEstimated = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="prize_limit_by_player", type="integer", options={"default": 0})
     *
     * @Assert\NotBlank(message = "campaign.prizeLimitByPlayer.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $prizeLimitByPlayer = null;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\AdventCalendar\Entity\Prize", mappedBy="campaign", cascade={"persist"})
     */
    private $prizes;

    /**
     * @var string
     *
     * @ORM\Column(name="color_primary", type="string", length=10, options={"default": "#ef6363"})
     *
     * @Gedmo\Versioned
     */
    private $colorPrimary = '#ef6363';

    /**
     * @var string
     *
     * @ORM\Column(name="color_second", type="string", length=10, options={"default": "#126c59"})
     *
     * @Gedmo\Versioned
     */
    private $colorSecond = '#126c59';

    /**
     * @var string
     *
     * @ORM\Column(name="color_declined_one", type="string", length=10, options={"default": "#b81313"})
     *
     * @Gedmo\Versioned
     */
    private $colorDeclinedOne = '#b81313';

    /**
     * @var string
     *
     * @ORM\Column(name="color_declined_two", type="string", length=10, options={"default": "#7b0d0d"})
     *
     * @Gedmo\Versioned
     */
    private $colorDeclinedTwo = '#7b0d0d';

    /**
     * @var string
     *
     * @ORM\Column(name="color_background", type="string", length=10, options={"default": "#ffffff"})
     *
     * @Gedmo\Versioned
     */
    private $colorBackground = '#ffffff';

    /**
     * @var string
     *
     * @ORM\Column(name="background_header", type="string", length=255, nullable=true)
     *
     * @Gedmo\Versioned
     */
    private $backgroundHeader;

    /**
     * @var string
     *
     * @ORM\Column(name="background", type="string", length=100, nullable=true)
     *
     * @Gedmo\Versioned
     */
    private $background;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="begin_at", type="date", nullable=true)
     *
     * @Gedmo\Versioned
     */
    private $beginAt = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end_at", type="date", nullable=true)
     *
     * @Gedmo\Versioned
     */
    private $endAt = null;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_days_per_line", type="integer", options={"default": 6})
     *
     * @Gedmo\Versioned
     */
    private $maxDaysPerLine = 6;

    /**
     * @var string
     *
     * @ORM\Column(name="msg_open", type="string", length=255, options={"default": "Ouvrir"})
     *
     * @Gedmo\Versioned
     */
    private $msgOpen = 'Ouvrir';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_won", type="string", length=255, options={"default": "Gagné !"})
     *
     * @Gedmo\Versioned
     */
    private $msgWon = 'Gagné !';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_lost", type="string", length=255, options={"default": "Perdu !"})
     *
     * @Gedmo\Versioned
     */
    private $msgLost = 'Perdu !';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_won_instruction", type="string", length=255, options={"default": "Félicitations ! Vous avez gagné..."})
     *
     * @Gedmo\Versioned
     */
    private $msgWonInstruction = 'Félicitations ! Vous avez gagné...';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_lost_instruction", type="string", length=255, options={"default": "Désolé..."})
     *
     * @Gedmo\Versioned
     */
    private $msgLostInstruction = 'Désolé...';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_lost_text_one", type="string", length=255, options={"default": "Vous avez perdu..."})
     *
     * @Gedmo\Versioned
     */
    private $msgLostTextOne = 'Vous avez perdu...';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_lost_text_two", type="string", length=255, options={"default": "N'hésitez pas à réessayer demain !"})
     *
     * @Gedmo\Versioned
     */
    private $msgLostTextTwo = "N'hésitez pas à réessayer demain !";

    /**
     * @var string
     *
     * @ORM\Column(name="msg_lost_text_two_end", type="string", length=255, options={"default": "Nous espérons que vous aurez plus de chance la prochaine fois"})
     *
     * @Gedmo\Versioned
     */
    private $msgLostTextTwoEnd = 'Nous espérons que vous aurez plus de chance la prochaine fois';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_download_pdf", type="string", length=255, options={"default": "Télécharger le récapitulatif du lot en PDF"})
     *
     * @Gedmo\Versioned
     */
    private $msgDownloadPdf = 'Télécharger le récapitulatif du lot en PDF';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_sending_email_prize_summary", type="string", length=255, options={"default": "M'envoyer par e-mail le récapitulatif du lot"})
     *
     * @Gedmo\Versioned
     */
    private $msgSendingEmailPrizeSummary = "M'envoyer par e-mail le récapitulatif du lot";

    /**
     * @var string
     *
     * @ORM\Column(name="msg_email_address_summary", type="string", length=255, options={"default": "Entrez-votre adresse e-mail:"})
     *
     * @Gedmo\Versioned
     */
    private $msgEmailAddressSummary = 'Entrez-votre adresse e-mail:';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_sending_email_button", type="string", length=255, options={"default": "Envoyer"})
     *
     * @Gedmo\Versioned
     */
    private $msgSendingEmailButton = 'Envoyer';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_sending_email_in_progress", type="string", length=255, options={"default": "Envoi de l'e-mail en cours"})
     *
     * @Gedmo\Versioned
     */
    private $msgSendingEmailInProgress = "Envoi de l'e-mail en cours";

    /**
     * @var string
     *
     * @ORM\Column(name="msg_email_sent", type="string", length=255, options={"default": "E-mail envoyé"})
     *
     * @Gedmo\Versioned
     */
    private $msgEmailSent = 'E-mail envoyé';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_email_error", type="string", length=255, options={"default": "Erreur lors de l'envoi de l'e-mail. Vérifiez votre adresse e-mail dans votre fiche utilisateur et retentez l'opération"})
     *
     * @Gedmo\Versioned
     */
    private $msgEmailError = "Erreur lors de l'envoi de l'e-mail. Vérifiez votre adresse e-mail dans votre fiche utilisateur et retentez l'opération";

    /**
     * Campaign constructor.
     */
    public function __construct()
    {
        $this->prizes = new ArrayCollection();
    }

    /**
     * Get dayPotentialWinners.
     *
     * @return null|string
     */
    public function getDayPotentialWinners(): ?string
    {
        return $this->dayPotentialWinners;
    }

    /**
     * Set dayPotentialWinners.
     *
     * @param null|string $dayPotentialWinners
     *
     * @return Campaign
     */
    public function setDayPotentialWinners(?string $dayPotentialWinners): Campaign
    {
        $this->dayPotentialWinners = $dayPotentialWinners;

        return $this;
    }

    /**
     * Get emailFrequency.
     *
     * @return null|string
     */
    public function getEmailFrequency(): ?string
    {
        return $this->emailFrequency;
    }

    /**
     * Set emailFrequency.
     *
     * @param null|string $emailFrequency
     *
     * @return Campaign
     */
    public function setEmailFrequency(?string $emailFrequency): Campaign
    {
        if (!\in_array($emailFrequency, [self::ONCE_PER_DAY, self::ONCE_PER_WEEK])) {
            $emailFrequency = null;
        }

        $this->emailFrequency = $emailFrequency;

        return $this;
    }

    /**
     * Get emailAddress.
     *
     * @return null|string
     */
    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    /**
     * Set emailAddress.
     *
     * @param null|string $emailAddress
     *
     * @return Campaign
     */
    public function setEmailAddress(?string $emailAddress): Campaign
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * Get sendEmailTime.
     *
     * @return int
     */
    public function getSendEmailTime(): int
    {
        return $this->sendEmailTime;
    }

    /**
     * set sendEmailTime.
     *
     * @param int $sendEmailTime
     *
     * @return Campaign
     */
    public function setSendEmailTime(int $sendEmailTime): Campaign
    {
        $this->sendEmailTime = $sendEmailTime;

        return $this;
    }

    /**
     * Get personalizedCSS.
     *
     * @return null|string
     */
    public function getPersonalizedCSS(): ?string
    {
        return $this->personalizedCSS;
    }

    /**
     * Set personalizedCSS.
     *
     * @param null|string $personalizedCSS
     *
     * @return Campaign
     */
    public function setPersonalizedCSS(?string $personalizedCSS): Campaign
    {
        $this->personalizedCSS = $personalizedCSS;

        return $this;
    }

    /**
     * Get usePersonalizedCSS.
     *
     * @return bool
     */
    public function isUsePersonalizedCSS(): bool
    {
        return $this->usePersonalizedCSS;
    }

    /**
     * Set usePersonalizedCSS.
     *
     * @param bool $usePersonalizedCSS
     *
     * @return Campaign
     */
    public function setUsePersonalizedCSS(bool $usePersonalizedCSS): Campaign
    {
        $this->usePersonalizedCSS = $usePersonalizedCSS;

        return $this;
    }

    /**
     * Get playersNumberEstimated.
     *
     * @return int|null
     */
    public function getPlayersNumberEstimated(): ?int
    {
        return $this->playersNumberEstimated;
    }

    /**
     * Set playersNumberEstimated.
     *
     * @param int|null $playersNumberEstimated
     *
     * @return Campaign
     */
    public function setPlayersNumberEstimated(?int $playersNumberEstimated): Campaign
    {
        $this->playersNumberEstimated = $playersNumberEstimated;

        return $this;
    }

    /**
     * Get prizeLimitByPlayer.
     *
     * @return int|null
     */
    public function getPrizeLimitByPlayer(): ?int
    {
        return $this->prizeLimitByPlayer;
    }

    /**
     * Set prizeLimitByPlayer.
     *
     * @param int|null $prizeLimitByPlayer
     *
     * @return Campaign
     */
    public function setPrizeLimitByPlayer(?int $prizeLimitByPlayer): Campaign
    {
        $this->prizeLimitByPlayer = $prizeLimitByPlayer;

        return $this;
    }

    /**
     * Add a prize.
     *
     * @param Prize $prize
     *
     * @return Campaign
     */
    public function addPrize(Prize $prize): Campaign
    {
        $prize->setCampaign($this);
        $this->prizes->add($prize);

        return $this;
    }

    /**
     * Remove a prize.
     *
     * @param Prize $prize
     *
     * @return Campaign
     */
    public function removePrize(Prize $prize): Campaign
    {
        $this->prizes->removeElement($prize);

        return $this;
    }

    /**
     * Get prizes.
     *
     * @return Collection
     */
    public function getPrizes(): ?Collection
    {
        return $this->prizes;
    }

    /**
     * Set prizes.
     *
     * @param Collection $prizes
     *
     * @return Campaign
     */
    public function setPrizes(Collection $prizes): Campaign
    {
        foreach ($prizes as $prize) {
            $prize->setCampaign($this);
        }

        $this->prizes = $prizes;

        return $this;
    }

    /**
     * Get colorPrimary.
     *
     * @return string|null
     */
    public function getColorPrimary(): ?string
    {
        return $this->colorPrimary;
    }

    /**
     * Set colorPrimary.
     *
     * @param string|null $colorPrimary
     *
     * @return Campaign
     */
    public function setColorPrimary(?string $colorPrimary): Campaign
    {
        $this->colorPrimary = $colorPrimary;

        return $this;
    }

    /**
     * Get colorSecond.
     *
     * @return string|null
     */
    public function getColorSecond(): ?string
    {
        return $this->colorSecond;
    }

    /**
     * Set colorSecond.
     *
     * @param string|null $colorSecond
     *
     * @return Campaign
     */
    public function setColorSecond(?string $colorSecond): Campaign
    {
        $this->colorSecond = $colorSecond;

        return $this;
    }

    /**
     * @return string
     */
    public function getColorDeclinedOne(): ?string
    {
        return $this->colorDeclinedOne;
    }

    /**
     * @param string $colorDeclinedOne
     *
     * @return Campaign
     */
    public function setColorDeclinedOne(?string $colorDeclinedOne): Campaign
    {
        $this->colorDeclinedOne = $colorDeclinedOne;

        return $this;
    }

    /**
     * @return string
     */
    public function getColorDeclinedTwo(): ?string
    {
        return $this->colorDeclinedTwo;
    }

    /**
     * @param string $colorDeclinedTwo
     *
     * @return Campaign
     */
    public function setColorDeclinedTwo(?string $colorDeclinedTwo): Campaign
    {
        $this->colorDeclinedTwo = $colorDeclinedTwo;

        return $this;
    }

    /**
     * Get colorBackground.
     *
     * @return string|null
     */
    public function getColorBackground(): ?string
    {
        return $this->colorBackground;
    }

    /**
     * Set colorBackground.
     *
     * @param string|null $colorBackground
     *
     * @return Campaign
     */
    public function setColorBackground(?string $colorBackground): Campaign
    {
        $this->colorBackground = $colorBackground;

        return $this;
    }

    /**
     * Get backgroundHeader.
     *
     * @return string|UploadedFile
     */
    public function getBackgroundHeader()
    {
        return $this->backgroundHeader;
    }

    /**
     * Set backgroundHeader.
     *
     * @param string|UploadedFile $backgroundHeader
     *
     * @return Campaign
     */
    public function setBackgroundHeader($backgroundHeader): Campaign
    {
        $this->backgroundHeader = $backgroundHeader;

        return $this;
    }

    /**
     * Get background.
     *
     * @return string|UploadedFile
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * Set background.
     *
     * @param string|UploadedFile $background
     *
     * @return Campaign
     */
    public function setBackground($background): Campaign
    {
        $this->background = $background;

        return $this;
    }

    /**
     * Get beginAt.
     *
     * @return \DateTime|null
     */
    public function getBeginAt(): ?\DateTime
    {
        return $this->beginAt;
    }

    /**
     * Set beginAt.
     *
     * @param \DateTime|null $beginAt
     *
     * @return Campaign
     */
    public function setBeginAt(?\DateTime $beginAt): Campaign
    {
        $this->beginAt = $beginAt;

        return $this;
    }

    /**
     * Get endAt.
     *
     * @return \DateTime|null
     */
    public function getEndAt(): ?\DateTime
    {
        return $this->endAt;
    }

    /**
     * Set endAt.
     *
     * @param \DateTime|null $endAt
     *
     * @return Campaign
     */
    public function setEndAt(?\DateTime $endAt): Campaign
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * Get maxDaysPerLine.
     *
     * @return int|null
     */
    public function getMaxDaysPerLine(): ?int
    {
        return $this->maxDaysPerLine;
    }

    /**
     * Set maxDaysPerLine.
     *
     * @param int|null $maxDaysPerLine
     *
     * @return Campaign
     */
    public function setMaxDaysPerLine(?int $maxDaysPerLine): Campaign
    {
        $this->maxDaysPerLine = $maxDaysPerLine;

        return $this;
    }

    /**
     * Get msgOpen.
     *
     * @return string|null
     */
    public function getMsgOpen(): ?string
    {
        return $this->msgOpen;
    }

    /**
     * Set msgOpen.
     *
     * @param string|null $msgOpen
     *
     * @return Campaign
     */
    public function setMsgOpen(?string $msgOpen): Campaign
    {
        $this->msgOpen = $msgOpen;

        return $this;
    }

    /**
     * Get msgWon.
     *
     * @return string|null
     */
    public function getMsgWon(): ?string
    {
        return $this->msgWon;
    }

    /**
     * Set msgWon.
     *
     * @param string|null $msgWon
     *
     * @return Campaign
     */
    public function setMsgWon(?string $msgWon): Campaign
    {
        $this->msgWon = $msgWon;

        return $this;
    }

    /**
     * Get msgLost.
     *
     * @return string|null
     */
    public function getMsgLost(): ?string
    {
        return $this->msgLost;
    }

    /**
     * Set msgLost.
     *
     * @param string|null $msgLost
     *
     * @return Campaign
     */
    public function setMsgLost(?string $msgLost): Campaign
    {
        $this->msgLost = $msgLost;

        return $this;
    }

    /**
     * Get msgWonInstruction.
     *
     * @return string|null
     */
    public function getMsgWonInstruction(): ?string
    {
        return $this->msgWonInstruction;
    }

    /**
     * Set msgWonInstruction.
     *
     * @param string|null $msgWonInstruction
     *
     * @return Campaign
     */
    public function setMsgWonInstruction(?string $msgWonInstruction): Campaign
    {
        $this->msgWonInstruction = $msgWonInstruction;

        return $this;
    }

    /**
     * Get msgLostInstruction.
     *
     * @return string|null
     */
    public function getMsgLostInstruction(): ?string
    {
        return $this->msgLostInstruction;
    }

    /**
     * Set msgLostInstruction.
     *
     * @param string|null $msgLostInstruction
     *
     * @return Campaign
     */
    public function setMsgLostInstruction(?string $msgLostInstruction): Campaign
    {
        $this->msgLostInstruction = $msgLostInstruction;

        return $this;
    }

    /**
     * Get msgLostTextOne.
     *
     * @return string|null
     */
    public function getMsgLostTextOne(): ?string
    {
        return $this->msgLostTextOne;
    }

    /**
     * Set msgLostTextOne.
     *
     * @param string|null $msgLostTextOne
     *
     * @return Campaign
     */
    public function setMsgLostTextOne(?string $msgLostTextOne): Campaign
    {
        $this->msgLostTextOne = $msgLostTextOne;

        return $this;
    }

    /**
     * Get msgLostTextTwo.
     *
     * @return string|null
     */
    public function getMsgLostTextTwo(): ?string
    {
        return $this->msgLostTextTwo;
    }

    /**
     * Set msgLostTextTwo.
     *
     * @param string|null $msgLostTextTwo
     *
     * @return Campaign
     */
    public function setMsgLostTextTwo(?string $msgLostTextTwo): Campaign
    {
        $this->msgLostTextTwo = $msgLostTextTwo;

        return $this;
    }

    /**
     * Get msgLostTextTwoEnd.
     *
     * @return string|null
     */
    public function getMsgLostTextTwoEnd(): ?string
    {
        return $this->msgLostTextTwoEnd;
    }

    /**
     * Set msgLostTextTwoEnd.
     *
     * @param string|null $msgLostTextTwoEnd
     *
     * @return Campaign
     */
    public function setMsgLostTextTwoEnd(?string $msgLostTextTwoEnd): Campaign
    {
        $this->msgLostTextTwoEnd = $msgLostTextTwoEnd;

        return $this;
    }

    /**
     * Get msgDownloadPdf.
     *
     * @return string|null
     */
    public function getMsgDownloadPdf(): ?string
    {
        return $this->msgDownloadPdf;
    }

    /**
     * Set msgDownloadPdf.
     *
     * @param string|null $msgDownloadPdf
     *
     * @return Campaign
     */
    public function setMsgDownloadPdf(?string $msgDownloadPdf): Campaign
    {
        $this->msgDownloadPdf = $msgDownloadPdf;

        return $this;
    }

    /**
     * Get msgSendingEmailPrizeSummary.
     *
     * @return string|null
     */
    public function getMsgSendingEmailPrizeSummary(): ?string
    {
        return $this->msgSendingEmailPrizeSummary;
    }

    /**
     * Set msgSendingEmailPrizeSummary.
     *
     * @param string|null $msgSendingEmailPrizeSummary
     *
     * @return Campaign
     */
    public function setMsgSendingEmailPrizeSummary(?string $msgSendingEmailPrizeSummary): Campaign
    {
        $this->msgSendingEmailPrizeSummary = $msgSendingEmailPrizeSummary;

        return $this;
    }

    /**
     * Get msgEmailAddressSummary.
     *
     * @return string|null
     */
    public function getMsgEmailAddressSummary(): ?string
    {
        return $this->msgEmailAddressSummary;
    }

    /**
     * Set msgEmailAddressSummary.
     *
     * @param string|null $msgEmailAddressSummary
     *
     * @return Campaign
     */
    public function setMsgEmailAddressSummary(?string $msgEmailAddressSummary): Campaign
    {
        $this->msgEmailAddressSummary = $msgEmailAddressSummary;

        return $this;
    }

    /**
     * Get msgSendingEmailButton.
     *
     * @return string|null
     */
    public function getMsgSendingEmailButton(): ?string
    {
        return $this->msgSendingEmailButton;
    }

    /**
     * Set msgSendingEmailButton.
     *
     * @param string|null $msgSendingEmailButton
     *
     * @return Campaign
     */
    public function setMsgSendingEmailButton(?string $msgSendingEmailButton): Campaign
    {
        $this->msgSendingEmailButton = $msgSendingEmailButton;

        return $this;
    }

    /**
     * Get msgSendingEmailInProgress.
     *
     * @return string|null
     */
    public function getMsgSendingEmailInProgress(): ?string
    {
        return $this->msgSendingEmailInProgress;
    }

    /**
     * Set msgSendingEmailInProgress.
     *
     * @param string|null $msgSendingEmailInProgress
     *
     * @return Campaign
     */
    public function setMsgSendingEmailInProgress(?string $msgSendingEmailInProgress): Campaign
    {
        $this->msgSendingEmailInProgress = $msgSendingEmailInProgress;

        return $this;
    }

    /**
     * Get msgEmailSent.
     *
     * @return string|null
     */
    public function getMsgEmailSent(): ?string
    {
        return $this->msgEmailSent;
    }

    /**
     * Set msgEmailSent.
     *
     * @param string|null $msgEmailSent
     *
     * @return Campaign
     */
    public function setMsgEmailSent(?string $msgEmailSent): Campaign
    {
        $this->msgEmailSent = $msgEmailSent;

        return $this;
    }

    /**
     * Get msgEmailError.
     *
     * @return string|null
     */
    public function getMsgEmailError(): ?string
    {
        return $this->msgEmailError;
    }

    /**
     * Set msgEmailError.
     *
     * @param string|null $msgEmailError
     *
     * @return Campaign
     */
    public function setMsgEmailError(?string $msgEmailError): Campaign
    {
        $this->msgEmailError = $msgEmailError;

        return $this;
    }
}
