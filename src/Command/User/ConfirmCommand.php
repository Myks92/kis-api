<?php

declare(strict_types=1);

namespace Api\Command\User;

use Api\ReadModel\User\UserFetcher;
use Exception;
use Myks92\User\Model\User\Command\JoinByEmail\Confirm;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class ConfirmCommand extends Command
{
    /**
     * @var UserFetcher
     */
    private UserFetcher $users;
    /**
     * @var Confirm\Handler
     */
    private Confirm\Handler $handler;

    /**
     * @param UserFetcher $users
     * @param Confirm\Handler $handler
     */
    public function __construct(UserFetcher $users, Confirm\Handler $handler)
    {
        $this->users = $users;
        $this->handler = $handler;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('user:confirm')
            ->setDescription('Confirms signed up user');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $email = $helper->ask($input, $output, new Question('Email: '));

        if (!$user = $this->users->findByEmail($email)) {
            throw new LogicException('User is not found.');
        }

        $command = new Confirm\Command($user->id);
        $this->handler->handle($command);

        $output->writeln('<info>Done!</info>');
    }
}
