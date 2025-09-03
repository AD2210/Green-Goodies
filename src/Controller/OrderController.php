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
        $order = new Order();
        $cart = $cartRepository->findOneBy(['owner' => $this->getUser()]);
        $order->setOwner($this->getUser());
        $order->addOrderItem($cart->getCartItems()); // necessite une convertion à traiter dans un service


        return $this->redirectToRoute('app_products');
    }

    #[Route('/order/confirm', name: 'app_order_confirm')]
    public function confirmOrder(MailerInterface $mailer): Response
    { //@todo regarder comment utiliser mailer pour envoyer l'email
//        $user = $this->getUser();
//        $mailer->send(
//            'test',
//            new Envelope('',
//            $user->getUserIdentifier()
//            )
//        )

        return $this->redirectToRoute('app_products');
    }
}
