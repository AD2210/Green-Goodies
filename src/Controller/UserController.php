<?php

namespace App\Controller;

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
        $orders = $orderRepository->findBy(['user' => $this->getUser()]);

        return $this->render('user/account.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/my_account/api_authorize', name: 'app_account_api_authorize')]
    public function apiAuthorize(UserRepository $userRepository,EntityManagerInterface $em): Response
    {
        // @todo ajouter is granted pour géré l'accès
        $user = $userRepository->findOneBy(['user' => $this->getUser()]);
        $user->setIsApiAuthorized(true);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_account');
    }
}
