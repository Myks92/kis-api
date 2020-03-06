<?php

declare(strict_types=1);

namespace Api\Container\User\Service;

use Myks92\User\Model\User\Entity\User\Email;
use Myks92\User\Model\User\Entity\User\Token;
use Myks92\User\Model\User\Service\JoinConfirmTokenSenderInterface;
use RuntimeException;
use Swift_Mailer;
use Swift_Message;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class JoinConfirmTokenSender implements JoinConfirmTokenSenderInterface
{
    /**
     * @var Swift_Mailer
     */
    private Swift_Mailer $mailer;
    /**
     * @var Environment
     */
    private Environment $twig;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @param Swift_Mailer $mailer
     * @param Environment $twig
     * @param TranslatorInterface $translator
     */
    public function __construct(Swift_Mailer $mailer, Environment $twig, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    /**
     * @param Email $email
     * @param Token $token
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function send(Email $email, Token $token): void
    {
        $message = (new Swift_Message($this->translator->trans('Join Confirmation', [], 'mail')))
            ->setTo($email->getValue())
            ->setBody($this->twig->render('mail/user/join.html.twig', ['token' => $token->getValue()]), 'text/html');

        if (!$this->mailer->send($message)) {
            throw new RuntimeException($this->translator->trans('Unable to send message.', [], 'error'));
        }
    }
}
