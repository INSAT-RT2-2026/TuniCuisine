<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Recipe;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class RecipeModerationMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $fromEmail,
    ) {
    }

    public function sendRecipeDecision(Recipe $recipe, User $user, bool $approved, ?string $reason = null): void
    {
        $subject = $approved
            ? sprintf('Your recipe "%s" was approved', $recipe->getName())
            : sprintf('Your recipe "%s" was declined', $recipe->getName());

        $body = $approved
            ? sprintf("Bonjour %s,\n\nYour recipe \"%s\" is now published on TuniCuisine.\n\nView it: %s\n", $user->getDisplayName(), $recipe->getName(), $this->recipeUrl($recipe))
            : sprintf("Bonjour %s,\n\nYour recipe \"%s\" was not approved.\n\nReason: %s\n\nYou can edit and resubmit from My Recipes.\n", $user->getDisplayName(), $recipe->getName(), $reason ?? 'No reason provided.');

        $this->send($user->getEmail(), $subject, $body);
    }

    public function sendTipDecision(User $user, string $tipPreview, bool $approved, ?string $reason = null): void
    {
        $subject = $approved ? 'Your cooking tip was approved' : 'Your cooking tip was declined';
        $body = $approved
            ? sprintf("Bonjour %s,\n\nYour tip is now visible on the Cooking Tips page.\n", $user->getDisplayName())
            : sprintf("Bonjour %s,\n\nYour tip was not approved.\nReason: %s\n\nTip: %s\n", $user->getDisplayName(), $reason ?? 'No reason provided.', $tipPreview);

        $this->send($user->getEmail(), $subject, $body);
    }

    private function send(string $to, string $subject, string $text): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($to)
            ->subject($subject)
            ->text($text);

        try {
            $this->mailer->send($email);
        } catch (\Throwable) {
            // Mailer may be null://null in dev — do not break the flow
        }
    }

    private function recipeUrl(Recipe $recipe): string
    {
        return $this->urlGenerator->generate(
            'app_recipe_show',
            ['id' => $recipe->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
