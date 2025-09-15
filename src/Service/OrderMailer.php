<?php

namespace App\Service;

use App\Entity\Order;
use Exception;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;


/**
 * Service responsable de l'envoi des emails liés aux commandes.
 */
class OrderMailer
{
    /**
     * Constructeur du service OrderMailer.
     *
     * @param MailerInterface $mailer Service d'envoi d'emails
     * @param string $fromAddress Adresse email d'expédition par défaut
     * @param string $fromName Nom d'expéditeur par défaut
     */
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromAddress = 'no-reply@green-googies.test',
        private readonly string $fromName = 'Green Googies'
    ) {}

    /**
     * Envoie un email de confirmation de commande au client.
     *
     * @param Order $order La commande à confirmer
     * @throws Exception
     */
    public function sendOrderConfirmation(Order $order): void
    {
        $owner = $order->getOwner();
        if (!$owner || !$owner->getEmail()) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromAddress, $this->fromName))
            ->to(new Address($owner->getEmail()))
            ->subject(sprintf('Confirmation de commande #%d', $order->getId() ?? 0))
            ->htmlTemplate('order/OrderConfirmationEmail.twig')
            ->context([
                'order' => $order,
                'customer' => $owner,
                'items' => $order->getOrderItems(),
                'total' => $order->getTotal(),
            ]);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new Exception('Impossible d\'envoyer l\'email de confirmation');
        }
    }
}
