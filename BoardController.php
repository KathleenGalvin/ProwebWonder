<?php

/**
 * Property of ProwebCE.
 */

namespace AppBundle\AdventCalendar\Controller;

use AppBundle\AdventCalendar\Entity\Board;
use AppBundle\AdventCalendar\Form\Board\AddBoardType;
use AppBundle\AdventCalendar\Form\Board\EditBoardType;
use AppBundle\AdventCalendar\Repository\BoardRepository;
use AppBundle\AdventCalendar\Services\BoardUpload;
use AppBundle\BackOffice\Entity\Game;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BoardController.
 *
 * @Route("/advent-calendar/board")
 */
class BoardController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/", name="advent_calendar_board_list")
     * @Route("/with-archives", name="advent_calendar_board_list_with_archives")
     *
     * @throws \LogicException
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        /** @var BoardRepository $boardRepository */
        $boardRepository = $this->getDoctrine()->getRepository(Board::class);

        // Si l'url contient "with-archives", on charge toutes les planches.
        // Sinon, juste les "en construction" et les "diffusées".
        $isWithArchives = ('advent_calendar_board_list_with_archives' === $request->get('_route'));

        if ($isWithArchives && !$this->isGranted('ROLE_EDIT')) {
            throw new AccessDeniedHttpException();
        }

        if ($isWithArchives) {
            $allBoards = $boardRepository->findAll();
        } else {
            $allBoards = $boardRepository->findBy(['state' => [Board::STATE_DRAFT, Board::STATE_PUBLISHED]]);
        }

        $boards = [
            'draft' => [],
            'published' => [],
            'archived' => [],
        ];

        /** @var Board $board */
        foreach ($allBoards as $board) {
            $state = $board->getState();
            switch ($state) {
                case Board::STATE_DRAFT:
                    $boards['draft'][] = $board;
                    break;
                case Board::STATE_PUBLISHED:
                    $boards['published'][] = $board;
                    break;
                case Board::STATE_ARCHIVED:
                    $boards['archived'][] = $board;
                    break;
            }
        }

        return $this->render('advent-calendar/back-office/board/list.html.twig', [
            'boards' => $boards,
            'isWithArchives' => $isWithArchives,
        ]);
    }

    /**
     * @param Request $request
     * @param Board   $board
     *
     * @throws AccessDeniedHttpException
     * @throws \LogicException
     * @throws \OutOfBoundsException
     *
     * @return Response
     *
     * @Route("/{board}/edit", name="advent_calendar_board_edit", methods={"GET", "POST"})
     * @Route("/{board}/read", name="advent_calendar_board_read", methods={"GET", "POST"})
     */
    public function editBoardAction(Request $request, Board $board): Response
    {
        // On ne doit pas avoir accès à la route "advent_calendar_board_edit" si on n'a pas un rôle d'édition.
        if ('advent_calendar_board_edit' === $request->get('_route') && !$this->isGranted('ROLE_EDIT')) {
            throw new AccessDeniedHttpException();
        }

        $isEditable = 'advent_calendar_board_edit' === $request->get('_route') &&
            Board::STATE_ARCHIVED !== $board->getState() &&
            $this->isGranted('ROLE_EDIT');

        $form = $this->editBoardForm($board, 'advent_calendar_board_edit', $isEditable);

        $form->handleRequest($request);

        if ($isEditable && $form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var BoardUpload $boardUpload */
                $boardUpload = $this->get(BoardUpload::class);

                /** @var UploadedFile|string|bool $backgroundHeaderFile */
                $backgroundHeaderFile = $form->get('backgroundHeader')->getData();

                /** @var UploadedFile|string|bool $backgroundFile */
                $backgroundFile = $form->get('background')->getData();

                if ($backgroundHeaderFile instanceof File) {
                    $boardUpload->uploadBoardBackgroundImages($backgroundHeaderFile, $board, 'backgroundHeader');
                }

                if ($backgroundFile instanceof File) {
                    $boardUpload->uploadBoardBackgroundImages($backgroundFile, $board, 'background');
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($board);
                $em->flush();

                return $this->redirectToRoute('advent_calendar_board_list');
            }
        }

        return $this->render('advent-calendar/back-office/board/board_edit.html.twig', [
            'board' => $board,
            'form' => $form->createView(),
            'isEditable' => $isEditable,
        ]);
    }

    /**
     * @param Request $request
     * @param Board   $board
     *
     * @throws \LogicException
     * @throws AccessDeniedHttpException
     *
     * @return Response
     *
     * @Route(
     *     "/{board}/archive",
     *     name="advent_calendar_board_archive",
     *     requirements={"board" = "\d+"},
     *     methods={"GET", "POST"}
     * )
     *
     * @Security("has_role('ROLE_EDIT')")
     */
    public function archiveBoardAction(Request $request, Board $board): Response
    {
        if (!$this->isGranted('ROLE_EDIT')) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createFormBuilder($board)
            ->setAction($this->generateUrl('advent_calendar_board_archive', ['board' => $board->getId()]))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $board->setState(Board::STATE_ARCHIVED);
            $em = $this->getDoctrine()->getManager();
            $em->persist($board);
            $em->flush();

            if ($request->isXmlHttpRequest()) {
                $data = ['message' => 'OK'];
                $result = new JsonResponse($data);
            } else {
                $result = $this->redirectToRoute('advent_calendar_board_list');
            }

            return $result;
        }

        return $this->render('advent-calendar/back-office/board/board-archive.html.twig', [
            'boardName' => $board->getName(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Suppression d'une planche.
     *
     * @param Request $request
     * @param Board   $board
     *
     * @throws AccessDeniedHttpException
     * @throws \LogicException
     *
     * @return Response
     *
     * @Route("/{board}/remove", name="advent_calendar_board_remove")
     *
     * @Security("has_role('ROLE_EDIT')")
     */
    public function removeBoardAction(Request $request, Board $board): Response
    {
        // Seules les planches archivées peuvent être supprimées.
        if (Board::STATE_ARCHIVED !== $board->getState()) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createFormBuilder($board)
            ->setAction($this->generateUrl('advent_calendar_board_remove', ['board' => $board->getId()]))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            /* La suppression d'une Board est effectuée par
             * une tâche planifiée.
             * Toutes les Boards dans l'état "STATE_TO_REMOVE"
             * seront supprimées par la tâche.
             */
            $board->setState(Board::STATE_TO_REMOVE);

            $em->persist($board);
            $em->flush();

            if ($request->isXmlHttpRequest()) {
                $data = ['message' => 'OK'];
                $result = new JsonResponse($data);
            } else {
                $result = $this->redirectToRoute('advent_calendar_board_list_with_archives');
            }

            return $result;
        }

        return $this->render('advent-calendar/back-office/board/board_remove.html.twig', [
            'boardName' => $board->getName(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     *
     * @throws \LogicException
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     *
     * @return Response
     *
     * @Route("/add", name="advent_calendar_board_add")
     *
     * @Security("has_role('ROLE_EDIT')")
     */
    public function addBoardAction(Request $request): Response
    {
        // On ne doit pas avoir accès à la route "advent_calendar_board_add" si on n'a pas un rôle d'édition.
        if ('advent_calendar_board_add' === $request->get('_route') && !$this->isGranted('ROLE_EDIT')) {
            throw new AccessDeniedHttpException();
        }

        $gameRepository = $this->getDoctrine()->getRepository(Game::class);
        $game = $gameRepository->findBy(['name' => Game::GAME_ADVENT_CALENDAR]);

        if (empty($game)) {
            throw new NotFoundHttpException();
        }

        $board = new Board();
        $board->setName('');
        $board->setState(Board::STATE_DRAFT);
        $board->setDescription(Board::DEFAULT_DESCRIPTION);

        $form = $this->addBoardForm($board, 'advent_calendar_board_add');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($board);
            $em->flush();
            $id = $board->getId();

            return $this->redirectToRoute('advent_calendar_board_edit', ['board' => $id]);
        }

        return $this->render('advent-calendar/back-office/board/board_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Board $board
     * @param int   $state
     *
     * @throws AccessDeniedHttpException
     * @throws \LogicException
     *
     * @return JsonResponse
     *
     * @Route("/{board}/state/{state}", name="advent_calendar_board_change_state", options={"expose" = true})
     *
     * @Security("has_role('ROLE_EDIT')")
     */
    public function changeStateAction(Board $board, int $state): JsonResponse
    {
        if (!$this->isGranted('ROLE_EDIT')) {
            throw new AccessDeniedHttpException();
        }

        $board->setState($state);

        $em = $this->getDoctrine()->getManager();
        $em->persist($board);
        $em->flush();

        return new JsonResponse([
            'message' => 'OK',
        ]);
    }

    /**
     * @param Board  $board
     * @param string $actionRoute
     * @param bool   $isEditable
     *
     * @return FormInterface
     */
    private function editBoardForm(Board $board, string $actionRoute, bool $isEditable = true): FormInterface
    {
        $form = $this->createForm(
            EditBoardType::class,
            $board,
            [
                'action' => $this->generateUrl($actionRoute, ['board' => $board->getId()]),
                'method' => 'POST',
                'disabled' => !$isEditable,
            ]
        );

        return $form;
    }

    /**
     * @param Board  $board
     * @param string $actionRoute
     *
     * @return FormInterface
     */
    private function addBoardForm(Board $board, string $actionRoute): FormInterface
    {
        $form = $this->createForm(
            AddBoardType::class,
            $board,
            [
                'action' => $this->generateUrl($actionRoute, ['board' => $board->getId()]),
                'method' => 'POST',
            ]
        );

        return $form;
    }
}
