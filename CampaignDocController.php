<?php

/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Controller;

use AppBundle\AdventCalendar\Entity\Campaign;
use AppBundle\AdventCalendar\Services\CampaignExport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CampaignDocController.
 *
 * @Route("/advent-calendar/campaign")
 */
class CampaignDocController extends Controller
{
    const PREVIEW_RESULT_WON = 'won';
    const PREVIEW_RESULT_LOST = 'lost';

    /**
     * Documentation export.
     *
     * @param int $campaignId
     *
     * @throws \Exception
     *
     * @Route(
     *     "/{campaignId}/doc",
     *     name="advent_calendar_campaign_doc_export",
     *     methods={"GET"},
     *     requirements={"campaignId"="\d+"}
     * )
     *
     * @return Response
     */
    public function exportAction(int $campaignId): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $generatedDir = $projectDir.'/var/generated';

        if (!is_dir($generatedDir)) {
            mkdir($generatedDir, 0755, true);
        }

        $fs = new Filesystem();

        $campaign = $this->getDoctrine()->getRepository(Campaign::class)->find($campaignId);

        /** @var CampaignExport $campaignExport */
        $campaignExport = $this->get(CampaignExport::class);

        // Css base : base.css
        $cssBase = $campaignExport->getCompileCss();
        $cssPath = $generatedDir.'/base.css';
        file_put_contents($cssPath, $cssBase);

        // playboard : calendrier.html
        $dataPlaybord = $campaignExport->getDataForHTMLPlayboard($campaign);
        $htmlPlayboard = $this->render('advent-calendar/front-office/play_board.html.twig', $dataPlaybord);
        $playboardPath = $generatedDir.'/calendrier.html';
        $fs->appendToFile($playboardPath, $htmlPlayboard->getContent());

        // won result : resultat-gain.html
        $dataResultWon = $campaignExport->getDataForHTMLResult($campaign, self::PREVIEW_RESULT_WON);
        $htmlResultWon = $this->render('advent-calendar/front-office/result.html.twig', $dataResultWon);
        $wonResultPath = $generatedDir.'/resultat-gain.html';
        $fs->appendToFile($wonResultPath, $htmlResultWon->getContent());

        // lost result : resultat-perte.html
        $dataResultLost = $campaignExport->getDataForHTMLResult($campaign, self::PREVIEW_RESULT_LOST);
        $htmlResultLost = $this->render('advent-calendar/front-office/result.html.twig', $dataResultLost);
        $lostResultPath = $generatedDir.'/resultat-perte.html';
        $fs->appendToFile($lostResultPath, $htmlResultLost->getContent());

        // Generation du zip
        $zip = new \ZipArchive();
        $zipName = $this->get('translator')->trans('name.documentation_CSS', [], 'advent-calendar-back-office');
        $zip->open($generatedDir.'/'.$zipName, \ZipArchive::CREATE);
        $zip->addGlob($generatedDir.'/*', GLOB_BRACE, ['remove_all_path' => true]);
        $zip->close();

        $response = new BinaryFileResponse($generatedDir.'/'.$zipName);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $zipName);
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
