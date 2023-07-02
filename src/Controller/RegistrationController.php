<?php

namespace App\Controller;

use App\Entity\AppUser;
use App\Repository\AppUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\BadUrlException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $appUserRepository;

    public function __construct(EntityManagerInterface $entityManager, AppUserRepository $appUserRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->appUserRepository = $appUserRepository;
    }
    //#[Route('/registration', name: 'app_registration', methods: ['POST'])]
    /**
     * @Route("/registration", name="app_registration", methods={"POST"})
     */
    public function index(MailerInterface $mailer, Request $request): Response
    {
        if ($registerUserEmail = $request->request->get('email')) {

            if (count($this->appUserRepository->findBy(['email' => $registerUserEmail])) === 0) {

                $user = new AppUser();
                $user->setUsername($request->request->get('username'));
                $user->setEmail($request->request->get('email'));
                $user->setPassword($this->passwordHasher->hashPassword(
                    $user,
                    $request->request->get('username')
                ));

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $email = (new TemplatedEmail())
                    ->from('registration@ufo.com')
                    ->to($user->getEmail())
                    ->subject('Registration confirm!!!')
                    ->htmlTemplate('email/register.html.twig')
                    ->context([
                        'user' => $user,
                        'title' => 'Name Application'
                    ]);

                $mailer->send($email);

                return $this->render('registration/thx.html.twig', [
                    'controller_name' => 'RegistrationController',
                ]);
            }
        }

        return $this->render('registration/index.html.twig', [
            'controller_name' => 'RegistrationController',
        ]);
    }

    //#[Route('/registration-confirm/user/{username}/hash/{hash}', name: 'app_registration_confirm')]
    /**
     * @Route("/registration-confirm/user/{username}/hash/{hash}", name="app_registration_confirm")
     */
    public function confirm($username, $hash): Response
    {
        $userConfirm = $this->appUserRepository->findOneBy(['username' => $username, 'hashConfirm' => $hash]);

        if (!$userConfirm) {
            throw new BadRequestHttpException('Nieprawidłowe dane');
        }
        $userConfirm->setStatus('active');
        $userConfirm->setHashConfirm(null);
        $this->entityManager->flush();

        return $this->render('registration/confirm.html.twig', [
            'controller_name' => 'RegistrationController',
        ]);
    }
}
