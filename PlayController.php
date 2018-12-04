<?php

/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Controller;

use AppBundle\AdventCalendar\Entity\Attempt;
use AppBundle\AdventCalendar\Entity\Board;
use AppBundle\AdventCalendar\Entity\Campaign;
use AppBundle\AdventCalendar\Repository\CampaignRepository;
use AppBundle\AdventCalendar\Services\AttemptExport;
use AppBundle\AdventCalendar\Services\Mailer;
use AppBundle\AdventCalendar\Services\PlayBoard;
use AppBundle\BackOffice\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PlayController.
 */
class PlayController extends Controller
{
    /**
     * Load the game.
     *
     * @param int $board
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \LogicException
     * @throws NotFoundHttpException
     *
     * @Route(
     *     "/play/advent-calendar/{board}",
     *     name="advent_calendar_play_board",
     *     requirements={"board"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @return Response|null
     */
    public function boardAction(int $board): ?Response
    {
        /** @var Player $player */
        $player = $this->getUser();
        /** @var Campaign $campaign */
        $campaign = $player->getCurrentCampaign();

        /** @var CampaignRepository $boardRepository */
        $campaignRepository = $this->getDoctrine()->getRepository(Campaign::class);
        $dataCampaign = $campaignRepository->getDatasForTemplating($campaign->getId());
        $dataHeader = $campaignRepository->getDatasForHeader($campaign->getId());

        if (empty($dataCampaign)) {
            throw new NotFoundHttpException();
        }

        /** @var CampaignRepository $boardRepository */
        $attemptRepository = $this->getDoctrine()->getRepository(Attempt::class);

        $daysList = [];
        $attemptsList = [];

        for ($day = $campaign->getBeginAt(); $day <= $campaign->getEndAt(); $day = $day->modify('+1 day')) {
            $key = $day->format('Y-m-d');
            $daysList[$key] = clone $day;
            $attemptsList[$key] = null;
        }

        $allAttempts = $attemptRepository->getAttemptsByCampaignAndPlayer($player->getId(), $campaign->getId());

        foreach ($allAttempts as $attempt) {
            $attemptsList[$attempt['date']->format('Y-m-d')] = $attempt;
        }

        return $this->render('advent-calendar/front-office/play_board.html.twig', [
            'datasCampaign' => $dataCampaign,
            'dataHeader' => $dataHeader,
            'daysList' => $daysList,
            'attemptsList' => $attemptsList,
            'boardId' => $board,
        ]);
    }

    /**
     * Attempt on a day.
     *
     * @param Board $board
     *
     * @Route(
     *     "/play/advent-calendar/{board}/attempt",
     *     name="advent_calendar_attempt",
     *     requirements={"board"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @throws \LogicException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response|null
     */
    public function dayAttemptAction(Board $board): ?Response
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Player $player */
        $player = $this->getUser();
        /** @var Campaign $campaign */
        $campaign = $player->getCurrentCampaign();

        if ($campaign->getBoard()->getId() !== $board->getId()) {
            throw new NotFoundHttpException();
        }

        $dayDate = new \DateTime('now');

        $currentAttempt = $this->getDoctrine()->getRepository(Attempt::class)
            ->findOneBy([
                'player' => $player,
                'campaign' => $campaign,
                'date' => $dayDate,
            ]);

        // A player can only play once a day.
        if (null !== $currentAttempt) {
            return $this->redirectToRoute(
                'advent_calendar_show_past_attempt',
                [
                    'board' => $board->getId(),
                    'attempt' => $currentAttempt->getId(),
                ]
            );
        }

        /** @var CampaignRepository $campaignRepository */
        $campaignRepository = $this->getDoctrine()->getRepository(Campaign::class);
        $playBoard = $this->get(PlayBoard::class);

        // Player number (connection order).
        $playerNumber = $player->getAdventPlayerAccessCurrentCampaign()->getPlayerOrder();

        // If this is the first player of the day to open the daily case, the list of potentials winners is generated.
        if (1 === $playerNumber) {
            $playBoard->setDayPotentialWinnersList($campaign);
        }

        // Check if the connection order of the player matches a winning numnber.
        $result = $playBoard->isPotentialWinner($campaign, $player, $playerNumber);

        $attempt = (new Attempt())
            ->setCampaign($campaign)
            ->setPlayer($player)
            ->setDate($dayDate);

        $em->persist($attempt);
        $em->flush();

        // If it is a winning number, a prize is affected randomly.
        if ($result) {
            $playBoard->prizeAffectation($campaign, $attempt);
        }

        $dataCampaign = $campaignRepository->getDatasForTemplating($campaign->getId());
        $dataHeader = $campaignRepository->getDatasForHeader($campaign->getId());

        return $this->render('advent-calendar/front-office/result.html.twig', [
            'datasCampaign' => $dataCampaign,
            'dataHeader' => $dataHeader,
            'attemptResult' => $result,
            'attempt' => $attempt,
            'backButtonUrl' => $this->get('router')->generate('advent_calendar_play_board', ['board' => $board->getId()]),
        ]);
    }

    /**
     * Show a past attempt.
     *
     * @param Board   $board
     * @param Attempt $attempt
     *
     * @Route("/play/advent-calendar/{board}/show-past-attempt/{attempt}",
     *     name="advent_calendar_show_past_attempt",
     *     requirements={"board"="\d+", "attempt"="\d+"},
     *     methods={"GET"}
     * )
     *
     * @throws \LogicException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function showPastAttemptAction(Board $board, Attempt $attempt): Response
    {
        /** @var Player $player */
        $player = $this->getUser();
        /** @var Campaign $campaign */
        $campaign = $player->getCurrentCampaign();

        if ($campaign->getBoard()->getId() !== $board->getId()) {
            throw new NotFoundHttpException();
        }

        /** @var CampaignRepository $campaignRepository */
        $campaignRepository = $this->getDoctrine()->getRepository(Campaign::class);

        $dataCampaign = $campaignRepository->getDatasForTemplating($campaign->getId());
        $dataHeader = $campaignRepository->getDatasForHeader($campaign->getId());

        return $this->render('advent-calendar/front-office/result.html.twig', [
            'datasCampaign' => $dataCampaign,
            'dataHeader' => $dataHeader,
            'attemptResult' => (null !== $attempt->getPrize()),
            'attempt' => $attempt,
            'backButtonUrl' => $this->get('router')->generate('advent_calendar_play_board', ['board' => $board->getId()]),
        ]);
    }

    /**
     * @param Attempt $attempt
     *
     * @Route(
     *     "/play/advent-calendar/{board}/attempt/{attempt}/download",
     *     name="advent_calendar_attempt_download",
     *     methods={"GET"}
     * )
     *
     * @throws \PhantomJS_Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return StreamedResponse
     */
    public function downloadSummaryAction(Attempt $attempt): StreamedResponse
    {
        $response = $this->get(AttemptExport::class)->exportResponse($attempt);

        return $response;
    }

    /**
     * @param Attempt $attempt
     *
     * @Route(
     *     "/play/advent-calendar/{board}/attempt/{attempt}/send",
     *     name="advent_calendar_attempt_send",
     *     methods={"POST"},
     *     options={"expose" = true}
     * )
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function sendEmailSummaryAction(Attempt $attempt): JsonResponse
    {
        $result = $this->get(Mailer::class)->sendAttemptSummary($attempt);
        $campaign = $attempt->getCampaign();
        $mailResultMessage = $campaign->getMsgEmailError();

        if ($result) {
            $mailResultMessage = $campaign->getMsgEmailSent();
        }

        return new JsonResponse(['message' => $mailResultMessage]);
    }
}
