<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProduitController extends AbstractController
{
    #[Route('/api/produits/', name: 'api_get_produits')]
    public function getProduitsList(ProduitRepository $produitRepository, SerializerInterface $serializer): JsonResponse
    {

        $produitsList = $produitRepository->findAll();
        $jsonProduitslist = $serializer->serialize($produitsList, 'json');

        return new JsonResponse($jsonProduitslist, Response::HTTP_OK, [], true);
    }
}
