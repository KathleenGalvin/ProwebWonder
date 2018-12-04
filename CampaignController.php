<?php

/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Controller;

use AppBundle\AdventCalendar\Entity\Attempt;
use AppBundle\AdventCalendar\Entity\AttemptHistory;
use AppBundle\AdventCalendar\Entity\Campaign;
use AppBundle\AdventCalendar\Entity\CampaignHistory;
use AppBundle\AdventCalendar\Entity\Prize;
use AppBundle\AdventCalendar\Form\CampaignType;
use AppBundle\AdventCalendar\Form\ImportPrizeType;
use AppBundle\AdventCalendar\Form\PrizeType;
use AppBundle\AdventCalendar\Repository\AttemptHistoryRepository;
use AppBundle\AdventCalendar\Repository\AttemptRepository;
use AppBundle\AdventCalendar\Repository\CampaignRepository;
use AppBundle\AdventCalendar\Services\CampaignEdit;
use AppBundle\AdventCalendar\Services\CampaignExport;
use AppBundle\AdventCalendar\Services\CampaignUpload;
use AppBundle\AdventCalendar\Services\CreatePrize;
use AppBundle\AdventCalendar\Services\PlayerHistoryFiltersList;
use AppBundle\BackOffice\Entity\PlayerAccess;
use AppBundle\BackOffice\Repository\PlayerAccessRepository;
use Doctrine\Common\Cache\Cache;
use Pwb\Utils\ImageUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * Class CampaignController.
 *
 * @Route("/advent-calendar/campaign")
 */
class CampaignController extends Controller
{
    const PREVIEW_RESULT_WON = 'won';
    const PREVIEW_RESULT_LOST = 'lost';

    /**
     * Edit campaign.
     *
     * @param Request  $request
     * @param Campaign $campaign
     *
     * @throws \Exception
     *
     * @return Response
     *
     * @Route(
     *     "/{campaign}/edit",
     *     name="advent_calendar_campaign_edit",
     *     requirements={"campaign" = "\d+"},
     *     methods={"GET", "POST"}
     * )
     *
     * @ParamConverter("campaign", class="AppBundle\AdventCalendar\Entity\Campaign")
     */
    public function editAction(Request $request, Campaign $campaign): Response
    {
        $em = $this->getDoctrine()->getManager();
        $listLimit = 9;

        $prizesList = $em->getRepository(Prize::class)->getUniquePrizesByCampaign($campaign->getId());

        $form = $this->createForm(
            CampaignType::class,
            $campaign
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('error', $this->get('translator')->trans('error.correction_message', [], 'advent-calendar-back-office'));
            } else {
                $fullInputs = $request->request->get('edit_campaign_advent_calendar');
                $prizeData = json_decode($fullInputs['fullInput']);

                /** @var UploadedFile|string|bool $backgroundHeaderFile */
                $backgroundHeaderFile = $form->get('backgroundHeader')->getData();

                /** @var UploadedFile|string|bool $backgroundFile */
                $backgroundFile = $form->get('background')->getData();

                if ($backgroundHeaderFile instanceof File) {
                    $this->get(CampaignUpload::class)->uploadCampaignBackgroundImages($backgroundHeaderFile, $campaign, 'backgroundHeader');
                }

                if ($backgroundFile instanceof File) {
                    $this->get(CampaignUpload::class)->uploadCampaignBackgroundImages($backgroundFile, $campaign, 'background');
                }

                $campaign = $this->get(CampaignEdit::class)->replaceNullValues($campaign);

                $em->persist($campaign);
                $em->flush();

                if (!empty($prizeData)) {
                    $this->get(CreatePrize::class)->updatePrize((array) $prizeData, $campaign);
                }

                /** @var Cache $resultCacheImpl */
                $resultCacheImpl = $this->getDoctrine()->getManager()->getConfiguration()->getResultCacheImpl();
                $resultCacheImpl->delete(CampaignRepository::RESULT_CACHE_DATAS_FOR_TEMPLATING_PREFIX.$campaign->getId());

                $this->addFlash('success', $this->get('translator')->trans('success.campaign_updated', [], 'advent-calendar-back-office'));

                return $this->redirectToRoute('campaign_list');
            }
        }

