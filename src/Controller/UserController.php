<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/my_account', name: 'app_account')]
    public function showMyAccount(OrderRepository $orderRepository): Response
    {
        // @todo ajouter is granted pour géré l'accès
        $user = $this->getUser();
        $orders = $orderRepository->findBy(['owner' => $user]);

        return $this->render('user/account.html.twig', [
            'orders' => $orders,
            'user' => $user,
        ]);
    }

    #[Route('/my_account/api_authorize', name: 'app_account_api_authorize')]
    public function apiAuthorize(EntityManagerInterface $em): Response
    {
        // @todo ajouter is granted pour géré l'accès

        /** @var User $user */
        $user = $this->getUser();

        // toggle bool isApiAuthorized
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
    public function accountDelete(UserRepository $userRepository,EntityManagerInterface $em): Response
    {
        // @todo ajouter is granted pour géré l'accès
        $user = $userRepository->findOneBy(['user' => $this->getUser()]);
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_products');
    }
}
