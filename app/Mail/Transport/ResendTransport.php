<?php

namespace App\Mail\Transport;

use Resend\Client;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class ResendTransport extends AbstractTransport
{
    public function __construct(
        protected Client $client
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        
        $payload = [
            'from' => $this->getFrom($email),
            'to' => $this->getTo($email),
            'subject' => $email->getSubject(),
        ];

        // Añadir CC si existe
        if ($cc = $this->getCc($email)) {
            $payload['cc'] = $cc;
        }

        // Añadir BCC si existe
        if ($bcc = $this->getBcc($email)) {
            $payload['bcc'] = $bcc;
        }

        // Añadir Reply-To si existe
        if ($replyTo = $this->getReplyTo($email)) {
            $payload['reply_to'] = $replyTo;
        }

        // Contenido del email
        if ($email->getHtmlBody()) {
            $payload['html'] = $email->getHtmlBody();
        }

        if ($email->getTextBody()) {
            $payload['text'] = $email->getTextBody();
        }

        // Adjuntos
        if ($attachments = $email->getAttachments()) {
            $payload['attachments'] = [];
            foreach ($attachments as $attachment) {
                $payload['attachments'][] = [
                    'content' => base64_encode($attachment->getBody()),
                    'filename' => $attachment->getFilename() ?? 'attachment',
                ];
            }
        }

        // Tags personalizados (opcional)
        if ($email->getHeaders()->has('X-Tag')) {
            $payload['tags'] = [
                ['name' => 'category', 'value' => $email->getHeaders()->get('X-Tag')->getBodyAsString()]
            ];
        }

        $this->client->emails->send($payload);
    }

    protected function getFrom($email): string
    {
        $from = $email->getFrom();
        if (empty($from)) {
            return config('mail.from.address');
        }
        
        $address = $from[0];
        if ($address->getName()) {
            return $address->getName() . ' <' . $address->getAddress() . '>';
        }
        
        return $address->getAddress();
    }

    protected function getTo($email): array
    {
        $recipients = [];
        foreach ($email->getTo() as $address) {
            if ($address->getName()) {
                $recipients[] = $address->getName() . ' <' . $address->getAddress() . '>';
            } else {
                $recipients[] = $address->getAddress();
            }
        }
        return $recipients;
    }

    protected function getCc($email): ?array
    {
        $cc = $email->getCc();
        if (empty($cc)) {
            return null;
        }
        
        $recipients = [];
        foreach ($cc as $address) {
            if ($address->getName()) {
                $recipients[] = $address->getName() . ' <' . $address->getAddress() . '>';
            } else {
                $recipients[] = $address->getAddress();
            }
        }
        return $recipients;
    }

    protected function getBcc($email): ?array
    {
        $bcc = $email->getBcc();
        if (empty($bcc)) {
            return null;
        }
        
        $recipients = [];
        foreach ($bcc as $address) {
            if ($address->getName()) {
                $recipients[] = $address->getName() . ' <' . $address->getAddress() . '>';
            } else {
                $recipients[] = $address->getAddress();
            }
        }
        return $recipients;
    }

    protected function getReplyTo($email): ?array
    {
        $replyTo = $email->getReplyTo();
        if (empty($replyTo)) {
            return null;
        }
        
        $recipients = [];
        foreach ($replyTo as $address) {
            if ($address->getName()) {
                $recipients[] = $address->getName() . ' <' . $address->getAddress() . '>';
            } else {
                $recipients[] = $address->getAddress();
            }
        }
        return $recipients;
    }

    public function __toString(): string
    {
        return 'resend';
    }
}
