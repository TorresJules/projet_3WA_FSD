<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("admin/item")
 */
class ItemController extends AbstractController
{
    /**
     * @Route("/", name="app_item_index", methods={"GET"})
     */
    public function index(ItemRepository $itemRepository): Response
    {
        // Affiche la liste de tous les articles dans la vue 'item/index.html.twig'
        return $this->render('item/index.html.twig', [
            'items' => $itemRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_item_new", methods={"GET", "POST"})
     */
    public function new(Request $request, SluggerInterface $slugger, ItemRepository $itemRepository, EntityManagerInterface $entityManager): Response
    {
        // Crée un nouvel article en utilisant un formulaire et le persiste en base de données s'il est soumis et valide
        $item = new Item();
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itemRepository->add($item, true);

            // Gestion de l'upload d'une image associée à l'article
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                // Génère un nouveau nom de fichier et le déplace vers un répertoire spécifié
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        // chemin spécifié dans services.yaml
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    var_dump("problème lors de l'envoi du fichier");
                }
                $item->setImageFilename($newFilename);

                $entityManager->persist($item);
                $entityManager->flush();
            }

            // Redirige vers la liste des articles après la création réussie
            return $this->redirectToRoute('app_item_index', [], Response::HTTP_SEE_OTHER);
        }

        // Affiche le formulaire de création d'article dans la vue 'item/new.html.twig'
        return $this->renderForm('item/new.html.twig', [
            'item' => $item,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_item_show", methods={"GET"})
     */
    public function show(Item $item): Response
    {
        // Affiche les détails d'un article, y compris les ingrédients associés, dans la vue 'item/show.html.twig'
        $ingredients = $item->getIngredient();
        return $this->render('item/show.html.twig', [
            'ingredients' => $ingredients,
            'item' => $item,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_item_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, Item $item, ItemRepository $itemRepository): Response
    {
        // formulaire créé avec les données de l'article existant en tant que valeur initiale
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itemRepository->add($item, true);

            // Gestion de la mise à jour de l'image associée à l'article
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                // Génère un nouveau nom de fichier et le déplace vers un répertoire spécifié
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    var_dump("problème lors de l'envoi du fichier");
                }
                $item->setImageFilename($newFilename);

                // Persister l'article mis à jour en base de données
                $entityManager->persist($item);
                $entityManager->flush();
            }

            // Redirige vers la liste des articles après la mise à jour réussie
            return $this->redirectToRoute('app_item_index', [], Response::HTTP_SEE_OTHER);
        }

        // Affiche le formulaire de modification d'article dans la vue 'item/edit.html.twig'
        return $this->renderForm('item/edit.html.twig', [
            'item' => $item,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_item_delete", methods={"POST"})
     */
    public function delete(Request $request, Item $item, ItemRepository $itemRepository): Response
    {
        // Supprime un article en vérifiant un jeton CSRF valide
        if ($this->isCsrfTokenValid('delete' . $item->getId(), $request->request->get('_token'))) {
            $itemRepository->remove($item, true);
        }

        // Redirige vers la liste des articles après la suppression réussie
        return $this->redirectToRoute('app_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
