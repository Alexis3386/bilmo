<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "api_get_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups={"getUsers", "getUser"})
 * )
 *
 * @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "api_delete_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getUsers", "getUser"})
 * )
 *
 * @Hateoas\Relation(
 *      "update",
 *      href = @Hateoas\Route(
 *          "api_update_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups={"getUsers", "getUser"})
 * )
 */
#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getUsers', 'getUser'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers', 'getUser'])]
    #[Assert\NotBlank(message: 'Le nom de l\'utilisateur ne doit pas être vide')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers', 'getUser'])]
    #[Assert\NotBlank(message: 'L\'adresse email ne doit pas être vide')]
    private ?string $adresseMail = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    private Client $client;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getAdresseMail(): ?string
    {
        return $this->adresseMail;
    }

    public function setAdresseMail(string $adresseMail): self
    {
        $this->adresseMail = $adresseMail;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}