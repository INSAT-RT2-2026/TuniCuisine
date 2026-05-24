<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Recipe;
use App\Entity\User;
use App\Enum\RecipeStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Recipe>
 */
final class RecipeVoter extends Voter
{
    public const EDIT = 'recipe_edit';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT && $subject instanceof Recipe;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Recipe $recipe */
        $recipe = $subject;

        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        $submitter = $recipe->getSubmittedBy();

        return $submitter !== null
            && $submitter->getId() === $user->getId()
            && \in_array($recipe->getStatus(), [RecipeStatus::Pending, RecipeStatus::Rejected], true);
    }
}
