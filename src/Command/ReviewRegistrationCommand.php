<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\RegistrationRequestRepository;
use App\Service\RegistrationReviewService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:review-registration',
    description: 'Review (approve or reject) a pending registration request.'
)]
class ReviewRegistrationCommand extends Command
{
    public function __construct(
        private RegistrationRequestRepository $repository,
        private RegistrationReviewService $reviewService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('registrationId', InputArgument::REQUIRED, 'ID of the registration request')
            ->addOption('decision', null, InputOption::VALUE_REQUIRED, 'Decision: approve or reject')
            ->addOption('reason', null, InputOption::VALUE_OPTIONAL, 'Reason for rejection');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $registrationId = (int)$input->getArgument('registrationId');
        $decision = $input->getOption('decision');
        $reason = $input->getOption('reason');

        if (!in_array($decision, ['approve', 'reject'], true)) {
            $io->error('Decision must be "approve" or "reject".');
            return Command::INVALID;
        }

        $request = $this->repository->find($registrationId);
        if (null === $request) {
            $io->error(sprintf('Registration request #%d not found.', $registrationId));
            return Command::FAILURE;
        }

        if ('pending' !== $request->getStatus()) {
            $io->error(sprintf('Registration request #%d is already "%s".', $registrationId, $request->getStatus()));
            return Command::FAILURE;
        }

        try {
            if ('approve' === $decision) {
                $member = $this->reviewService->approve($request);
                $io->success(sprintf('Candidature approuvée avec succès. Compte membre %s créé.', $member->getDisplayNumber()));
            } else {
                $this->reviewService->reject($request, $reason);
                $io->success('Candidature rejetée.');
            }
        } catch (\Exception $e) {
            $io->error('An error occurred during review: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
