<?php

/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Controller;

use AppBundle\AdventCalendar\Entity\Attempt;
use AppBundle\AdventCalendar\Entity\Campaign;
use AppBundle\AdventCalendar\Repository\AttemptRepository;
use AppBundle\AdventCalendar\Services\WinnersExport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CampaignWinnersController.
 *
 * @Route("/advent-calendar/campaign/{campaign}/winners", requirements={"campaign" = "\d+"})
 */
class CampaignWinnersController extends Controller
{
    /**
     * Winners list.
     *
     * @param Campaign $campaign
     * @param int      $page
     *
     * @return Response
     *
     * @Route(
     *     "/{page}",
     *     name="advent_calendar_campaign_winners_list",
     *     requirements={"page" = "\d+"},
     *     defaults={"page" = 1},
     *     methods={"GET"}
     * )
     */
    public function listAction(Campaign $campaign, int $page): Response
    {
        $om = $this->getDoctrine()->getManager();

        /** @var AttemptRepository $attemptRepository */
        $attemptRepository = $om->getRepository(Attempt::class);

        $pagination = $this->get('knp_paginator')->paginate(
            $attemptRepository->getWinnersByCampaign($campaign->getId()),
            $page,
            50
        );

        return $this->render(
            'advent-calendar/back-office/campaign-winners/list.html.twig',
            [
                'pagination' => $pagination,
                'campaign' => $campaign,
            ]
        );
    }

    /**
     * @param Campaign $campaign
     *
     * @throws \Exception
     *
     * @return StreamedResponse
     *
     * @Route(
     *     "/export",
     *     name="advent_calendar_campaign_winners_export",
     *     methods={"GET"}
     * )
     */
    public function exportAction(Campaign $campaign): StreamedResponse
    {
        /** @var WinnersExport $winnersExportService */
        $winnersExportService = $this->get(WinnersExport::class);

        $response = $winnersExportService->exportResponse($campaign);

        return $response;
    }
}
