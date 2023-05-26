<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProduitController extends AbstractController
{
    #[Route('/api/produits/', name: 'api_get_produits')]
    public function getProduitsList(SerializerInterface    $serializer,
                                    ProduitRepository      $produitRepository,
                                    Request                $request,
                                    TagAwareCacheInterface $cachePool,
    ): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = 'getAllBooks-' . $page . '-' . $limit;
        $produitsList = $cachePool->get($idCache, function (ItemInterface $item) use (
            $produitRepository, $page, $limit
        ) {
            $item->tag('produitsCache');
            return $produitRepository->findAllWithPagination($page, $limit);
        });

        $jsonProduitslist = $serializer->serialize($produitsList, 'json');
        return new JsonResponse($jsonProduitslist, Response::HTTP_OK, [], true);
    }

    #[Route('/api/produits/{id}', name: 'api_get_produit')]
    public function getProduit(SerializerInterface    $serializer,
                               Produit                $produit,
                               TagAwareCacheInterface $cachePool,
    ): JsonResponse
    {
        $idCache = 'getProduit' . $produit->getId();
        $produit = $cachePool->get($idCache, function (ItemInterface $item) use ($produit) {
            $item->tag('produitCache');
            return $produit;
        });
        $jsonProduit = $serializer->serialize($produit, 'json');
        return new JsonResponse($jsonProduit, Response::HTTP_OK, [], true);
    }
}
