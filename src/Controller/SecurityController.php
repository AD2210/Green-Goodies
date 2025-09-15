<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class SecurityController extends AbstractController
{
    /**
     * Injecte le service de vérification d'email.
     *
     * @param EmailVerifier $emailVerifier Service de gestion des emails de vérification.
     */
    public function __construct(private EmailVerifier $emailVerifier)
    {}

    #[Route(path: '/login', name: 'app_login')]
    /**
     * Affiche la page de connexion et gère l'affichage d'une éventuelle erreur d'authentification.
     *
     * @param AuthenticationUtils $authenticationUtils Utilitaire pour récupérer la dernière erreur et le dernier identifiant saisi.
     * @param Request $request Requête HTTP (peut être utilisé pour des traitements additionnels).
     * @param UserRepository $repository Référentiel utilisateur (réservé à de futurs usages ici).
     * @param ValidatorInterface $validator Service de validation (réservé à de futurs usages ici).
     *
     * @return Response Réponse HTTP affichant le formulaire de connexion.
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request, UserRepository $repository, ValidatorInterface $validator): Response
    {
        // On récupère les erreurs de login si présentes
        $error = $authenticationUtils->getLastAuthenticationError();

        // on récupère le dernier nom d'utilisateur saisi'
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginType::class);

        return $this->render('security/login.html.twig', [
            'form' => $form,
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    /**
     * Point d'entrée de déconnexion. Méthode volontairement vide,
     * l'action est gérée par le pare-feu de sécurité.
     *
     * @return void
     *
     * @throws \LogicException Toujours levée si la méthode est exécutée directement.
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register')]
    /**
     * Gère l'inscription d'un nouvel utilisateur, le hachage du mot de passe
     * et l'envoi de l'email de confirmation, puis redirige vers la liste des produits.
     *
     * @param Request $request Requête HTTP contenant les données du formulaire d'inscription.
     * @param UserPasswordHasherInterface $userPasswordHasher Service de hachage du mot de passe utilisateur.
     * @param Security $security Service de sécurité (réservé à de futurs usages post-inscription).
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités pour persister le nouvel utilisateur.
     *
     * @return Response Réponse HTTP affichant le formulaire ou redirigeant après succès.
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        //On crée le user et le formulaire
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // on encode le password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // On génère le mail de confirmation
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@green-googies.test', 'Green Googies'))
                    ->to((string) $user->getEmail())
                    ->subject('Veuillez confirmer votre adresse e-mail')
                    ->htmlTemplate('security/confirmation_email.html.twig')
            );

            return $this->redirectToRoute('app_products');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    /**
     * Valide l'adresse email de l'utilisateur via le lien envoyé, connecte l'utilisateur
     * et redirige vers la page de compte. En cas d'échec, redirige vers l'inscription.
     *
     * @param Request $request Requête contenant les paramètres de vérification.
     * @param TranslatorInterface $translator Service de traduction pour les messages d'erreur du bundle de vérification.
     * @param UserRepository $userRepository Référentiel utilisateur pour retrouver l'utilisateur par son identifiant.
     * @param Security $security Service de sécurité pour connecter l'utilisateur après vérification.
     *
     * @return Response Réponse HTTP de redirection selon le résultat.
     */
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository,Security $security): Response
    {
        // on récupère l'id du user dans l'url de confirmation
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // Validation de l'email, et enregistrement en BDD du bool isVerified
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre email a été vérifié');

        $security->login($user, 'form_login', 'main');
        return $this->redirectToRoute('app_account');
    }

    #[Route('/verify/email/resend', name: 'app_verify_email_resend')]
    /**
     * Récupère l'adresse email soumise et renvoie, si applicable, un nouvel email de vérification.
     * Redirige l'utilisateur vers la connexion avec un message de statut.
     *
     * @param Request $request Requête contenant le token CSRF et l'email soumis.
     * @param UserRepository $repository Référentiel pour retrouver l'utilisateur par email.
     *
     * @return Response Réponse HTTP de redirection vers la connexion.
     */
    public function resendVerificationEmail(Request $request, UserRepository $repository): Response
    {
        // Contrôle du token CSRF du formulaire de renvoi
        if (!$this->isCsrfTokenValid('resend_confirmation', $request->request->get('_token'))) {
            $this->addFlash('error', 'Requête invalide.');
            return $this->redirectToRoute('app_login');
        }

        $email = (string) $request->request->get('email', '');
        if ($email !== '') {
            $user = $repository->findOneBy(['email' => $email]);

            // on renvoie le mail de confirmation si l'utilisateur à tenter de se connecter sans avoir validé son email et cliquer sur le bouton
            if ($user && !$user->isVerified()) {
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('no-reply@green-googies.test', 'Green Googies'))
                        ->to((string) $user->getEmail())
                        ->subject('Veuillez confirmer votre adresse e‑mail')
                        ->htmlTemplate('security/confirmation_email.html.twig')
                );

                $this->addFlash('success', 'Si un compte correspond, un nouvel email de confirmation a été envoyé.');
                return $this->redirectToRoute('app_login');
            }
        }

        // Si l'email n'est pas valide, on redirige vers la page de connexion
        $this->addFlash('error', 'votre adresse mail n\'est pas valide');
        return $this->redirectToRoute('app_login');
    }
}
