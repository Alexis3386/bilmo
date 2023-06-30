<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Exception;
use JMS\Serializer\SerializationContext;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializerInterface;
use Hateoas\Representation\PaginatedRepresentation;
use Hateoas\Representation\CollectionRepresentation;

class UserController extends AbstractController
{

    #[Route('api/client/users/', name: 'api_create_user', methods: ['POST'])]
    public function createUtilisateur(Request                $request,
                                      SerializerInterface    $serializer,
                                      EntityManagerInterface $em,
                                      UrlGeneratorInterface  $urlGenerator,
                                      ValidatorInterface     $validator,
                                      TagAwareCacheInterface $cachePool): JsonResponse
    {
        $utilisateur = $serializer->deserialize($request->getContent(), Utilisateur::class, 'json');
        $errors = $validator->validate($utilisateur);

        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'),
                Response::HTTP_BAD_REQUEST,
                [],
                true);
        }

        /** @var Client $client */
        $client = $this->getUser();
        $client->addUtilisateur($utilisateur);

        $em->persist($utilisateur);
        $em->flush();

        $cachePool->invalidateTags(['usersCache' . $client->getId()]);

        $jsonUtilisateur = $serializer->serialize($utilisateur, 'json');
        $location = $urlGenerator->generate('api_get_user', ['id' => $utilisateur->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonUtilisateur, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/api/client/users/', name: 'api_get_users', methods: ['GET'])]
    public function getUsersList(SerializerInterface    $serializer,
                                 Request                $request,
                                 PaginatorInterface     $paginator,
                                 UtilisateurRepository  $utilisateurRepository,
                                 TagAwareCacheInterface $cachePool
    ): Response
    {

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 3);
        /** @var Client $client */
        $client = $this->getUser();

        $idCache = 'getAllUsers-' . $page . '-' . $limit . '-' . $client->getId();

        $userList = $cachePool->get($idCache, function (ItemInterface $item) use (
            $utilisateurRepository, $page, $limit, $paginator, $client, $serializer
        ) {
            $pagination = $paginator->paginate(
                $utilisateurRepository->findByClientQuery($client),
                $page,
                $limit
            );
            $item->expiresAfter(3600);
            $item->tag('usersCache' . $client->getId());

            $paginatedRepresentation = new PaginatedRepresentation(
                new CollectionRepresentation($pagination->getItems()),
                'api_get_users', // route
                array('limit' => $limit, 'page' => $page),
                $pagination->getCurrentPageNumber(),
                $pagination->getItemNumberPerPage(),
                ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage())
            );
            $context = SerializationContext::create()->setGroups(['Default', 'items' => ['getUsers', 'client' => ['getUsers']]]);
            return $serializer->serialize($paginatedRepresentation, 'json', $context);
        });

        return new JsonResponse($userList, Response::HTTP_OK, [], true);
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    #[Route('/api/client/users/{id}', name: 'api_get_user', methods: ['GET'])]
    public function getUserDetails(Utilisateur            $utilisateur,
                                   SerializerInterface    $serializer,
                                   TagAwareCacheInterface $cachePool): Response
    {
        /** @var Client $client */
        $client = $this->getUser();

        if (!$client->getUtilisateurs()->contains($utilisateur)) {
            throw new Exception('403 acces denied');
        }
        $idCache = 'getUser' . $utilisateur->getId();
        $utilisateurCache = $cachePool->get($idCache, function (ItemInterface $item) use ($utilisateur, $serializer, $client) {
            $item->tag('usersCache' . $client->getId());
            $context = SerializationContext::create()->setGroups(['getUser']);
            return $serializer->serialize($utilisateur, 'json', $context);
        });
        return new JsonResponse($utilisateurCache, Response::HTTP_OK, [], true);
    }

    /**
     * @throws ORMException
     * @throws Exception
     */
    #[Route('api/client/users/{id}', name: 'api_delete_user', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un utilisateur')]
    public function deleteUtilisateur(Utilisateur            $utilisateur,
                                      EntityManagerInterface $em,
                                      TagAwareCacheInterface $cachePool): JsonResponse
    {
        /** @var Client $client */
        $client = $this->getUser();

        if (!$client->getUtilisateurs()->contains($utilisateur)) {
            return new JsonResponse('Vous n\'avez pas les droits pour supprimer cette ressource', Response::HTTP_FORBIDDEN);
        }

        try {
            $em->remove($utilisateur);
            $em->flush();
            $cachePool->invalidateTags(['usersCache' . $client->getId()]);
        } catch (ORMException $e) {
            throw new ORMException('erreur : ' . $e);
        }
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('api/client/users/{id}', name: "api_update_user", methods: ['PUT'])]
    public function updateBook(Request                $request,
                               SerializerInterface    $serializer,
                               Utilisateur            $currentUser,
                               EntityManagerInterface $em,
                               ValidatorInterface     $validator,
                               TagAwareCacheInterface $cache): JsonResponse
    {

        /** @var Client $client */
        $client = $this->getUser();

        if (!$client->getUtilisateurs()->contains($currentUser)) {
            return new JsonResponse('Vous n\'avez pas les droits pour modifier cette ressource', Response::HTTP_FORBIDDEN);
        }

        $newUser = $serializer->deserialize($request->getContent(), Utilisateur::class, 'json');
        $currentUser->setNom($newUser->getNom());
        $currentUser->setAdresseMail($newUser->getAdresseMail());

        $errors = $validator->validate($currentUser);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $currentUser->setClient($client);

        $em->persist($currentUser);
        $em->flush();

        $cache->invalidateTags(['usersCache' . $client->getId()]);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
