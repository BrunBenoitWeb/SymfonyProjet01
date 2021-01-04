<?php


namespace App\Notification;


use App\Entity\Contact;
use Twig\Environment;


class ContactNotification
{


    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $Renderer;

    public function __construct(\Swift_Mailer $mailer, Environment $Renderer){

        $this->mailer = $mailer;
        $this->Renderer = $Renderer;
    }

    /**
     * @param Contact $contact
     */

    public function notify(Contact $contact){
        $message = (new \Swift_Message('Agence :' . $contact->getProperty()->getTitle()))
            ->setFrom('noreply@server.com')
            ->setTo('conatct@agence.fr')
            ->setReplyTo($contact->getEmail())
            ->setBody($this->Renderer->render('emails/contact.html.twig', [
                'contact' => $contact
            ]), 'text/html');
        $this->mailer->send($message);
    }
}