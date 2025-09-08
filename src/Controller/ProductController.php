<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
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

    #[Route('/api/products', name: 'app_api_products', methods: ['GET'])]
    #[IsGranted('ROLE_USER_VERIFIED')]
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
                            new OA\Property(property: 'shortDescription', type: 'string', example: 'Un cookie au chocolat'),
                            new OA\Property(property: 'longDescription', type: 'string', example: 'Un cookie au chocolat extra'),
                            new OA\Property(property: 'price', type: 'number', format: 'float', example: 3.5),
                            new OA\Property(property: 'picture', type: 'string', example: 'https://example.com/cookie-choco.jpg'),
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
    public function getProducts(#[CurrentUser] ?User $user, ProductRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        if (!$user->isApiAuthorized()) {
            return $this->json(['message' => "Votre accès API n'est pas activé."], Response::HTTP_FORBIDDEN);
        }
        $products = $repository->findAll();

        $jsonProducts = $serializer->serialize($products, 'json');

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }
}
