<?php

/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Controller;

use AppBundle\AdventCalendar\Entity\AttemptHistory;
use AppBundle\AdventCalendar\Entity\Campaign;
use AppBundle\AdventCalendar\Form\PlayerHistoryFilterType;
use AppBundle\AdventCalendar\Repository\AttemptHistoryRepository;
use AppBundle\AdventCalendar\Services\PlayerHistoryFiltersList;
use AppBundle\BackOffice\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CampaignPlayerHistoryController.
 *
 * @Route("/advent-calendar/campaign/{campaign}/player-history", requirements={"campaign" = "\d+"})
 */
class CampaignPlayerHistoryController extends Controller
{
    /**
     * Player history list.
     *
     * @param Request  $request
     * @param Campaign $campaign
     * @param int      $page
     *
     * @throws \LogicException
     *
     * @return Response
     *
     * @Route(
     *     "/{page}",
     *     name="advent_calendar_campaign_player_history_list",
     *     requirements={"page" = "\d+"},
     *     defaults={"page" = 1},
     *     methods={"GET"}
     * )
     */
    public function listAction(Request $request, Campaign $campaign, int $page): Response
    {
        $em = $this->getDoctrine()->getManager();

        $parameters = $request->query->all();
        $sort = $request->query->get('sort');
        $direction = $request->query->get('direction');

        if (!empty($parameters['player_history_filter'])) {
            $filters = $parameters['player_history_filter'];
        }

        $filters['campaignId'] = $campaign->getId();

        /** @var AttemptHistoryRepository $attemptHistoryRepository */
        $attemptHistoryRepository = $em->getRepository(AttemptHistory::class);

        $attemptLogEntries = $attemptHistoryRepository
            ->getPlayerAttemptsHistoryFromCampaign(
                $this->get(PlayerHistoryFiltersList::class),
                $filters,
                null,
                $sort,
                $direction
            );

        $formPlayerHistory = $this->createForm(
            PlayerHistoryFilterType::class,
            null,
            [
                'filters' => $filters,
            ]
        );

        $pagination = $this->get('knp_paginator')->paginate(
            $attemptLogEntries,
            $page,
            50
        );

        return $this->render(
            'advent-calendar/back-office/campaign-player-history/list.html.twig',
            [
                'pagination' => $pagination,
                'campaign' => $campaign,
                'formFilters' => $formPlayerHistory->createView(),
            ]
        );
    }

    /**
     * @param Request  $request
     * @param Campaign $campaign
     *
     * @throws \LogicException
     *
     * @return JsonResponse
     *
     * @Route(
     *     "/search-filter",
     *     name="advent_calendar_campaign_player_history_search_filter",
     *     options={"expose" = true},
     *     methods={"POST"}
     * )
     */
    public function autocompleteSearchFilterAction(Request $request, Campaign $campaign): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();

        $filters = $request->query->get('player_history_filter');
        $parameters = $request->request->all();

        if (!empty($parameters['search'])) {
            $filters['user']['name'] = $parameters['search'];
            unset($filters['user']['id']);
        }

        $filters['campaignId'] = $campaign->getId();

        /** @var PlayerHistoryFiltersList $playerHistoryFiltersList */
        $playerHistoryFiltersList = $this->get(PlayerHistoryFiltersList::class);

        $queryResult = $em->getRepository(Player::class)
            ->getQueryForAutocompleteFilters($playerHistoryFiltersList, $filters)
            ->getArrayResult();

        $results = ['suggestions' => []];

        if (!empty($queryResult)) {
            foreach ($queryResult as $elem) {
                $results['suggestions'][] = [
                    'value' => $elem['value'],
                    'data' => $elem['id'],
                ];
            }
        }

        return new JsonResponse($results);
    }
}