        // Campaign History.
        $campaignLogEntries = $em->getRepository(CampaignHistory::class)
            ->getLogEntryCreateAction(Campaign::class, $campaign->getId());

        // Player ranking.
        /** @var AttemptRepository $attemptRepository */
        $attemptRepository = $em->getRepository(Attempt::class);

        $winners = $em->getRepository(Attempt::class)
            ->getWinnersByCampaign($campaign->getId(), $listLimit)
            ->getArrayResult();

        /** @var AttemptHistoryRepository $attemptHistoryRepository */
        $attemptHistoryRepository = $em->getRepository(AttemptHistory::class);

        // Statistics.
        /** @var PlayerAccessRepository $playerAccessRepository */
        $playerAccessRepository = $em->getRepository(PlayerAccess::class);

        $statTodayVisit = $playerAccessRepository->countByCampaignAndDate(
            $campaign->getId(),
            get_class($campaign)
        );

        $statUniquePlayer = $attemptHistoryRepository->countUniquePlayerByCampaign(
            $campaign->getId()
        );

        $statTodayAttempts = $attemptHistoryRepository->countByCampaignAndDate(
            $campaign->getId()
        );

        $yesterday = new \DateTime('-1 day');

        $statYesterdayVisit = $playerAccessRepository->countByCampaignAndDate(
            $campaign->getId(),
            get_class($campaign),
            $yesterday
        );

        $statYesterdayAttempts = $attemptHistoryRepository->countByCampaignAndDate(
            $campaign->getId(),
            $yesterday
        );

        $statTotalAttempts = $attemptRepository->countByCampaign(
            $campaign->getId()
        );

        $statWinners = $attemptRepository->countWinnersByCampaign($campaign->getId());

        // Player attempts history.
        $attemptLogEntries = $attemptHistoryRepository
            ->getPlayerAttemptsHistoryFromCampaign(
                $this->get(PlayerHistoryFiltersList::class),
                ['campaignId' => $campaign->getId()],
                $listLimit
            )
            ->getResult();

        $formPrizeEdit = $this->createForm(
            PrizeType::class,
            null,
            [
                'action' => $this->generateUrl('advent_calendar_prize_edit'),
                'validation_groups' => ['edit'],
            ]
        );

        $formPrizeImport = $this->createForm(
            ImportPrizeType::class,
            null,
            [
                'action' => $this->generateUrl('advent_calendar_prize_import'),
            ]
        );

        // Get the play url.
        $playUrlScheme = $this->getParameter('router.request_context.scheme');
        $playUrlNet = $this->generateUrl(
            'advent_calendar_play_board',
            ['board' => $campaign->getBoard()->getId()],
            UrlGenerator::NETWORK_PATH
        );
        $playUrlNet = preg_replace('#^//admin\.#', '//', $playUrlNet);
        $playUrl = $playUrlScheme.':'.$playUrlNet;

