<?php
/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Entity;

use AppBundle\BackOffice\Entity\AbstractBoard;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AdventBoard.
 *
 * @ORM\Table(name="advent_board")
 * @ORM\Entity(repositoryClass="AppBundle\AdventCalendar\Repository\BoardRepository")
 *
 * @Gedmo\Loggable(logEntryClass="AppBundle\AdventCalendar\Entity\BoardHistory")
 */
class Board extends AbstractBoard
{
    const DEFAULT_DESCRIPTION = 'Cliquez sur la case du jour pour savoir si vous avez gagné !';

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\AdventCalendar\Entity\Campaign", mappedBy="board", cascade={"persist", "remove"})
     */
    protected $campaigns;

    /**
     * @var string
     *
     * @ORM\Column(name="color_primary", type="string", length=10, options={"default": "#ef6363"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.colorPrimary.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $colorPrimary = '#ef6363';

    /**
     * @var string
     *
     * @ORM\Column(name="color_second", type="string", length=10, options={"default": "#126c59"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.colorSecond.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $colorSecond = '#126c59';

    /**
     * @var string
     *
     * @ORM\Column(name="color_declined_one", type="string", length=10, options={"default": "#b81313"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.colorDeclinedOne.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $colorDeclinedOne = '#b81313';

    /**
     * @var string
     *
     * @ORM\Column(name="color_declined_two", type="string", length=10, options={"default": "#7b0d0d"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.colorDeclinedTwo.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $colorDeclinedTwo = '#7b0d0d';

    /**
     * @var string
     *
     * @ORM\Column(name="color_background", type="string", length=10, options={"default": "#ffffff"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.colorBackground.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $colorBackground = '#ffffff';

    /**
     * @var null|string
     *
     * @ORM\Column(name="background_header", type="string", length=255, nullable=true)
     *
     * @Gedmo\Versioned
     */
    private $backgroundHeader;

    /**
     * @var null|string
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
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.date.constraint.message")
     *
     * @Gedmo\Versioned
     */
    private $beginAt = null;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end_at", type="date", nullable=true)
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.date.constraint.message")
     *
     * @Gedmo\Versioned
     */
    private $endAt = null;

    /**
     * @var int
     *
     * @ORM\Column(name="max_days_per_line", type="integer", options={"default": 6})
     *
     * @Assert\Range(
     *     groups={"secondForm"},
     *     min = 3,
     *     max = 12,
     *     minMessage = "board.maxDaysPerLine.constraint.message",
     *     maxMessage = "board.maxDaysPerLine.constraint.message"
     * )
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.maxDaysPerLine.constraint.message")
     *
     * @Gedmo\Versioned
     */
    private $maxDaysPerLine = 6;

    /**
     * @var string
     *
     * @ORM\Column(name="msg_open", type="string", length=255, options={"default": "Ouvrir"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgOpen = 'Ouvrir';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_won", type="string", length=255, options={"default": "Gagné !"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgWon = 'Gagné !';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_lost", type="string", length=255, options={"default": "Perdu !"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgLost = 'Perdu !';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_won_instruction", type="string", length=255, options={"default": "Félicitations ! Vous avez gagné..."})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgWonInstruction = 'Félicitations ! Vous avez gagné...';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_lost_instruction", type="string", length=255, options={"default": "Désolé..."})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgLostInstruction = 'Désolé...';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_lost_text_one", type="string", length=255, options={"default": "Vous avez perdu..."})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgLostTextOne = 'Vous avez perdu...';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_lost_text_two", type="string", length=255, options={"default": "N'hésitez pas à réessayer demain !"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgLostTextTwo = "N'hésitez pas à réessayer demain !";

    /**
     * @var string
     *
     * @ORM\Column(name="msg_lost_text_two_end", type="string", length=255, options={"default": "Nous espérons que vous aurez plus de chance la prochaine fois"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgLostTextTwoEnd = 'Nous espérons que vous aurez plus de chance la prochaine fois';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_download_pdf", type="string", length=255, options={"default": "Télécharger le récapitulatif du lot en PDF"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgDownloadPdf = 'Télécharger le récapitulatif du lot en PDF';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_sending_email_prize_summary", type="string", length=255, options={"default": "M'envoyer par e-mail le récapitulatif du lot"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgSendingEmailPrizeSummary = "M'envoyer par e-mail le récapitulatif du lot";

    /**
     * @var string
     *
     * @ORM\Column(name="msg_email_address_summary", type="string", length=255, options={"default": "Entrez-votre adresse e-mail :"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgEmailAddressSummary = 'Entrez-votre adresse e-mail:';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_sending_email_button", type="string", length=255, options={"default": "Envoyer"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgSendingEmailButton = 'Envoyer';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_sending_email_in_progress", type="string", length=255, options={"default": "Envoi de l'e-mail en cours"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgSendingEmailInProgress = "Envoi de l'e-mail en cours";

    /**
     * @var string
     *
     * @ORM\Column(name="msg_email_sent", type="string", length=255, options={"default": "E-mail envoyé"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgEmailSent = 'E-mail envoyé';

    /**
     * @var string
     *
     * @ORM\Column(name="msg_email_error", type="string", length=255, options={"default": "Erreur lors de l'envoi de l'e-mail. Vérifiez votre adresse e-mail dans votre fiche utilisateur et retentez l'opération"})
     *
     * @Assert\NotBlank(groups={"secondForm"}, message = "board.msg.constraint.notBlank.message")
     *
     * @Gedmo\Versioned
     */
    private $msgEmailError = "Erreur lors de l'envoi de l'e-mail. Vérifiez votre adresse e-mail dans votre fiche utilisateur et retentez l'opération";

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
     * @return string
     */
    public function getColorPrimary(): string
    {
        return $this->colorPrimary;
    }

