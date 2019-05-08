<?php

namespace App\Controller;

use App\Form\LoginFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $form = $this->createForm(LoginFormType::class);
        $error = $authenticationUtils->getLastAuthenticationError();
        if($error){
            $formError = new FormError($error->getMessageKey());
            $form->addError($formError);
        }
        $lastUsername = $authenticationUtils->getLastUsername();
        $form->setData(['email' => $lastUsername]);
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'loginForm' => $form->createView() ]);
    }

    public function logout(){

        return $this->redirectToRoute('index');
    }

}
