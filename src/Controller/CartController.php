<?php

namespace App\Controller;

use App\Entity\Cart;
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
        $cartItems = $cart?->getCartItems();

        //@todo ajouter le service calcul de prix total (servira pour cart et order)

        return $this->render('cart/cart.html.twig', [
            'cartItems' => $cartItems,
            'cart' => $cart,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function addToCart(Product $product, EntityManagerInterface $em, CartRepository $cartRepository): Response
    { //@todo ajout d'un isGranted si non erreur si non loggé + gestion d'erreur
        // on récupère le panier en cours si non on en créer un nouveau
        $cart = $cartRepository->findOneBy(['owner' => $this->getUser()]);
        if ($cart === null) {
            $cart = new Cart();
            $cart->setOwner($this->getUser());
            $em->persist($cart);
        }

        //On parcours les items du panier
        $cardtItems = $cart->getCartItems();

        if (empty($cardtItems->toArray())) {
            $cart->addCartItem(new CartItem($product, 1)); // si le panier est vide, on ajoute le produit au panier
        } else {
            $checkItem = false;
            foreach ($cardtItems as $item) {
                if ($item->getProduct() === $product) {
                    $item->setQuantity($item->getQuantity() + 1); // si le produit existe, on incrémente la quantité
                    $checkItem = true;
                    break;
                }
            }
            if (!$checkItem) {
                $cart->addCartItem(new CartItem($product, 1));
            }
        }


        $em->flush();
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
