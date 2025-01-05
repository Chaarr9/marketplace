<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Category;
use App\Service\FileUploader;
use App\Repository\AdvertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(AdvertRepository $advertRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $page = max(1, $request->query->getInt('page', 1)); // Default to page 1
        $limit = 10; // Number of items per page

        $adverts = $advertRepository->findAllPaginatedForUser($user, $page, $limit);

        return $this->render('home/profile/index.html.twig', [
            'adverts' => $adverts,
            'currentPage' => $page,
            'totalPages' => ceil($adverts->count() / $limit),
        ]);
    }

    #[Route('/profile/avatar', name: 'app_profile_photo', methods: ['POST'])]
    public function update(Request $request, FileUploader $fileUploader, EntityManagerInterface $entityManager,): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $image = $request->files->get('avatar');

        $user->setImage($fileUploader->upload($image));

        $entityManager->flush();

        return $this->redirectToRoute('app_profile', [], Response::HTTP_SEE_OTHER);
    }
}
