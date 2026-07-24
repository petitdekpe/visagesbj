<?php

namespace App\Command;

use App\Entity\Personnalite;
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
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsCommand(
    name: 'app:personnalites:photos:import',
    description: 'Importe en masse des photos depuis var/photos-import/ — match exact par slug, puis par ressemblance de nom (--threshold, 70% par défaut)',
)]
class ImportPersonnalitesPhotosCommand extends Command
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

    // Preferred over one another when the same personality has several candidate files.
    private const EXTENSION_PREFERENCE = ['jpg' => 0, 'jpeg' => 0, 'png' => 1, 'webp' => 1, 'avif' => 2];

    private AsciiSlugger $slugger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PersonnaliteRepository $personnaliteRepository,
        private readonly PersonnalitePhotoUploader $photoUploader,
        private readonly string $projectDir,
    ) {
        parent::__construct();
        $this->slugger = new AsciiSlugger();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Affiche ce qui serait fait, sans rien modifier')
            ->addOption('threshold', null, InputOption::VALUE_REQUIRED, 'Score minimum (0-100) pour accepter un match approximatif', 70)
            ->addOption('dir', null, InputOption::VALUE_REQUIRED, 'Dossier à scanner (par défaut var/photos-import/)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $threshold = (float) $input->getOption('threshold');
        $importDir = $input->getOption('dir') ?: $this->projectDir.'/var/photos-import';

        if (!is_dir($importDir)) {
            if ($input->getOption('dir')) {
                $io->error(sprintf('Dossier introuvable : %s', $importDir));

                return Command::FAILURE;
            }
            mkdir($importDir, 0775, true);
            $io->warning(sprintf(
                "Le dossier %s n'existait pas, il vient d'être créé.\nDépose-y des photos (idéalement nommées \"{slug}.jpg\", voir la colonne Slug dans /admin/personnalites) puis relance cette commande.",
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

        $people = $this->personnaliteRepository->findAllOrdered();

        $skippedExtension = [];
        $exactMatches = [];
        $fuzzyCandidates = []; // personality id => [ ['file' => SplFileInfo, 'score' => float], ... ]
        $peopleById = [];

        foreach ($people as $p) {
            $peopleById[$p->getId()] = $p;
        }

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $extension = strtolower($file->getExtension());

            if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                $skippedExtension[] = $file->getFilename();
                continue;
            }

            $slug = strtolower($file->getFilenameWithoutExtension());
            $exact = $this->personnaliteRepository->findOneBySlug($slug);

            if (null !== $exact) {
                $exactMatches[] = ['file' => $file, 'personnalite' => $exact];
                continue;
            }

            [$bestPerson, $bestScore] = $this->findBestFuzzyMatch($file, $people);

            if (null !== $bestPerson && $bestScore >= $threshold) {
                $fuzzyCandidates[$bestPerson->getId()][] = ['file' => $file, 'score' => $bestScore];
            }
        }

        // Resolve duplicates: keep the best candidate per personality (score desc,
        // then "nicer" extension, then smaller file — see resolveDuplicate()).
        $fuzzyWinners = [];
        $fuzzySkippedDuplicates = [];
        foreach ($fuzzyCandidates as $personnaliteId => $candidates) {
            if (count($candidates) > 1) {
                usort($candidates, fn ($a, $b) => $this->compareCandidates($a, $b));
            }
            $fuzzyWinners[] = ['personnalite' => $peopleById[$personnaliteId], 'file' => $candidates[0]['file'], 'score' => $candidates[0]['score']];
            foreach (array_slice($candidates, 1) as $loser) {
                $fuzzySkippedDuplicates[] = sprintf('%s (score %.1f, doublon de %s)', $loser['file']->getFilename(), $loser['score'], $candidates[0]['file']->getFilename());
            }
        }

        $appliedExact = [];
        $skippedHasPhotoExact = [];
        foreach ($exactMatches as $m) {
            $appliedExact[] = sprintf('%s  →  %s (slug exact)', $m['file']->getFilename(), $m['personnalite']->getFullName());
            if (!$dryRun) {
                $this->applyPhoto($m['personnalite'], $m['file']);
            }
        }

        $appliedFuzzy = [];
        $skippedHasPhotoFuzzy = [];
        foreach ($fuzzyWinners as $w) {
            if (null !== $w['personnalite']->getPhoto()) {
                $skippedHasPhotoFuzzy[] = sprintf('%s  →  %s (score %.1f%%, a déjà une photo)', $w['file']->getFilename(), $w['personnalite']->getFullName(), $w['score']);
                continue;
            }
            $appliedFuzzy[] = sprintf('%s  →  %s (score %.1f%%)', $w['file']->getFilename(), $w['personnalite']->getFullName(), $w['score']);
            if (!$dryRun) {
                $this->applyPhoto($w['personnalite'], $w['file']);
            }
        }

        $matchedFiles = array_map(fn ($m) => $m['file']->getFilename(), $exactMatches);
        foreach ($fuzzyCandidates as $candidates) {
            foreach ($candidates as $c) {
                $matchedFiles[] = $c['file']->getFilename();
            }
        }
        $unmatched = [];
        foreach ($finder as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, self::ALLOWED_EXTENSIONS, true) && !in_array($file->getFilename(), $matchedFiles, true)) {
                $unmatched[] = $file->getFilename();
            }
        }

        if (!$dryRun && (count($appliedExact) > 0 || count($appliedFuzzy) > 0)) {
            $this->entityManager->flush();
        }

        if ($dryRun) {
            $io->note('Mode dry-run : rien n\'a été modifié.');
        }

        if ($appliedExact) {
            $io->success(sprintf('%d photo(s) associée(s) par slug exact :', count($appliedExact)));
            $io->listing($appliedExact);
        }

        if ($appliedFuzzy) {
            $io->success(sprintf('%d photo(s) associée(s) par ressemblance de nom (seuil %.0f%%) :', count($appliedFuzzy), $threshold));
            $io->listing($appliedFuzzy);
        }

        if ($skippedHasPhotoFuzzy) {
            $io->warning('Ignorés — la personnalité a déjà une photo (ne pas écraser silencieusement) :');
            $io->listing($skippedHasPhotoFuzzy);
        }

        if ($fuzzySkippedDuplicates) {
            $io->note('Doublons détectés — le meilleur candidat a été gardé, les autres ignorés :');
            $io->listing($fuzzySkippedDuplicates);
        }

        if ($unmatched) {
            $io->warning(sprintf('Aucune correspondance à %.0f%% ou plus (probablement pas dans la liste des 66/72 visages) :', $threshold));
            $io->listing($unmatched);
        }

        if ($skippedExtension) {
            $io->warning('Extension non supportée (jpg/jpeg/png/webp/avif attendus) :');
            $io->listing($skippedExtension);
        }

        $missingCount = $this->personnaliteRepository->countWithoutPhoto();
        if ($missingCount > 0) {
            $io->note(sprintf('%d personnalité(s) encore sans photo.', $missingCount));
        } else {
            $io->success('Toutes les personnalités ont une photo.');
        }

        return Command::SUCCESS;
    }

    private function applyPhoto(Personnalite $personnalite, SplFileInfo $file): void
    {
        $oldPhoto = $personnalite->getPhoto();
        $newFilename = $this->photoUploader->upload(new File($file->getPathname()), $personnalite->getFullName());
        $this->photoUploader->remove($oldPhoto);
        $personnalite->setPhoto($newFilename);
    }

    /**
     * @param Personnalite[] $people
     *
     * @return array{0: ?Personnalite, 1: float}
     */
    private function findBestFuzzyMatch(SplFileInfo $file, array $people): array
    {
        $fileNorm = $this->normalize($file->getFilenameWithoutExtension());

        $best = null;
        $bestScore = -1.0;
        foreach ($people as $p) {
            $score = $this->bestScoreForPerson($fileNorm, $p);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $p;
            }
        }

        return [$best, max(0.0, $bestScore)];
    }

    private function bestScoreForPerson(string $fileNorm, Personnalite $p): float
    {
        $first = $this->normalize($p->getFirstName());
        $last = $this->normalize($p->getLastName());
        $candidates = array_unique(array_filter([
            trim($first.' '.$last),
            trim($last.' '.$first),
            $last,
            $first,
        ]));

        $best = 0.0;
        foreach ($candidates as $candidate) {
            if ('' === $candidate) {
                continue;
            }
            similar_text($fileNorm, $candidate, $percent);
            $best = max($best, $percent, $this->tokenOverlap($fileNorm, $candidate));
        }

        return $best;
    }

    private function normalize(string $value): string
    {
        $value = preg_replace('/\.(jpe?g|png|avif|webp)$/i', '', $value) ?? $value;
        $value = str_replace(['_', '(', ')', '.', "'", '’'], ' ', $value);
        $slug = strtolower((string) $this->slugger->slug($value, ' '));

        return trim(preg_replace('/\s+/', ' ', $slug) ?? $slug);
    }

    private function tokenOverlap(string $a, string $b): float
    {
        $tokensA = array_values(array_filter(explode(' ', $a)));
        $tokensB = array_values(array_filter(explode(' ', $b)));
        if (!$tokensA || !$tokensB) {
            return 0.0;
        }

        $common = 0;
        $remaining = $tokensB;
        foreach ($tokensA as $token) {
            $idx = array_search($token, $remaining, true);
            if (false !== $idx) {
                ++$common;
                unset($remaining[$idx]);
            }
        }

        return (2 * $common / (count($tokensA) + count($tokensB))) * 100;
    }

    /**
     * @param array{file: SplFileInfo, score: float} $a
     * @param array{file: SplFileInfo, score: float} $b
     */
    private function compareCandidates(array $a, array $b): int
    {
        if ($a['score'] !== $b['score']) {
            return $b['score'] <=> $a['score'];
        }

        $extA = self::EXTENSION_PREFERENCE[strtolower($a['file']->getExtension())] ?? 9;
        $extB = self::EXTENSION_PREFERENCE[strtolower($b['file']->getExtension())] ?? 9;
        if ($extA !== $extB) {
            return $extA <=> $extB;
        }

        return $a['file']->getSize() <=> $b['file']->getSize();
    }
}
