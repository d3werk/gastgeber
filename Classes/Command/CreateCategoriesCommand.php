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

#[AsCommand(name: 'gastgeber:categories:create', description: 'Legt den Gastgeber-Kategoriebaum an.')]
class CreateCategoriesCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('pid', null, InputOption::VALUE_REQUIRED, 'PID des Ordners für sys_category', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pid = (int)$input->getOption('pid');
        $root = $this->createCategory('Gastgeber', $pid, 0, '', false, true);
        $art = $this->createCategory('Gastgeber-Art', $pid, $root, 'bi bi-house-heart', false, true);
        foreach (['Hotel','Pension','Ferienwohnung','Ferienhaus','Pferdehof','Bauernhof','Campingstellplatz','Privatzimmer'] as $title) {
            $this->createCategory($title, $pid, $art, 'bi bi-house', false, false);
        }
        $classification = $this->createCategory('Klassifizierung', $pid, $root, 'bi bi-star', false, true);
        foreach (['Keine Sterne','1 Stern','2 Sterne','3 Sterne','4 Sterne','5 Sterne'] as $title) {
            $this->createCategory($title, $pid, $classification, 'bi bi-star-fill', false, false);
        }
        $features = $this->createCategory('Merkmale', $pid, $root, 'bi bi-ui-checks-grid', false, true);
        $featureMap = [
            'WLAN' => 'bi bi-wifi',
            'Parkplatz' => 'bi bi-p-square',
            'Balkon' => 'bi bi-border-top',
            'Terrasse' => 'bi bi-brightness-high',
            'Garten / Wiese' => 'bi bi-flower1',
            'Haustiere erlaubt' => 'bi bi-heart',
            'Haustiere auf Anfrage' => 'bi bi-question-circle',
            'Hunde erlaubt' => 'bi bi-heart-fill',
            'Barrierefrei' => 'bi bi-universal-access',
            'Rollstuhlgerecht' => 'bi bi-universal-access-circle',
            'Nichtraucher' => 'bi bi-ban',
            'Sauna' => 'bi bi-thermometer-sun',
            'Schwimmbad' => 'bi bi-water',
            'Fahrradfreundlich' => 'bi bi-bicycle',
            'Fahrradverleih' => 'bi bi-bicycle',
            'E-Bike-Ladestation' => 'bi bi-lightning-charge',
            'Garage' => 'bi bi-car-front',
            'Pferdeboxen' => 'bi bi-signpost-2',
            'Bett & Box' => 'bi bi-signpost-split',
            'Reitpferde' => 'bi bi-compass',
            'Ponys' => 'bi bi-compass-fill',
            'Spielplatz / Spielgeräte' => 'bi bi-emoji-smile',
            'Restaurant im Haus' => 'bi bi-cup-hot',
            'Saal / Tagungsraum' => 'bi bi-people',
            'Safe' => 'bi bi-lock',
            'TV' => 'bi bi-tv',
            'Küche' => 'bi bi-cup-hot',
            'Kochnische' => 'bi bi-cup',
            'Kinderbett' => 'bi bi-person-arms-up',
            'Waschmaschine' => 'bi bi-droplet',
            'Trockner' => 'bi bi-wind',
            'Geschirrspüler' => 'bi bi-droplet-half',
        ];
        foreach ($featureMap as $title => $icon) {
            $this->createCategory($title, $pid, $features, $icon, false, false);
        }
        $targets = $this->createCategory('Zielgruppen', $pid, $root, 'bi bi-people', false, true);
        foreach (['Familienfreundlich','Gruppen geeignet','Paarurlaub','Urlaub mit Pferd','Wanderfreundlich','Radfahrerfreundlich','Seniorenfreundlich'] as $title) {
            $this->createCategory($title, $pid, $targets, 'bi bi-check2-circle', false, false);
        }
        $output->writeln('<info>Gastgeber-Kategorien wurden angelegt oder ergänzt.</info>');
        return Command::SUCCESS;
    }

    private function createCategory(string $title, int $pid, int $parent, string $iconClass, bool $hideFilter, bool $isGroup): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_category');
        $existing = $connection->select(['uid'], 'sys_category', ['title' => $title, 'parent' => $parent, 'pid' => $pid])->fetchAssociative();
        if (is_array($existing)) {
            return (int)$existing['uid'];
        }
        $connection->insert('sys_category', [
            'pid' => $pid,
            'title' => $title,
            'parent' => $parent,
            'sorting' => time(),
            'tx_gastgeber_icon_class' => $iconClass,
            'tx_gastgeber_hide_filter' => $hideFilter ? 1 : 0,
            'tx_gastgeber_is_filter_group' => $isGroup ? 1 : 0,
        ]);
        return (int)$connection->lastInsertId('sys_category');
    }
}
