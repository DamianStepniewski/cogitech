<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends AbstractController
{
    /**
     * @Route("/lista", name="list")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('posts/index.html.twig');
    }
}
