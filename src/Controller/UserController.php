<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{
    #[Route('/my_account', name: 'app_account')]
    #[IsGranted('ROLE_USER')]
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
    #[IsGranted('ROLE_USER')]
    public function apiAuthorize(EntityManagerInterface $em): Response
    {
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
    #[IsGranted('ROLE_USER')]
    public function accountDelete(UserRepository $userRepository,EntityManagerInterface $em): Response
    {
        $user = $userRepository->findOneBy(['user' => $this->getUser()]);
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_products');
    }
}
