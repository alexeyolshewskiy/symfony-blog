<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends EasyAdminController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    public function persistUserEntity(User $entity){
        if(!empty($entity->getPlainPassword())){
            $entity->setPassword(
                $this->passwordEncoder->encodePassword(
                    $entity,
                    $entity->getPlainPassword()
                )
            );
        }
        parent::persistEntity($entity);
    }

    public function updateUserEntity(User $entity)
    {
        if(!empty($entity->getPlainPassword())){
            $entity->setPassword(
                $this->passwordEncoder->encodePassword(
                    $entity,
                    $entity->getPlainPassword()
                )
            );
        }
        parent::updateEntity($entity);
    }

    public function createPostEntityFormBuilder($entity, $view)
    {
        $formBuilder = parent::createEntityFormBuilder($entity, $view);

        $users = $this->getDoctrine()->getRepository(User::class)->findAllWithEmailKey();

        $formBuilder->add('author', ChoiceType::class, [
            'choices'  => $users
        ]);

        return $formBuilder;
    }

    public function createCommentEntityFormBuilder($entity, $view)
    {
        $formBuilder = parent::createEntityFormBuilder($entity, $view);

        $users = $this->getDoctrine()->getRepository(User::class)->findAllWithEmailKey();

        $formBuilder->add('author', ChoiceType::class, [
            'choices'  => $users
        ]);

        $posts = $this->getDoctrine()->getRepository(Post::class)->findAllWithTitleKey();

        $formBuilder->add('post', ChoiceType::class, [
            'choices'  => $posts
        ]);

        return $formBuilder;
    }
}
