<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Product;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(CartRepository $repository): Response
    { //@todo ajout d'un isGranted

        $cart = $repository->findOneBy(['owner' => $this->getUser()]);
        $cartItems = $cart->getCartItems();

        //@todo ajouter le service calcul de prix total (servira pour cart et order)

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function addToCart(Product $product, EntityManagerInterface $em, CartRepository $cartRepository): Response
    { //@todo ajout d'un isGranted si non erreur si non loggé + gestion d'erreur
        // pour le moment pas de quantité à géré, on envoie 1 à chaque fois

        $cart = $cartRepository->findOneBy(['owner' => $this->getUser()]);
        $cart->addCartItem(new CartItem($product, 1));

        return $this->redirectToRoute('app_products'); // on retourne à la liste des produits
    }

    #[Route('/cart/clear', name: 'app_cart_clear')]
    public function clearCart(EntityManagerInterface $em, CartRepository $cartRepository): Response
    { //@todo ajout d'un isGranted si non erreur si non loggé + gestion d'erreur

        $cart = $cartRepository->findOneBy(['owner' => $this->getUser()]);
        $cart->clearCartItems();
        $em->flush();

        return $this->redirectToRoute('app_products'); // on retourne à la liste des produits
    }
}
