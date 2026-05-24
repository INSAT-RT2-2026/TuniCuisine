<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\User;
use App\Entity\UserFavorite;
use App\Repository\UserFavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class FavoriteController extends AbstractController
{
    #[Route('/recipe/{id}/favorite', name: 'app_recipe_favorite_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggle(
        Recipe $recipe,
        Request $request,
        UserFavoriteRepository $favoriteRepository,
        EntityManagerInterface $em,
    ): Response {
        if (!$recipe->isPublished()) {
            throw $this->createNotFoundException();
        }

        /** @var User $user */
        $user = $this->getUser();
        $existing = $favoriteRepository->findOneByUserAndRecipe($user, $recipe);
        $favorited = false;

        if ($existing !== null) {
            $em->remove($existing);
            $em->flush();
        } else {
            $favorite = (new UserFavorite())->setUser($user)->setRecipe($recipe);
            $em->persist($favorite);
            $em->flush();
            $favorited = true;
        }

        if ($request->isXmlHttpRequest() || $request->headers->get('Accept') === 'application/json') {
            return new JsonResponse([
                'favorited' => $favorited,
                'recipeId' => $recipe->getId(),
            ]);
        }

        $referer = $request->headers->get('referer');

        return $this->redirect($referer ?: $this->generateUrl('app_home'));
    }
}
