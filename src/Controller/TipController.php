<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CommunityTip;
use App\Entity\User;
use App\Enum\ModerationStatus;
use App\Repository\CommunityTipRepository;
use App\Service\TipWorkflowService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TipController extends AbstractController
{
    #[Route('/api/tips', name: 'api_tips_list', methods: ['GET'])]
    public function list(CommunityTipRepository $repository): JsonResponse
    {
        $tips = array_map(
            static fn (CommunityTip $tip) => [
                'id' => $tip->getId(),
                'authorName' => $tip->getAuthorName(),
                'content' => $tip->getContent(),
                'createdAt' => $tip->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ],
            $repository->findLatestPublished()
        );

        return $this->json(['tips' => $tips]);
    }

    #[Route('/api/tips', name: 'api_tips_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        TipWorkflowService $tipWorkflow,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!\is_array($data)) {
            return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $content = trim((string) ($data['content'] ?? ''));
        if ($content === '') {
            return $this->json(['error' => 'Tip content is required'], Response::HTTP_BAD_REQUEST);
        }

        if (mb_strlen($content) > 2000) {
            return $this->json(['error' => 'Tip is too long'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();
        if ($user instanceof User) {
            $tip = (new CommunityTip())->setContent($content);

            if ($this->isGranted('ROLE_ADMIN')) {
                $tipWorkflow->publishDirectly($tip, $user);

                return $this->json([
                    'tip' => [
                        'id' => $tip->getId(),
                        'authorName' => $tip->getAuthorName(),
                        'content' => $tip->getContent(),
                        'createdAt' => $tip->getCreatedAt()->format(\DateTimeInterface::ATOM),
                    ],
                    'message' => 'Your tip is now live on the heritage board.',
                    'pending' => false,
                ], Response::HTTP_CREATED);
            }

            $tipWorkflow->submitPending($tip, $user);

            return $this->json([
                'tip' => null,
                'message' => 'Your tip was submitted for admin review. You will be notified when it is published.',
                'pending' => true,
            ], Response::HTTP_ACCEPTED);
        }

        $authorName = trim((string) ($data['authorName'] ?? ''));
        if ($authorName === '') {
            return $this->json(['error' => 'Name and tip are required (or log in to submit)'], Response::HTTP_BAD_REQUEST);
        }

        if (mb_strlen($authorName) > 120) {
            return $this->json(['error' => 'Name is too long'], Response::HTTP_BAD_REQUEST);
        }

        $tip = (new CommunityTip())
            ->setAuthorName($authorName)
            ->setContent($content)
            ->setStatus(ModerationStatus::Published);

        $em->persist($tip);
        $em->flush();

        return $this->json([
            'tip' => [
                'id' => $tip->getId(),
                'authorName' => $tip->getAuthorName(),
                'content' => $tip->getContent(),
                'createdAt' => $tip->getCreatedAt()->format(\DateTimeInterface::ATOM),
            ],
            'pending' => false,
        ], Response::HTTP_CREATED);
    }
}
