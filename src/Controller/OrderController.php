<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\CartRepository;
use App\Service\OrderMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class OrderController extends AbstractController
{
    #[Route('/order/create', name: 'app_order_create')]
    #[IsGranted('ROLE_USER_VERIFIED', message: 'Vous n\'avez pas de compte ou votre email n\'est pas encore vérifié')]
    public function createOrder(EntityManagerInterface $em, CartRepository $cartRepository, OrderMailer $orderMailer): Response
    {
        $cart = $cartRepository->findOneBy(['owner' => $this->getUser()]);
        $order = Order::createOrderFromCart($cart);
        $em->persist($order);
        $em->flush();

        $orderMailer->sendOrderConfirmation($order); // On envoie la confirmation de commande

        $cart->clearCartItems();
        $em->flush();

        return $this->redirectToRoute('app_products',['flash_type'=>'success', 'flash_message'=>'votre commande est accepté, vous aller recevoir un email de confirmation']);
    }
}
