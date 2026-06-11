<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\RegistrationRequestRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:list-registrations',
    description: 'List registration requests from the database.'
)]
class ListRegistrationRequestsCommand extends Command
{
    public function __construct(private RegistrationRequestRepository $repository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('status', InputArgument::OPTIONAL, 'Optional status filter (pending, approved, rejected)')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Maximum number of rows to display', 20);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $status = $input->getArgument('status');
        $limit = max(1, (int) $input->getOption('limit'));

        $criteria = [];
        if (is_string($status) && '' !== $status) {
            $criteria['status'] = $status;
        }

        $requests = $this->repository->findBy($criteria, ['id' => 'DESC'], $limit);

        $table = new Table($output);
        $table->setHeaders(['ID', 'Prénom', 'Nom', 'Email', 'Statut']);

        foreach ($requests as $request) {
            $table->addRow([
                $request->getId(),
                $request->getFirstName(),
                $request->getLastName(),
                $request->getEmailAddress(),
                $request->getStatus(),
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
