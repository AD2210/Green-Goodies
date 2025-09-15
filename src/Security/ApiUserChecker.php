<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Vérifie l'état d'authentification de l'utilisateur avant et après la connexion.
 *
 * Cette implémentation vérifie que l'utilisateur est autorisé pour l'accès API et
 * possède un compte vérifié durant les contrôles de pré-authentification.
 */
class ApiUserChecker implements UserCheckerInterface
{
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

        if (!$user->isApiAuthorized()) {
            throw new HttpException(
                403,
                'Votre accès API n\'est pas activé'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Rien à faire ici
    }
}
