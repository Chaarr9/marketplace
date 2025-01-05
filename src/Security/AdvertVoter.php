<?php


namespace App\Security;

use App\Entity\User;
use App\Entity\Advert;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AdvertVoter extends Voter
{
    // these strings are just invented: you can use anything
    const EDIT = 'edit';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        // only vote on `Advert` objects
        if (!$subject instanceof Advert) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Advert $advert */
        $advert = $subject;

        return match($attribute) {
            self::EDIT => $this->canEdit($advert, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canEdit(Advert $advert, User $user): bool
    {
        return $user === $advert->getUser() || $user->hasRole('ROLE_MODERATOR') || $user->hasRole('ROLE_ADMIN');
    }
}