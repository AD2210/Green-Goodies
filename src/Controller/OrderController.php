<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class OrderController extends AbstractController
{
    #[Route('/order/create', name: 'app_order_create')]
    #[IsGranted('ROLE_USER')]
    public function createOrder(EntityManagerInterface $em, CartRepository $cartRepository): Response
    {
        $cart = $cartRepository->findOneBy(['owner' => $this->getUser()]);
        $order = Order::createOrderFromCart($cart);
        $em->persist($order);
        $em->flush();

        //$this->confirmOrder(); //On envoie le mail de confirmation de commande

        $cart->clearCartItems();
        $em->flush();

        return $this->redirectToRoute('app_products');
    }

    private function confirmOrder(MailerInterface $mailer): void
    { //@todo regarder comment utiliser mailer pour envoyer l'email
//        $user = $this->getUser();
//        $mailer->send(
//            'test',
//            new Envelope('',
//            $user->getUserIdentifier()
//            )
//        )

    }
}
