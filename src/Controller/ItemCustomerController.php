<?php

namespace App\Controller;

use App\Entity\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemCustomerController extends AbstractController
{
    /**
     * @Route("/item/{id}", name="app_item_customer")
     */
    public function show(Item $item): Response
    {
        $ingredients = $item->getIngredient();
        return $this->render('item_customer/index.html.twig', [
            'ingredients' => $ingredients,
            'item' => $item,
        ]);
    }
}
