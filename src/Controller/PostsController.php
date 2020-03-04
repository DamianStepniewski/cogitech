<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends AbstractController
{
    /**
     * @Route("/lista", name="list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $posts = $this->getDoctrine()->getRepository(Post::class)->findAll();

        return $this->render('posts/index.html.twig',[
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/post/delete/{id}", name="post_delete")
     * @param Post $post
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Post $post) {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($post);
        $manager->flush();

        return $this->redirectToRoute('list');
    }
}
