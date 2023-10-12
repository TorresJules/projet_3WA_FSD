<?php

namespace App\Controller;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(ItemRepository $itemRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'items' => $itemRepository->findAll(),
        ]);
    }
}
