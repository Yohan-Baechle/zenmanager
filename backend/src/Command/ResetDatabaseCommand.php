<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reset-db',
    description: 'Drop the database, run migrations, and load fixtures.'
)]
class ResetDatabaseCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🔄 Resetting the database');

        $io->section('Dropping the database…');
        $this->runSubCommand($io, 'doctrine:database:drop', [
            '--force' => true,
            '--if-exists' => true,
        ]);

        $io->section('Creating the database…');
        $this->runSubCommand($io, 'doctrine:database:create', [
            '--if-not-exists' => true,
        ]);

        $io->section('Running migrations…');
        $this->runSubCommand($io, 'doctrine:migrations:migrate', [
            '--no-interaction' => true,
        ]);

        $io->section('Loading fixtures…');
        $this->runSubCommand($io, 'doctrine:fixtures:load', [
            '--no-interaction' => true,
        ]);

        $io->success('✅ Database has been successfully reset, migrated, and seeded!');

        return Command::SUCCESS;
    }

    /**
     * Helper method to run another Symfony console command programmatically.
     *
     * @param array<string, mixed> $args
     */
    private function runSubCommand(SymfonyStyle $io, string $name, array $args = []): void
    {
        $app = $this->getApplication();
        $command = $app->find($name);

        $input = new ArrayInput(array_merge(['command' => $name], $args));
        $input->setInteractive(false);

        $exitCode = $command->run($input, $io);
        if (Command::SUCCESS !== $exitCode) {
            throw new \RuntimeException(sprintf('The sub-command "%s" failed (exit code %d).', $name, $exitCode));
        }
    }
}
