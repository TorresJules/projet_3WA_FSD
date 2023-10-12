<?php

namespace App\Service\Cart;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService {

    protected $session;
    protected $entityManager;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    public function add(int $id)
    {
        // Récupère le panier actuel depuis la session ou un tableau vide s'il est vide
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id])) {
            // Si l'article existe déjà dans le panier, augmente la quantité de 1
            $panier[$id]++;
        } else {
            // Si l'article n'est pas encore dans le panier, l'ajoute avec une quantité de 1
            $panier[$id] = 1;
        }

        // Enregistre le panier mis à jour dans la session
        $this->session->set('panier', $panier);
    }

    public function remove(int $id)
    {
        // Récupère le panier actuel depuis la session ou un tableau vide s'il est vide
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id])) {
            // Si l'article existe dans le panier, le supprime
            unset($panier[$id]);
        }

        // Enregistre le panier mis à jour dans la session
        $this->session->set('panier', $panier);
    }

    public function getFullCart(): array
    {
        // Récupère le panier actuel depuis la session ou un tableau vide s'il est vide
        $panier = $this->session->get('panier', []);

        $panierWithData = [];
        foreach ($panier as $id => $quantity) {
            // Pour chaque article dans le panier, récupère l'article lui-même depuis la base de données
            $panierWithData[] = [
                'product' => $this->entityManager->getRepository(Item::class)->find($id),
                'quantity' => $quantity
            ];
        }

        // Retourne un tableau contenant des informations sur les articles dans le panier
        return $panierWithData;
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getFullCart() as $item) {
            // Calcule le total en additionnant les prix de chaque article multiplié par sa quantité
            $total += $item['product']->getPrice() * $item['quantity'];
        }

        // Retourne le total du panier
        return $total;
    }
}
