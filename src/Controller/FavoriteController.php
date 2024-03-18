<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class FavoriteController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addFavorite(Request $request, string $omdbId): JsonResponse
{
    // Récupérer les données de la requête
    $data = json_decode($request->getContent(), true);

    // Récupérer l'utilisateur actuellement connecté
    $user = $this->getUser();

    // Vérifier si l'utilisateur est connecté
    if (!$user) {
        return new JsonResponse(['error' => 'Utilisateur non identifié'], 401);
    }

    // Créer une nouvelle entrée dans la table favorite
    $favorite = new Favorite();
    $favorite->setOmdbId($omdbId);
    $favorite->setUserId($user); 

    // Enregistrer dans la base de données
    $this->entityManager->persist($favorite);
    $this->entityManager->flush();

    return new JsonResponse(['message' => 'Favoris enregistré'], 201);
}


// public function getFavorite(FavoriteRepository $favoriteRepository): JsonResponse
// {
//     // Récupérer l'utilisateur actuellement connecté
//     $user = $this->getUser();

//     // Vérifier si l'utilisateur est connecté
//     if (!$user) {
//         return new JsonResponse(['error' => 'User not authenticated'], 401);
//     }

//     // Récupérer les favoris de l'utilisateur
//     $favorites = $favoriteRepository->findBy(['userId' => $user]);

//     // Retourner les favoris sous forme de réponse JSON
//     return new JsonResponse($favorites, 200);
// }

public function getFavorite(FavoriteRepository $favoriteRepository): JsonResponse
{
    // Récupérer l'utilisateur actuellement connecté
    $user = $this->getUser();

    // Vérifier si l'utilisateur est connecté
    if (!$user) {
        return new JsonResponse(['error' => 'User not authenticated'], 401);
    }

    // Récupérer les favoris de l'utilisateur avec leurs détails
    $favorites = $favoriteRepository->findBy(['userId' => $user]);

    // Préparer les données à envoyer dans la réponse JSON
    $formattedFavorites = [];
    foreach ($favorites as $favorite) {
        $formattedFavorites[] = [
            'id' => $favorite->getId(),
            'omdbId' => $favorite->getOmdbId(),
            // Autres propriétés que vous souhaitez inclure
        ];
    }

    // Retourner les favoris sous forme de réponse JSON
    return new JsonResponse($formattedFavorites, 200);
}

public function deleteFavorite(Request $request, FavoriteRepository $favoriteRepository, EntityManagerInterface $entityManager): JsonResponse
{
    // Récupérer l'utilisateur actuellement connecté
    $user = $this->getUser();

    // Vérifier si l'utilisateur est connecté
    if (!$user) {
        return new JsonResponse(['error' => 'User not authenticated'], 401);
    }

    // Récupérer l'ID du favori à supprimer à partir de la requête
    $favoriteId = $request->get('favoriteId');

    // Recherche du favori dans la base de données
    $favorite = $favoriteRepository->findOneBy(['id' => $favoriteId, 'userId' => $user]);

    // Vérifier si le favori existe
    if (!$favorite) {
        return new JsonResponse(['error' => 'Favorite not found'], 404);
    }

    // Supprimer le favori de la base de données
    $entityManager->remove($favorite);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Favorite deleted successfully'], 200);
}
}
