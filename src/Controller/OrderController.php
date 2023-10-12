<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\Cart\CartService;
use OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/order")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", name="order_show")
     */
    public function index(): Response
    {
        return $this->render('order/index.html.twig', [
            'controller_name' => 'OrderController',
        ]);
    }

    /**
     * @Route("/new", name="order_new")
     */
    public function new(Request $request, CartService $cartService, Security $security, OrderRepository $orderRepository): Response
    {
        $user = $security->getUser();
        $addresses = $user->getAddress();
        $wallet = $user->getWallet()->getAmount();;
        $addresses->initialize();

        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setUser($user);

        if ($form->isSubmitted() && $form->isValid()) {
            $orderRepository->add($order, true);

        }
        return $this->render('order/new.html.twig',[
            'cart' => $cartService->getFullCart(),
            'user' => $user,
            'wallet' => $wallet,
            'addresses' => $addresses
        ]);
    }

    /**
     * @Route("/all", name="order_all")
     */
    public function all(): Response
    {

        return $this->render('order/all.html.twig',[

        ]);
    }
}
