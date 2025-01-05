<?php

namespace App\Controller;

use App\Entity\Advert;
use App\Enums\Currency;
use App\Entity\Category;
use App\Form\AdvertType;
use App\Service\FileUploader;
use App\Repository\AdvertRepository;
use Symfony\Component\Form\FormError;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/advert')]
final class AdvertController extends AbstractController
{
    #[Route(name: 'app_advert_index', methods: ['GET'])]
    public function index(AdvertRepository $advertRepository, Request $request, CategoryRepository $categoryRepository): Response
    {
        $page = max(1, $request->query->getInt('page', 1)); // Default to page 1
        $limit = 10; // Number of items per page

        $category = $request->query->get('category');
        $currency = $request->query->get('currency');

        $payload = [];

        if(! is_null($category)){
            $payload['categories.name'] = $category;
        }

        if(! is_null($currency)){
            $payload['currency'] = Currency::from($currency)->symbol();
        }

        $adverts = $advertRepository->findAllPaginatedBy(
            $payload,
            $page, $limit
        );

        $categories = $categoryRepository->findAll();

        return $this->render('home/advert/index.html.twig', [
            'adverts' => $adverts,
            'currentPage' => $page,
            'totalPages' => ceil($adverts->count() / $limit),
            'categories' => $categories,
            'currencies' => Currency::options(),
        ]);
    }

    #[Route('/new', name: 'app_advert_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, FileUploader $fileUploader): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $advert = new Advert();
        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();

            if($image){
                $advert->setImage($fileUploader->upload($image));
            }

            $selectedCategoryIds = $request->get('categories', []);

            foreach ($selectedCategoryIds as $categoryId) {
                $category = $entityManager->getRepository(Category::class)->find($categoryId);

                if ($category) {
                    $advert->addCategory($category);
                }
            }
            
            $advert->setUser($this->getUser());
            $entityManager->persist($advert);
            $entityManager->flush();

            return $this->redirectToRoute('app_profile', [], Response::HTTP_SEE_OTHER);
        }

        $categories = $categoryRepository->findAll();

        return $this->render('home/advert/new.html.twig', [
            'advert' => $advert,
            'form' => $form,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}', name: 'app_advert_show', methods: ['GET'])]
    public function show(Advert $advert): Response
    {
        return $this->render('home/advert/show.html.twig', [
            'advert' => $advert,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_advert_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', subject: 'advert')]
    public function edit(Request $request, EntityManagerInterface $entityManager, Advert $advert, CategoryRepository $categoryRepository, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(AdvertType::class, $advert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get selected category IDs from the form
            $categoryIds = $request->get('categories', []); // Assuming 'categories' is the field name
            
            if(empty($categoryIds)){
                $form->get('categories')->addError(new FormError('You must select at least one category.'));
            }
            else{
                $image = $form->get('image')->getData();
                
                if($image){
                    $advert->setImage($fileUploader->upload($image));
                }

                // Remove categories not in the submitted data
                foreach ($advert->getCategories() as $category) {
                    if (!in_array($category->getId(), $categoryIds)) {
                        $advert->removeCategory($category);
                    }
                }

                // Add newly selected categories
                foreach ($categoryIds as $id) {
                    $category = $categoryRepository->find($id);
                    
                    if (!$advert->getCategories()->contains($category)) {
                        $advert->addCategory($category);
                    }
                }
                $entityManager->flush();

                return $advert->isOwner($this->getUser()) ? $this->redirectToRoute('app_profile', [], Response::HTTP_SEE_OTHER) :
                    $this->redirectToRoute('app_advert_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        $categories = $categoryRepository->findAll();

        return $this->render('home/advert/edit.html.twig', [
            'advert' => $advert,
            'form' => $form,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}', name: 'app_advert_delete', methods: ['POST'])]
    #[IsGranted('edit', subject: 'advert')]
    public function delete(Request $request, Advert $advert, EntityManagerInterface $entityManager): Response
    {
        $isOwner = $advert->isOwner($this->getUser());

        $entityManager->remove($advert);
        $entityManager->flush();

        return $isOwner ? $this->redirectToRoute('app_profile', [], Response::HTTP_SEE_OTHER) :
            $this->redirectToRoute('app_advert_index', [], Response::HTTP_SEE_OTHER);
    }
}
