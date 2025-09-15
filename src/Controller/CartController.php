<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    #[IsGranted('ROLE_USER_VERIFIED', message: 'Vous n\'avez pas de compte ou votre email n\'est pas encore vérifié')]
    public function index(CartRepository $repository): Response
    {
        $cart = $repository->findOneBy(['owner' => $this->getUser()]);
        $cartItems = $cart?->getCartItems();

        return $this->render('cart/cart.html.twig', [
            'cartItems' => $cartItems,
            'cart' => $cart,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    #[IsGranted('ROLE_USER_VERIFIED', message: 'Vous n\'avez pas de compte ou votre email n\'est pas encore vérifié')]
    public function addToCart(Product $product, EntityManagerInterface $em, CartRepository $cartRepository): Response
    {
        // on récupère le panier en cours sinon, on en crée un nouveau
        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['owner' => $user]);
        if ($cart === null) {
            $cart = new Cart();
            /** @var User $user */
            $cart->setOwner($user);
            $em->persist($cart);
        }

        //On parcours les items du panier
        $cardtItems = $cart->getCartItems();

        if (empty($cardtItems->toArray())) {
            $cart->addCartItem(new CartItem($product, 1)); // si le panier est vide, on l'ajoute le produit au panier
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
                $cart->addCartItem(new CartItem($product, 1)); // si le produit n'existe pas, on l'ajoute le produit au panier
            }
        }

        $em->flush();

        $this->addFlash('success','Le produit à bien été ajouter au panier');
        return $this->redirectToRoute('app_products');
    }

    #[Route('/cart/clear', name: 'app_cart_clear')]
    #[IsGranted('ROLE_USER_VERIFIED', message: 'Vous n\'avez pas de compte ou votre email n\'est pas encore vérifié')]
    public function clearCart(EntityManagerInterface $em, CartRepository $cartRepository): Response
    {
        $cart = $cartRepository->findOneBy(['owner' => $this->getUser()]);
        $cart->clearCartItems();
        $em->flush();

        return $this->redirectToRoute('app_products'); // on retourne à la liste des produits
    }
}
