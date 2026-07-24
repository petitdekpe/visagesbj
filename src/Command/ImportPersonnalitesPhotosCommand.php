<?php

namespace App\Command;

use App\Repository\PersonnaliteRepository;
use App\Service\PersonnalitePhotoUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(
    name: 'app:personnalites:photos:import',
    description: 'Importe en masse des photos depuis var/photos-import/ — chaque fichier doit être nommé "{slug}.jpg" (ou .png/.webp)',
)]
class ImportPersonnalitesPhotosCommand extends Command
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PersonnaliteRepository $personnaliteRepository,
        private readonly PersonnalitePhotoUploader $photoUploader,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Affiche ce qui serait fait, sans rien modifier')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $importDir = $this->projectDir.'/var/photos-import';

        if (!is_dir($importDir)) {
            mkdir($importDir, 0775, true);
            $io->warning(sprintf(
                "Le dossier %s n'existait pas, il vient d'être créé.\nDépose-y des photos nommées \"{slug}.jpg\" (voir la colonne Slug dans /admin/personnalites) puis relance cette commande.",
                $importDir
            ));

            return Command::SUCCESS;
        }

        $finder = new Finder();
        $finder->files()->in($importDir)->depth(0);

        if (!$finder->hasResults()) {
            $io->warning(sprintf('Aucun fichier trouvé dans %s.', $importDir));

            return Command::SUCCESS;
        }

        $imported = [];
        $unmatched = [];
        $skippedExtension = [];

        foreach ($finder as $file) {
            $extension = strtolower($file->getExtension());
            $slug = strtolower($file->getFilenameWithoutExtension());

            if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                $skippedExtension[] = $file->getFilename();
                continue;
            }

            $personnalite = $this->personnaliteRepository->findOneBySlug($slug);

            if (null === $personnalite) {
                $unmatched[] = $file->getFilename();
                continue;
            }

            $imported[] = sprintf('%s  →  %s', $file->getFilename(), $personnalite->getFullName());

            if (!$dryRun) {
                $oldPhoto = $personnalite->getPhoto();
                $newFilename = $this->photoUploader->upload(new File($file->getPathname()), $personnalite->getFullName());
                $this->photoUploader->remove($oldPhoto);
                $personnalite->setPhoto($newFilename);
            }
        }

        if (!$dryRun && count($imported) > 0) {
            $this->entityManager->flush();
        }

        if ($dryRun) {
            $io->note('Mode dry-run : rien n\'a été modifié.');
        }

        if ($imported) {
            $io->success(sprintf('%d photo(s) importée(s) :', count($imported)));
            $io->listing($imported);
        }

        if ($unmatched) {
            $io->warning('Fichiers ignorés (aucun slug correspondant) :');
            $io->listing($unmatched);
        }

        if ($skippedExtension) {
            $io->warning('Fichiers ignorés (extension non supportée, attendu jpg/jpeg/png/webp) :');
            $io->listing($skippedExtension);
        }

        $missingCount = $this->personnaliteRepository->countWithoutPhoto();
        if ($missingCount > 0) {
            $io->note(sprintf('%d personnalité(s) sans photo — voir /admin/personnalites pour la liste des slugs attendus.', $missingCount));
        } else {
            $io->success('Toutes les personnalités ont une photo.');
        }

        return Command::SUCCESS;
    }
}
