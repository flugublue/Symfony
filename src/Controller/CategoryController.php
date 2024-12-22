<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/category/creer', name: 'app_category_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie créée avec succès.');

            return $this->redirectToRoute('app_category_fetch');
        }

        return $this->render('category/creer.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/category/supprimer/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $category = $entityManager->getRepository(Category::class)->find($id);

        if (!$category) {
            $this->addFlash('error', 'Catégorie non trouvée.');
        } else {
            try {
                $entityManager->remove($category);
                $entityManager->flush();

                $this->addFlash('success', 'Catégorie supprimée avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Impossible de supprimer la catégorie : elle est utilisée par un ou plusieurs articles.');
            }
        }

        return $this->redirectToRoute('app_category_fetch');
    }

    #[Route('/category/modifier/{id}', name: 'app_category_edit')]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Catégorie modifiée avec succès.');

            return $this->redirectToRoute('app_category_fetch');
        }

        return $this->render('category/update.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }

    #[Route('/category/fetch', name: 'app_category_fetch')]
    public function fetch(EntityManagerInterface $entityManager): Response
    {
        $categories = $entityManager->getRepository(Category::class)->findAll();

        return $this->render('category/fetch.html.twig', [
            'categories' => $categories,
        ]);
    }
}