    /**
     * @param string $colorPrimary
     *
     * @return Board
     */
    public function setColorPrimary(?string $colorPrimary): Board
    {
        $this->colorPrimary = $colorPrimary;

        return $this;
    }

    /**
     * @return string
     */
    public function getColorSecond(): string
    {
        return $this->colorSecond;
    }

    /**
     * @param string $colorSecond
     *
     * @return Board
     */
    public function setColorSecond(?string $colorSecond): Board
    {
        $this->colorSecond = $colorSecond;

        return $this;
    }

    /**
     * @return string
     */
    public function getColorDeclinedOne(): string
    {
        return $this->colorDeclinedOne;
    }

    /**
     * @param string $colorDeclinedOne
     *
     * @return Board
     */
    public function setColorDeclinedOne(?string $colorDeclinedOne): Board
    {
        $this->colorDeclinedOne = $colorDeclinedOne;

        return $this;
    }

    /**
     * @return string
     */
    public function getColorDeclinedTwo(): string
    {
        return $this->colorDeclinedTwo;
    }

    /**
     * @param string $colorDeclinedTwo
     *
     * @return Board
     */
    public function setColorDeclinedTwo(?string $colorDeclinedTwo): Board
    {
        $this->colorDeclinedTwo = $colorDeclinedTwo;

        return $this;
    }

    /**
     * @return string
     */
    public function getColorBackground(): string
    {
        return $this->colorBackground;
    }

