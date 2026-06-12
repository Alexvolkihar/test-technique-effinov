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
            ->addArgument('registrationId', InputArgument::OPTIONAL, 'ID of the registration request')
            ->addOption('decision', null, InputOption::VALUE_REQUIRED, 'Decision: approve or reject')
            ->addOption('reason', null, InputOption::VALUE_OPTIONAL, 'Reason for rejection');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $registrationId = $input->getArgument('registrationId');
        $decision = $input->getOption('decision');
        $reason = $input->getOption('reason');

        // Check if we need to enter interactive mode
        if (null === $registrationId) {
            $io->title('Fight Club - Portail d\'Administration (Mode Interactif)');
            
            // 1. Fetch pending requests
            $pendingRequests = $this->repository->findBy(['status' => 'pending'], ['id' => 'ASC']);
            
            if (empty($pendingRequests)) {
                $io->info('Aucune demande d\'inscription en attente.');
                return Command::SUCCESS;
            }
            
            // 2. Build choices list
            $choices = [];
            $requestMap = [];
            foreach ($pendingRequests as $req) {
                $label = sprintf('#%d - %s %s (%s)', $req->getId(), $req->getFirstName(), $req->getLastName(), $req->getEmailAddress());
                $choices[$req->getId()] = $label;
                $requestMap[$label] = $req;
            }
            
            // Allow cancelling
            $choices['cancel'] = 'Annuler l\'opération';
            
            $selectedLabel = $io->choice('Sélectionnez une demande d\'inscription à traiter :', $choices);
            
            if ('Annuler l\'opération' === $selectedLabel || 'cancel' === $selectedLabel) {
                $io->warning('Opération annulée.');
                return Command::SUCCESS;
            }
            
            $request = $requestMap[$selectedLabel] ?? $this->repository->find((int)$selectedLabel);
            
            if (null === $request) {
                $io->error('Demande d\'inscription introuvable.');
                return Command::FAILURE;
            }
            
            $registrationId = $request->getId();
            
            // 3. Ask for decision
            $decisionChoice = $io->choice(
                sprintf('Quelle décision pour %s %s ?', $request->getFirstName(), $request->getLastName()),
                ['Approuver', 'Rejeter', 'Annuler']
            );
            
            if ('Annuler' === $decisionChoice) {
                $io->warning('Opération annulée.');
                return Command::SUCCESS;
            }
            
            $decision = ('Approuver' === $decisionChoice) ? 'approve' : 'reject';
            
            // 4. Ask for reason if reject
            if ('reject' === $decision) {
                $reason = $io->ask('Quel est le motif du refus ? (Optionnel)');
            }
        } else {
            // Non-interactive mode validation
            $registrationId = (int)$registrationId;
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
