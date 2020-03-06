<?php

declare(strict_types=1);

namespace Api\Command\User;

use Api\ReadModel\User\UserFetcher;
use Myks92\User\Model\User\Command\ChangeRole;
use Myks92\User\Model\User\Entity\User\Role as RoleValue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class RoleCommand extends Command
{
    /**
     * @var UserFetcher
     */
    private UserFetcher $users;
    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;
    /**
     * @var ChangeRole\Handler
     */
    private ChangeRole\Handler $handler;

    /**
     * @param UserFetcher $users
     * @param ValidatorInterface $validator
     * @param ChangeRole\Handler $handler
     */
    public function __construct(UserFetcher $users, ValidatorInterface $validator, ChangeRole\Handler $handler)
    {
        $this->users = $users;
        $this->validator = $validator;
        $this->handler = $handler;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this
            ->setName('user:role')
            ->setDescription('Changes user role');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $email = $helper->ask($input, $output, new Question('Email: '));

        if (!$user = $this->users->findByEmail($email)) {
            throw new LogicException('User is not found.');
        }

        $command = new ChangeRole\Command($user->id);

        $roles = [RoleValue::USER, RoleValue::ADMIN];
        $command->role = $helper->ask($input, $output, new ChoiceQuestion('Role: ', $roles, 0));

        $violations = $this->validator->validate($command);

        if ($violations->count()) {
            foreach ($violations as $violation) {
                $output->writeln('<error>' . $violation->getPropertyPath() . ': ' . $violation->getMessage() . '</error>');
            }
            return;
        }

        $this->handler->handle($command);

        $output->writeln('<info>Done!</info>');
    }
}