    /**
     * @param string $colorBackground
     *
     * @return Board
     */
    public function setColorBackground(string $colorBackground): Board
    {
        $this->colorBackground = $colorBackground;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getBackgroundHeader(): ?string
    {
        return $this->backgroundHeader;
    }

    /**
     * @param null|string $backgroundHeader
     *
     * @return Board
     */
    public function setBackgroundHeader(?string $backgroundHeader): Board
    {
        $this->backgroundHeader = $backgroundHeader;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getBackground(): ?string
    {
        return $this->background;
    }

    /**
     * @param null|string $background
     *
     * @return Board
     */
    public function setBackground(?string $background): Board
    {
        $this->background = $background;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getBeginAt(): ?\DateTime
    {
        return $this->beginAt;
    }

    /**
     * @param \DateTime|null $beginAt
     *
     * @return Board
     */
    public function setBeginAt(?\DateTime $beginAt): Board
    {
        $this->beginAt = $beginAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndAt(): ?\DateTime
    {
        return $this->endAt;
    }

    /**
     * @param \DateTime|null $endAt
     *
     * @return Board
     */
    public function setEndAt(?\DateTime $endAt): Board
    {
        $this->endAt = $endAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxDaysPerLine(): int
    {
        return $this->maxDaysPerLine;
    }

    /**
     * @param int $maxDaysPerLine
     *
     * @return Board
     */
    public function setMaxDaysPerLine(?int $maxDaysPerLine): Board
    {
        $this->maxDaysPerLine = $maxDaysPerLine;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgOpen(): string
    {
        return $this->msgOpen;
    }

    /**
     * @param string $msgOpen
     *
     * @return Board
     */
    public function setMsgOpen(?string $msgOpen): Board
    {
        $this->msgOpen = $msgOpen;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgWon(): string
    {
        return $this->msgWon;
    }

    /**
     * @param string $msgWon
     *
     * @return Board
     */
    public function setMsgWon(?string $msgWon): Board
    {
        $this->msgWon = $msgWon;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgLost(): string
    {
        return $this->msgLost;
    }

    /**
     * @param string $msgLost
     *
     * @return Board
     */
    public function setMsgLost(?string $msgLost): Board
    {
        $this->msgLost = $msgLost;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgWonInstruction(): string
    {
        return $this->msgWonInstruction;
    }

    /**
     * @param string $msgWonInstruction
     *
     * @return Board
     */
    public function setMsgWonInstruction(?string $msgWonInstruction): Board
    {
        $this->msgWonInstruction = $msgWonInstruction;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgLostInstruction(): string
    {
        return $this->msgLostInstruction;
    }

    /**
     * @param string $msgLostInstruction
     *
     * @return Board
     */
    public function setMsgLostInstruction(?string $msgLostInstruction): Board
    {
        $this->msgLostInstruction = $msgLostInstruction;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgLostTextOne(): string
    {
        return $this->msgLostTextOne;
    }

    /**
     * @param string $msgLostTextOne
     *
     * @return Board
     */
    public function setMsgLostTextOne(?string $msgLostTextOne): Board
    {
        $this->msgLostTextOne = $msgLostTextOne;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgLostTextTwo(): string
    {
        return $this->msgLostTextTwo;
    }

    /**
     * @param string $msgLostTextTwo
     *
     * @return Board
     */
    public function setMsgLostTextTwo(?string $msgLostTextTwo): Board
    {
        $this->msgLostTextTwo = $msgLostTextTwo;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgLostTextTwoEnd(): string
    {
        return $this->msgLostTextTwoEnd;
    }

    /**
     * @param string $msgLostTextTwoEnd
     *
     * @return Board
     */
    public function setMsgLostTextTwoEnd(?string $msgLostTextTwoEnd): Board
    {
        $this->msgLostTextTwoEnd = $msgLostTextTwoEnd;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgDownloadPdf(): string
    {
        return $this->msgDownloadPdf;
    }

    /**
     * @param string $msgDownloadPdf
     *
     * @return Board
     */
    public function setMsgDownloadPdf(?string $msgDownloadPdf): Board
    {
        $this->msgDownloadPdf = $msgDownloadPdf;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgSendingEmailPrizeSummary(): string
    {
        return $this->msgSendingEmailPrizeSummary;
    }

    /**
     * @param string $msgSendingEmailPrizeSummary
     *
     * @return Board
     */
    public function setMsgSendingEmailPrizeSummary(?string $msgSendingEmailPrizeSummary): Board
    {
        $this->msgSendingEmailPrizeSummary = $msgSendingEmailPrizeSummary;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgEmailAddressSummary(): string
    {
        return $this->msgEmailAddressSummary;
    }

    /**
     * @param string $msgEmailAddressSummary
     *
     * @return Board
     */
    public function setMsgEmailAddressSummary(?string $msgEmailAddressSummary): Board
    {
        $this->msgEmailAddressSummary = $msgEmailAddressSummary;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgSendingEmailButton(): string
    {
        return $this->msgSendingEmailButton;
    }

    /**
     * @param string $msgSendingEmailButton
     *
     * @return Board
     */
    public function setMsgSendingEmailButton(?string $msgSendingEmailButton): Board
    {
        $this->msgSendingEmailButton = $msgSendingEmailButton;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgSendingEmailInProgress(): string
    {
        return $this->msgSendingEmailInProgress;
    }

    /**
     * @param string $msgSendingEmailInProgress
     *
     * @return Board
     */
    public function setMsgSendingEmailInProgress(?string $msgSendingEmailInProgress): Board
    {
        $this->msgSendingEmailInProgress = $msgSendingEmailInProgress;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgEmailSent(): string
    {
        return $this->msgEmailSent;
    }

    /**
     * @param string $msgEmailSent
     *
     * @return Board
     */
    public function setMsgEmailSent(?string $msgEmailSent): Board
    {
        $this->msgEmailSent = $msgEmailSent;

        return $this;
    }

    /**
     * @return string
     */
    public function getMsgEmailError(): string
    {
        return $this->msgEmailError;
    }

    /**
     * @param string $msgEmailError
     *
     * @return Board
     */
    public function setMsgEmailError(?string $msgEmailError): Board
    {
        $this->msgEmailError = $msgEmailError;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPersonalizedCSS(): ?string
    {
        return $this->personalizedCSS;
    }

    /**
     * @param null|string $personalizedCSS
     *
     * @return Board
     */
    public function setPersonalizedCSS(?string $personalizedCSS): Board
    {
        $this->personalizedCSS = $personalizedCSS;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUsePersonalizedCSS(): bool
    {
        return $this->usePersonalizedCSS;
    }

    /**
     * @param bool $usePersonalizedCSS
     *
     * @return Board
     */
    public function setUsePersonalizedCSS(bool $usePersonalizedCSS): Board
    {
        $this->usePersonalizedCSS = $usePersonalizedCSS;

        return $this;
    }
}
