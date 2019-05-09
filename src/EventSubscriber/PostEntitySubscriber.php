<?php
/**
 * Created by PhpStorm.
 * User: alexey
 * Date: 07.05.19
 * Time: 21:33
 */

namespace App\EventSubscriber;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PostEntitySubscriber implements EventSubscriber,ContainerAwareInterface
{
    use ContainerAwareTrait;

    private $mailer;
    private $mailFrom;

    public function __construct(\Swift_Mailer $mailer, ContainerInterface $container)
    {
        $this->setContainer($container);
        $this->mailer = $mailer;

        if($this->container->hasParameter('mailer.from')){
            $this->mailFrom = $this->container->getParameter('mailer.from');
        }

    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->resolver($args);
    }

    public function resolver(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Post) {
            $this->sendNewPostEmail($entity);
        }

        if ($entity instanceof Comment){
            $this->sendNewCommentEmail($entity);
        }

    }

    public function sendNewPostEmail(Post $post)
    {
        $commentators = $this->container->get('doctrine')->getRepository(User::class)->findAllCommentators();
        foreach ($commentators as $user) {
            $message = (new \Swift_Message('New Post Added'))
                ->setFrom($this->mailFrom )
                ->setTo( $user->getEmail() )
                ->setBody(
                    $this->container->get('twig')->render(
                        'emails/new_post.html.twig',
                        array(
                            'title' => $post->getTitle(),
                            'name' => $user->getUsername(),
                            'id' => $post->getId()
                        )
                    ),
                    'text/html'
                );
            $this->mailer->send($message);
        }
    }

    public function sendNewCommentEmail(Comment $comment){
        $post = $comment->getPost();
        $author = $post->getAuthor();
        $message = (new \Swift_Message('New Comment Added'))
            ->setFrom($this->mailFrom )
            ->setTo($author->getEmail())
            ->setBody(
                $this->container->get('twig')->render(
                    'emails/new_comment.html.twig',
                    array(
                        'title' => $post->getTitle(),
                        'name' => $author->getUsername(),
                        'id' => $post->getId()
                    )
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }
}