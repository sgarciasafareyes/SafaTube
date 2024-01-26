<?php

namespace App\Controller;

use App\Entity\Canal;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/api/registro')]
class RegistroController extends AbstractController
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    #[Route('', name: 'registrar_usuario', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Crear un nuevo usuario
        $user = new Usuario();
        $user->setUsername($data['username']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        // Guardar el usuario en la base de datos
        $entityManager->persist($user);
        $entityManager->flush();

        // Crear un nuevo canal asociado al usuario
        $canal = new Canal();
        $canal->setEmail($data['email']); // Asegúrate de que el campo de correo electrónico esté presente en los datos
        $canal->setUsuario($user); // Asociar el canal al usuario

        // Guardar el canal en la base de datos
        $entityManager->persist($canal);
        $entityManager->flush();

        // Generar el token de verificación y asociarlo al usuario
        $user->generateVerificationToken();

        // Guardar el usuario con el token de verificación actualizado
        $entityManager->persist($user);
        $entityManager->flush();

        // Enviar correo de verificación
        $this->sendVerificationEmail($user);

        // Devolver una respuesta JSON exitosa
        return new JsonResponse(['message' => 'Usuario registrado con éxito'], 201);
    }

    private function sendVerificationEmail(Usuario $user): void
    {
        $email = (new Email())
            ->from('noreply@example.com')
            ->to($user->getCanal()->getEmail())
            ->subject('Verificación de Correo Electrónico')
            ->html(
                $this->renderView(
                    'emails/verification.html.twig',
                    ['token' => $user->getVerificationToken()]
                )
            );

        $this->mailer->send($email);
    }
}