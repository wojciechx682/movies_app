<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an administrator account',
)]
class CreateAdminCommand
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
    ) {
    }

    public function __invoke(
        InputInterface $input,
        SymfonyStyle $io,
    ): int {
        if (!$input->isInteractive()) {
            $io->error('Run this command in an interactive terminal');

            return Command::FAILURE;
        }

        $email = trim((string) $io->ask('Email address'));

        $emailViolations = $this->validator->validate($email, [
            new NotBlank(message: 'Enter your email address'),
            new Email(message: 'Enter a valid email address'),
            new Length(
                max: 180,
                maxMessage: 'The email address cannot be longer than {{ limit }} characters',
            ),
        ]);

        if (count($emailViolations) > 0) {
            $io->error($emailViolations[0]->getMessage());

            return Command::FAILURE;
        }

        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            $io->error('An account with this email already exists');

            return Command::FAILURE;
        }

        $plainPassword = $io->askHidden('Password');
        $passwordConfirmation = $io->askHidden('Repeat password');

        if (!is_string($plainPassword) || !is_string($passwordConfirmation)) {
            $io->error('The password could not be read');

            return Command::FAILURE;
        }

        $passwordViolations = $this->validator->validate($plainPassword, [
            new NotBlank(message: 'Please enter a password'),
            new Length(
                min: 6,
                minMessage: 'Your password should be at least {{ limit }} characters',
                max: 4096,
            ),
        ]);

        if (count($passwordViolations) > 0) {
            $io->error($passwordViolations[0]->getMessage());

            return Command::FAILURE;
        }

        if ($plainPassword !== $passwordConfirmation) {
            $io->error('The passwords do not match');

            return Command::FAILURE;
        }

        if (!$io->confirm(sprintf('Create an administrator account for %s?', $email), false)) {
            $io->note('Administrator creation cancelled');

            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            $io->error('An account with this email already exists');

            return Command::FAILURE;
        }

        $io->success(sprintf('Administrator account created for %s', $email));

        return Command::SUCCESS;
    }
}
