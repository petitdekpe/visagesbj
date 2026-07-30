<?php

namespace App\Command;

use App\Repository\PersonnaliteRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

#[AsCommand(
    name: 'app:personnalites:consent:pdf',
    description: 'Génère un PDF listant les personnalités et leur lien de consentement personnalisé',
)]
class ExportPersonnalitesConsentPdfCommand extends Command
{
    public function __construct(
        private readonly PersonnaliteRepository $personnaliteRepository,
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('output', null, InputOption::VALUE_REQUIRED, 'Chemin du fichier PDF à générer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $outputPath = $input->getOption('output') ?: $this->projectDir.'/var/personnalites-consentement.pdf';

        $personnalites = $this->personnaliteRepository->findAllOrdered();

        $rows = array_map(fn ($p) => [
            'nom' => $p->getFullName(),
            'lien' => $this->urlGenerator->generate('app_personnalites_consent', ['slug' => $p->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
            'statut' => $p->isConsentAccepted() ? 'Oui, '.$p->getConsentedAt()?->format('d/m/Y') : 'Non',
        ], $personnalites);

        $html = $this->twig->render('admin/personnalite/consent_pdf.html.twig', [
            'rows' => $rows,
            'generatedAt' => new \DateTimeImmutable(),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dir = \dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($outputPath, $dompdf->output());

        $io->success(sprintf('%d personnalité(s) exportée(s) vers %s', count($rows), $outputPath));

        return Command::SUCCESS;
    }
}
