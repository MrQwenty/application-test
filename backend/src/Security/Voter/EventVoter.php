<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Document\Event;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use App\Security\Constant\DocumentVoterAttributes;
use App\Security\Constant\Roles;
use function in_array;

final class EventVoter extends Voter
{

    private array $supportedAttributes;


    public function __construct(private Security $security)
    {
        $this->supportedAttributes = array(
            DocumentVoterAttributes::CREATE,
            DocumentVoterAttributes::READ,
            DocumentVoterAttributes::UPDATE,
            DocumentVoterAttributes::DELETE,
        );
    }

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return isset($subject)
            && is_a($subject, Event::class)
            && in_array($attribute, $this->supportedAttributes);
    }


    /**
     * @inheritDoc
     * @param Event $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            DocumentVoterAttributes::CREATE => $this->canCreate($subject, $token),
            DocumentVoterAttributes::READ => $this->canRead($subject, $token),
            DocumentVoterAttributes::UPDATE => $this->canUpdate($subject, $token),
            DocumentVoterAttributes::DELETE => $this->canDelete($subject, $token),
            default => false,
        };
    }

    private function canCreate(Event $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted(Roles::ROLE_ADMIN);
    }

    private function canRead(Event $subject, TokenInterface $token): bool
    {
        return true;
    }

    private function canUpdate(Event $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted(Roles::ROLE_ADMIN);
    }


    private function canDelete(Event $subject, TokenInterface $token): bool
    {
        return $this->security->isGranted(Roles::ROLE_ADMIN);
    }
}
