<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1)); // Default to page 1
        $limit = 10; // Number of items per page

        $users = $userRepository->findAllPaginated($page, $limit);

        return $this->render('dashboard/user/index.html.twig', [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => ceil($users->count() / $limit),
        ]);
    }

    #[Route('/{id}/edit/{action}', name: 'app_user_edit', methods: ['POST'])]
    public function edit(User $user, string $action, EntityManagerInterface $entityManager): Response
    {
        if($action === 'promote'){
            $user->setRoles(['ROLE_MODERATOR']);
        }
        else{
            $user->setRoles([]);
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
