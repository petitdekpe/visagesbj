<?php

namespace App\Command;

use App\Entity\Personnalite;
use App\Repository\PersonnaliteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(
    name: 'app:personnalites:load',
    description: 'Ajoute les personnalités de src/Data/personnalites.php qui manquent encore en base (n\'écrase jamais les entrées existantes, gérées ensuite via /admin)',
)]
class LoadPersonnalitesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PersonnaliteRepository $personnaliteRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $slugger = new AsciiSlugger();

        $rows = require dirname(__DIR__).'/Data/personnalites.php';

        $seenSlugs = [];
        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $fullName = trim($row['firstName'].' '.$row['lastName']);
            $baseSlug = strtolower((string) $slugger->slug($fullName));
            $slug = $baseSlug;
            $suffix = 2;
            while (isset($seenSlugs[$slug])) {
                $slug = $baseSlug.'-'.$suffix++;
            }
            $seenSlugs[$slug] = true;

            if (null !== $this->personnaliteRepository->findOneBySlug($slug)) {
                // Already in the database — leave it alone, it may since have been
                // edited through /admin (photo uploaded, role rewritten, etc.).
                ++$skipped;
                continue;
            }

            $personnalite = (new Personnalite())
                ->setFirstName($row['firstName'])
                ->setLastName($row['lastName'])
                ->setRole($row['role'] ?? null)
                ->setPhoto($row['photo'] ?? null)
                ->setSlug($slug)
                ->setPosition($row['position']);

            $this->entityManager->persist($personnalite);
            ++$created;
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            '%d ajoutée(s), %d déjà en base (inchangée(s)).',
            $created,
            $skipped
        ));

        return Command::SUCCESS;
    }
}
