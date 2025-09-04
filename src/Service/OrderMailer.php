<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class OrderMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromAddress = 'no-reply@green-googies.test',
        private readonly string $fromName = 'Green Googies'
    ) {}

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

        $this->mailer->send($email);
    }
}
