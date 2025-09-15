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
    /**
     * Crée une commande à partir du panier de l'utilisateur connecté, vide le panier,
     * envoie un email de confirmation puis redirige vers la liste des produits.
     *
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine pour la persistance de la commande.
     * @param CartRepository $cartRepository Référentiel pour récupérer le panier de l'utilisateur.
     * @param OrderMailer $orderMailer Service d'envoi des emails de confirmation de commande.
     *
     * @return Response Réponse HTTP de redirection vers la page des produits.
     */
    public function createOrder(EntityManagerInterface $em, CartRepository $cartRepository, OrderMailer $orderMailer): Response
    {
        $cart = $cartRepository->findOneBy(['owner' => $this->getUser()]);
        $order = Order::createOrderFromCart($cart); //Conversion du panier vers commande
        $em->persist($order);
        $cart->clearCartItems();
        $em->flush();

        $orderMailer->sendOrderConfirmation($order); // On envoie la confirmation de commande

        $this->addFlash('success','votre commande est accepté, vous aller recevoir un email de confirmation' );
        return $this->redirectToRoute('app_products');
    }
}
