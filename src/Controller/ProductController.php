<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Products')]
final class ProductController extends AbstractController
{
    #[Route('/', name: 'app_products')]
    public function indexProducts(ProductRepository $repository,Request $request): Response
    {
        $products = $repository->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_detail')]
    /**
     * Affiche le détail d'un produit.
     *
     * @param Product $product Le produit à afficher (résolu automatiquement via ParamConverter).
     *
     * @return Response Réponse HTTP affichant la vue du produit.
     */
    public function showProduct(Product $product): Response
    {
        // Prévoir pagination si le nombre de produits augmente fortement
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
    /**
     * Retourne la liste des produits au format JSON pour les utilisateurs ayant autorisé l'accès API.
     *
     * @param User|null $user L'utilisateur courant injecté via l'attribut CurrentUser (peut être null).
     * @param ProductRepository $repository Référentiel pour récupérer les produits.
     * @param SerializerInterface $serializer Sérialiseur pour transformer les entités en JSON.
     *
     * @return JsonResponse Réponse JSON contenant la liste des produits ou un message d'erreur si l'accès API n'est pas autorisé.
     */
    public function getProducts(#[CurrentUser] ?User $user, ProductRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        //On récupère le user avec CurrentUser (évite de récupérer un user logué via le site), on test l'accès à l'API
        if (!$user->isApiAuthorized()) {
            return $this->json(['message' => "Votre accès API n'est pas activé."], Response::HTTP_FORBIDDEN);
        }
        $products = $repository->findAll();

        try{
            $jsonProducts = $serializer->serialize($products, 'json');
        } catch (ExceptionInterface $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }
}
