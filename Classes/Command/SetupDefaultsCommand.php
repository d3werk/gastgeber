<?php

declare(strict_types=1);

namespace D3Werk\Gastgeber\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsCommand(name: 'gastgeber:setup:defaults', description: 'Legt Standard-Unterkunftsarten, Merkmalsgruppen, Merkmale, Ortsteile und Zertifikate an.')]
class SetupDefaultsCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('pid', null, InputOption::VALUE_REQUIRED, 'PID des Speicherordners', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pid = (int)$input->getOption('pid');
        foreach (['Hotel','Pension','Ferienwohnung','Ferienhaus','Bauernhof','Pferdehof','Campingstellplatz','Privatzimmer'] as $title) {
            $this->insertIfMissing('tx_gastgeber_domain_model_type', $pid, $title);
        }
        $groups = [
            'Allgemein' => ['WLAN','Parkplatz','Nichtraucher','Haustiere erlaubt','Haustiere auf Anfrage'],
            'Außenbereich' => ['Balkon','Terrasse','Garten / Wiese','Grillmöglichkeit'],
            'Familie' => ['Kinderbett','Hochstuhl','Spielplatz / Spielgeräte','Familienfreundlich'],
            'Mobilität' => ['Fahrradgarage','E-Ladestation','Fahrradverleih','Bett & Bike'],
            'Wellness' => ['Sauna','Pool','Badewanne'],
            'Barrierefreiheit' => ['Barrierefrei','Rollstuhlgerechtes Zimmer','Stufenloser Zugang'],
            'Reiten' => ['Pferdeboxen','Bett & Box','Reitpferde','Ponys'],
            'Ferienwohnung / Ferienhaus' => ['Küche','Kochnische','Geschirrspüler','Waschmaschine','Trockner'],
        ];
        foreach ($groups as $groupTitle => $features) {
            $groupUid = $this->insertIfMissing('tx_gastgeber_domain_model_featuregroup', $pid, $groupTitle);
            foreach ($features as $featureTitle) {
                $this->insertIfMissing('tx_gastgeber_domain_model_feature', $pid, $featureTitle, ['group' => $groupUid]);
            }
        }
        foreach (['Undeloh','Wesel','Wehlen','Meningen','Heimbuch','Thonhof','Egestorf'] as $title) {
            $this->insertIfMissing('tx_gastgeber_domain_model_district', $pid, $title);
        }
        foreach (['1 Stern','2 Sterne','3 Sterne','4 Sterne','5 Sterne','DTV klassifiziert','Bett+Bike'] as $title) {
            $this->insertIfMissing('tx_gastgeber_domain_model_certificate', $pid, $title);
        }
        $output->writeln('<info>Gastgeber-Standarddaten wurden angelegt/ergänzt.</info>');
        return Command::SUCCESS;
    }

    /** @param array<string,mixed> $extra */
    private function insertIfMissing(string $table, int $pid, string $title, array $extra = []): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $existing = $connection->createQueryBuilder()
            ->select('uid')
            ->from($table)
            ->where(
                'pid = ' . (int)$pid,
                'title = ' . $connection->quote($title),
                'deleted = 0'
            )
            ->executeQuery()
            ->fetchOne();
        if ($existing) {
            return (int)$existing;
        }
        $row = array_merge([
            'pid' => $pid,
            'title' => $title,
            'slug' => $this->slugify($title),
            'crdate' => time(),
            'tstamp' => time(),
        ], $extra);
        $connection->insert($table, $row);
        return (int)$connection->lastInsertId();
    }

    private function slugify(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9äöüÄÖÜß]+/u', '-', $title) ?? '', '-'));
        return str_replace(['ä','ö','ü','ß'], ['ae','oe','ue','ss'], $slug);
    }
}
