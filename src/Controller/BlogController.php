<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentFormType;
use App\Form\PostFormType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends AbstractController
{
    public function index(Request $request, AuthorizationCheckerInterface $authChecker, PaginatorInterface $paginator)
    {

        $responseData = [];

        if($authChecker->isGranted('ROLE_USER')){

            $post = new Post();
            $postForm = $this->createForm(PostFormType::class, $post);
            $postForm->handleRequest($request);
            if ($postForm->isSubmitted() && $postForm->isValid()) {

                $post->setAuthor($this->getUser());
                $post->setTitle($postForm->get('title')->getData());
                $post->setContent($postForm->get('content')->getData());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($post);
                $entityManager->flush();
                $postForm = $this->createForm(PostFormType::class, new Post());

            }
            $responseData['postForm'] = $postForm->createView();

        }

        $em = $this->getDoctrine()->getManager();
        $postsRepository = $em->getRepository(Post::class);
        $allPostsQuery = $postsRepository->createQueryBuilder('p')->orderBy('p.updated','DESC')->getQuery();

        $posts = $paginator->paginate(
            $allPostsQuery,
            $request->query->getInt('page', 1),
            5
        );

        $responseData['posts'] = $posts;

        return $this->render('blog/index.html.twig', $responseData );

    }

    public function post(Request $request, AuthorizationCheckerInterface $authChecker, PaginatorInterface $paginator){

        $post = $this->getDoctrine()
            ->getRepository(Post::class)
            ->find( $request->attributes->getInt('id') );

        if (!$post) {
            throw $this->createNotFoundException('The post does not exist');
        }

        $responseData['post'] = $post;

        if($authChecker->isGranted('ROLE_USER')){
            $comment = new Comment();
            $commentForm = $this->createForm(CommentFormType::class, $comment);
            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {

                $comment->setPost($post);
                $comment->setAuthor($this->getUser());
                $comment->setContent($commentForm->get('content')->getData());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($comment);
                $entityManager->flush();
                $commentForm = $this->createForm(CommentFormType::class, new Comment());

            }
            $responseData['commentForm'] = $commentForm->createView();
        }

        $em = $this->getDoctrine()->getManager();
        $commentsRepository = $em->getRepository(Comment::class);
        $commentCommentQuery = $commentsRepository->createQueryBuilder('c')
            ->where('c.post = :post')
            ->setParameter('post', $post )
            ->orderBy('c.updated','DESC')
            ->getQuery();

        $comments = $paginator->paginate(
            $commentCommentQuery,
            $request->query->getInt('page', 1),
            5
        );

        $responseData['comments'] = $comments;

        return $this->render('blog/post.html.twig', $responseData );
    }

}
