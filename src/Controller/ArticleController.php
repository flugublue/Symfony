<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article/creer', name: 'app_article_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads')] string $brochuresDirectory): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move($brochuresDirectory, $newFilename);

                $article->setImage('uploads/' . $newFilename);
            } else {
                $article->setImage(null);
            }

            $this->addFlash('success', 'Article créé avec succès.');

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_fetch');
        }

        return $this->render('article/creer.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/supprimer/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $entityManager, #[Autowire('%kernel.project_dir%/public')] string $publicDir): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
        } else {
            if ($article->getImage()) {
                $imagePath = $publicDir . '/' . $article->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($article);
            $entityManager->flush();

            $this->addFlash('success', 'Article supprimé avec succès.');
        }

        return $this->redirectToRoute('app_article_fetch');
    }

    #[Route('/article/lire/{id}', name: 'app_article_read')]
    public function read(int $id, EntityManagerInterface $entityManager): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Aucun article trouvé pour cet ID');
        }

        return $this->render('article/lire.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/article/modifier/{id}', name: 'app_article_edit')]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, SluggerInterface $slugger, #[Autowire('%kernel.project_dir%/public/uploads')] string $brochuresDirectory): Response
    {
        $originalImage = $article->getImage(); 

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $deleteImage = $form->get('deleteImage')->getData();
            if ($deleteImage) {
                if ($originalImage && file_exists($brochuresDirectory . '/' . $originalImage)) {
                    unlink($brochuresDirectory . '/' . $originalImage);
                }
                $article->setImage(null); 
            }

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move($brochuresDirectory, $newFilename);
                $article->setImage('uploads/' . $newFilename); 
            } elseif (!$deleteImage) {
                $article->setImage($originalImage);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Article modifié avec succès.');

            return $this->redirectToRoute('app_article_fetch');
        }

        return $this->render('article/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/fetch', name: 'app_article_fetch')]
    public function fetch(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('article/fetch.html.twig', [
            'articles' => $articles,
        ]);
    }
}
