<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{
    #[Route('/my_account', name: 'app_account')]
    #[IsGranted('ROLE_USER_VERIFIED', message: 'Vous n\'avez pas de compte ou votre email n\'est pas encore vérifié')]
    /**
     * Affiche la page "Mon compte" avec la liste des commandes de l'utilisateur connecté.
     *
     * @param OrderRepository $orderRepository Référentiel pour récupérer les commandes de l'utilisateur.
     *
     * @return Response Réponse HTTP affichant la page de compte utilisateur.
     */
    public function showMyAccount(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $orders = $orderRepository->findBy(['owner' => $user]);

        return $this->render('user/account.html.twig', [
            'orders' => $orders,
            'user' => $user,
        ]);
    }

    #[Route('/my_account/api_authorize', name: 'app_account_api_authorize')]
    #[IsGranted('ROLE_USER_VERIFIED', message: 'Vous n\'avez pas de compte ou votre email n\'est pas encore vérifié')]
    /**
     * Active ou désactive l'autorisation d'accès API pour l'utilisateur connecté puis redirige vers le compte.
     *
     * @param EntityManagerInterface $em Gestionnaire d'entités pour persister la modification.
     *
     * @return Response Réponse HTTP de redirection vers la page de compte.
     */
    public function apiAuthorize(EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // on change l'état du bool isApiAuthorized
        if ($user->isApiAuthorized() === false) {
            $user->setIsApiAuthorized(true);
        }else {
            $user->setIsApiAuthorized(false);
        }

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_account');
    }

    #[Route('/my_account/delete', name: 'app_account_delete')]
    #[IsGranted('ROLE_USER_VERIFIED', message: 'Vous n\'avez pas de compte ou votre email n\'est pas encore vérifié')]
    /**
     * Supprime le compte utilisateur courant, déconnecte l'utilisateur et redirige vers la liste des produits.
     *
     * @param UserRepository $userRepository Référentiel pour charger l'utilisateur depuis la base de données.
     * @param EntityManagerInterface $em Gestionnaire d'entités pour supprimer l'utilisateur.
     * @param Security $security Service de sécurité pour effectuer le logout programmatique.
     *
     * @return Response Réponse HTTP de redirection après suppression du compte.
     */
    public function accountDelete(UserRepository $userRepository,EntityManagerInterface $em, Security $security): Response
    {
        $user = $userRepository->find($this->getUser()->getId());
        $security->logout(false); // Logout de l'utilisateur, csrf désactivé pour la suppression du compte
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Votre compte a bien été supprimer');
        return $this->redirectToRoute('app_products');
    }
}
