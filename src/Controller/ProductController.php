<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ProductController extends AbstractController
{
    #[Route('/', name: 'app_products')]
    public function indexProducts(ProductRepository $repository): Response
    {
        $products = $repository->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_detail')]
    public function showProduct(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/api/products', name: 'app_api_products', methods: ['GET'])]
    public function getProducts(ProductRepository $repository, SerializerInterface $serializer): JsonResponse
    { //@todo completer la route avec gestion d'erreur + doc api
        $products = $repository->findAll();

        $jsonProducts = $serializer->serialize($products, 'json');

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }
}
