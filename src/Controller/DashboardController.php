<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(Security $security): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $security->getUser();
        if ($user) {
            $addresses = $user->getAddress();
            $addresses->initialize();
            $phone = $user->getPhone();
        }


        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'addresses' => $addresses,
            'phone' => $phone,
        ]);
    }
}