<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializerInterface;
use Hateoas\Representation\PaginatedRepresentation;
use Hateoas\Representation\CollectionRepresentation;

class ProduitController extends AbstractController
{
    #[Route('/api/produits/', name: 'api_get_produits')]
    public function getProduitsList(SerializerInterface    $serializer,
                                    ProduitRepository      $produitRepository,
                                    Request                $request,
                                    TagAwareCacheInterface $cachePool,
                                    PaginatorInterface     $paginator
    ): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 4);

        $idCache = 'getAllProducts-' . $page . '-' . $limit;
        $produitsList = $cachePool->get($idCache, function (ItemInterface $item) use (
            $produitRepository, $page, $limit, $paginator
        ) {
            $pagination = $paginator->paginate(
                $produitRepository->queryFindAllWithPagination(),
                $page,
                $limit
            );
            $item->expiresAfter(3600);
            $item->tag('produitsCache');
            return new PaginatedRepresentation(
                new CollectionRepresentation($pagination->getItems()),
                'api_get_produits', // route
                array('limit' => $limit, 'page' => $page),
                (int)$pagination->getCurrentPageNumber(),
                (int)$pagination->getItemNumberPerPage(),
                ceil(intdiv((int)$pagination->getTotalItemCount(), (int)$pagination->getItemNumberPerPage())),
            );
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
