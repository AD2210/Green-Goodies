<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * Vérifie l'état du compte utilisateur lors de l'authentification.
 * Implémente UserCheckerInterface pour effectuer des vérifications
 * avant et après l'authentification.
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * Vérifie l'état du compte avant l'authentification.
     * Lance une exception si le compte n'est pas vérifié.
     *
     * @param UserInterface $user L'utilisateur à vérifier
     * @throws CustomUserMessageAccountStatusException Si le compte n'est pas vérifié
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte n’est pas vérifié. Veuillez confirmer votre adresse email.',
                code: 12
            );
        }
    }

    /**
     * Effectue des vérifications après l'authentification.
     * Actuellement sans implémentation.
     *
     * @param UserInterface $user L'utilisateur authentifié
     */
    public function checkPostAuth(UserInterface $user): void
    {
        // Rien à faire ici
    }
}