        return $this->render(
            'advent-calendar/back-office/campaign/edit.html.twig',
            [
                'campaign' => $campaign,
                'campaignHistory' => $campaignLogEntries,
                'form' => $form->createView(),
                'winnersList' => $winners,
                'listLimit' => $listLimit,
                'statTodayVisit' => $statTodayVisit,
                'statUniquePlayer' => $statUniquePlayer,
                'statTodayAttempts' => $statTodayAttempts,
                'statYesterdayVisit' => $statYesterdayVisit,
                'statYesterdayAttempts' => $statYesterdayAttempts,
                'statTotalAttempts' => $statTotalAttempts,
                'attemptsHistory' => $attemptLogEntries,
                'statWinners' => $statWinners,
                'prizesList' => $prizesList,
                'formPrizeEdit' => $formPrizeEdit->createView(),
                'formPrizeImport' => $formPrizeImport->createView(),
                'bucketUrl' => $this->getParameter('aws_s3_bucket_url'),
                'playUrl' => $playUrl,
            ]
        );
    }

    /**
     * Edit prize.
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return JsonResponse
     *
     * @Route(
     *     "/prize-edit",
     *     name="advent_calendar_prize_edit",
     *     options={"expose" = true},
     *     methods={"POST"}
     * )
     */
    public function prizeEditAction(Request $request)
    {
        $isDeleted = $request->get('isDeleted');
        $validationGroups = 'edit';

        if ($isDeleted) {
            $validationGroups = 'delete';
        }

        $form = $this->createForm(
            PrizeType::class,
            null,
            ['validation_groups' => [$validationGroups]]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $result = ['error' => true];

                /** @var FormError $error */
                foreach ($form->getErrors(true) as $error) {
                    $result['messages'][] = $error->getMessage();
                }
            } else {
                /** @var UploadedFile|string|bool $picture */
                $picture = $form['picture']->getData();

                $isDuplicate = $request->get('isDuplicate');
                $imagePath = $picture;

                if ($picture instanceof File) {
                    $imagePath = $this->get(CampaignUpload::class)
                        ->uploadPreSave($picture);
                } elseif (true === (bool) $isDuplicate) {
                    if (!preg_match('#'.CampaignUpload::UPLOAD_TMP_DIR.'#', $picture)) {
                        $picture = $this->getParameter('aws_s3_bucket_url').'/'.$picture;
                    }

                    $imagePath = $this->get(CampaignUpload::class)
                        ->urlImagePreSave($picture);
                }

                $result = $request->get('prize');
                $result['picture'] = $imagePath;

                if (preg_match('#'.CampaignUpload::UPLOAD_TMP_DIR.'#', $imagePath)) {
                    $result['pictureBase64'] = (new ImageUtil($imagePath))->toDataUri();
                }

                if (!isset($result['isBigPrize'])) {
                    $result['isBigPrize'] = '0';
                }
            }
        } else {
            $result = ['error' => true];
        }

        return new JsonResponse($result);
    }

    /**
     * Import prizes.
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return JsonResponse
     *
     * @Route(
     *     "/prize-import",
     *     name="advent_calendar_prize_import",
     *     options={"expose" = true},
     *     methods={"POST"}
     * )
     */
    public function importPrizesAction(Request $request)
    {
        $form = $this->createForm(
            ImportPrizeType::class
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $result = ['error' => true];
            } else {
                /** @var UploadedFile $file */
                $file = $form['import']->getData();
                $ignoreFirstLine = $form['ignoreFirstLine']->getData();
                $result = ['error' => 'Not a file'];

                if ($file instanceof File) {
                    $result = $this->get(CreatePrize::class)->importPrizes(
                        $file,
                        $ignoreFirstLine
                    );
                }
            }
        } else {
            $result = ['error' => 'not submitted'];
        }

        return new JsonResponse($result);
    }

    /**
     * Game Preview.
     *
     * @param Campaign $campaign
     *
     * @throws \LogicException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Response
     *
     *
     * @Route(
     *     "/{campaign}/preview",
     *     name="advent_calendar_preview",
     *     requirements={"campaign" = "\d+"},
     *     methods={"GET"}
     * )
     */
    public function previewAction(Campaign $campaign): Response
    {
        $dataPlaybord = $this->get(CampaignExport::class)->getDataForHTMLPlayboard($campaign);

        return $this->render('advent-calendar/front-office/play_board.html.twig', $dataPlaybord);
    }

    /**
     * @param Campaign $campaign
     * @param string   $result
     *
     * @Route(
     *     "/{campaign}/preview-result/{result}",
     *     name="advent_calendar_preview_result",
     *     requirements={"campaign" = "\d+"},
     *     methods={"GET"}
     * )
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \LogicException
     *
     * @return Response
     */
    public function previewResultAction(Campaign $campaign, string $result): Response
    {
        $dataResult = $this->get(CampaignExport::class)->getDataForHTMLResult($campaign, $result);

        return $this->render('advent-calendar/front-office/result.html.twig', $dataResult);
    }
}
