<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Products')]
final class ProductController extends AbstractController
{
    #[Route('/', name: 'app_products')]
    public function indexProducts(ProductRepository $repository,Request $request): Response
    {
        if ($request->query->has('flash_type')) {
            $this->addFlash($request->query->get('flash_type'), $request->query->get('flash_message'));
        }

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

    #[OA\Get(
        path: '/api/products',
        summary: 'Liste des produits',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des produits',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Cookie Choco'),
                            new OA\Property(property: 'price', type: 'number', format: 'float', example: 3.5),
                        ],
                        type: 'object'
                    )
                )
            ),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 403, description: 'Accès API non activé'),
            new OA\Response(response: 500, description: 'Erreur serveur')
        ]
    )]
    #[Route('/api/products', name: 'app_api_products', methods: ['GET'])]
    public function getProducts(ProductRepository $repository, SerializerInterface $serializer): JsonResponse
    { //@todo completer la route avec gestion d'erreur + doc api
        $products = $repository->findAll();

        $jsonProducts = $serializer->serialize($products, 'json');

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }
}
