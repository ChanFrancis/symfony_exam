<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('index.html.twig');  // Make sure you have index.html.twig in the templates folder
    }

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        return $this->render('profile/profile.html.twig');  // Make sure you have index.html.twig in the templates folder
    }

    #[Route('/admin', name: 'admin')]
    public function admin(): Response
    {
        return $this->render('admin/admin.html.twig');  // Make sure you have index.html.twig in the templates folder
    }

}
