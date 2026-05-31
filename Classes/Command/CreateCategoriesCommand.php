<?php

namespace D3Werk\Gastgeber\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;

final class CreateCategoriesCommand extends Command
{
    /**
     * Empfohlene Taxonomie für Gastgeber-Suche und News-Kategorisierung.
     * Die Typ-Kategorien liegen bewusst unter "Gastgeber-Art", damit Filter sauber trennbar bleiben.
     */


    /**
     * Optionale Standard-Iconklassen. Diese Klassen werden von der Extension gestylt
     * und können im Backend pro Kategorie jederzeit ersetzt oder durch ein Upload-Icon überschrieben werden.
     */
    private const ICON_CLASS_BY_TITLE = [
        'Hotel' => 'gastgeber-icon--hotel',
        'Pension' => 'gastgeber-icon--bed',
        'Ferienwohnung' => 'gastgeber-icon--apartment',
        'Ferienhaus' => 'gastgeber-icon--house',
        'Pferdehof' => 'gastgeber-icon--horse',
        'Bauernhof' => 'gastgeber-icon--farm',
        'Campingstellplatz' => 'gastgeber-icon--camping',
        'Bett & Bike' => 'gastgeber-icon--bike',
        'Nichtraucher' => 'gastgeber-icon--nonsmoking',
        'Rollstuhlgerecht / barrierefrei' => 'gastgeber-icon--accessible',
        'Familienfreundlich' => 'gastgeber-icon--family',
        'Frühstück möglich' => 'gastgeber-icon--breakfast',
        'Restaurant im Haus' => 'gastgeber-icon--restaurant',
        'Saal / Tagungsraum' => 'gastgeber-icon--meeting',
        'Safe' => 'gastgeber-icon--safe',
        'WLAN' => 'gastgeber-icon--wifi',
        'ÖPNV-nah' => 'gastgeber-icon--bus',
        'Ruhige Lage' => 'gastgeber-icon--quiet',
        'Zentrumsnah' => 'gastgeber-icon--center',
        'Natur / Heide' => 'gastgeber-icon--nature',
        'Balkon' => 'gastgeber-icon--balcony',
        'Terrasse' => 'gastgeber-icon--terrace',
        'Garten / Wiese' => 'gastgeber-icon--garden',
        'Parkplatz' => 'gastgeber-icon--parking',
        'Garage' => 'gastgeber-icon--garage',
        'E-Ladestation' => 'gastgeber-icon--charging',
        'Fahrradgarage mit Ladestation' => 'gastgeber-icon--bike-charging',
        'Fahrradverleih' => 'gastgeber-icon--bike',
        'Schwimmbad im Haus' => 'gastgeber-icon--pool',
        'Sauna' => 'gastgeber-icon--sauna',
        'Spielplatz / Spielgeräte' => 'gastgeber-icon--playground',
        'Hunde erlaubt' => 'gastgeber-icon--dog',
        'Haustiere erlaubt' => 'gastgeber-icon--pets',
        'Haustiere auf Anfrage' => 'gastgeber-icon--pets-request',
        'Pferdeboxen' => 'gastgeber-icon--horse-box',
        'Bett & Box' => 'gastgeber-icon--bed-box',
        'Reitpferde' => 'gastgeber-icon--riding',
        'Ponys' => 'gastgeber-icon--pony',
        'Küche' => 'gastgeber-icon--kitchen',
        'Kochnische' => 'gastgeber-icon--kitchenette',
        'Kombiniertes Wohn-/Schlafzimmer' => 'gastgeber-icon--living-bedroom',
        'Kinderbett' => 'gastgeber-icon--babybed',
        'Waschmaschine' => 'gastgeber-icon--washing',
        'Trockner' => 'gastgeber-icon--dryer',
        'Geschirrspüler' => 'gastgeber-icon--dishwasher',
        'TV im Zimmer / FW / FH' => 'gastgeber-icon--tv',
    ];

    private const CATEGORY_TREE = [
        'Gastgeber' => [
            'Gastgeber-Art' => [
                'Hotel' => [
                    'Hotel garni',
                    'Landhotel',
                    'Wellnesshotel',
                    'Tagungshotel',
                ],
                'Pension' => [
                    'Frühstückspension',
                    'Privatpension',
                ],
                'Ferienwohnung' => [
                    'Ferienwohnung 1-2 Personen',
                    'Ferienwohnung Familie',
                    'Ferienwohnung barrierearm',
                ],
                'Ferienhaus' => [
                    'Ferienhaus Familie',
                    'Ferienhaus Gruppe',
                    'Ferienhaus mit Garten',
                ],
                'Pferdehof' => [
                    'Reiterhof',
                    'Urlaub mit eigenem Pferd',
                    'Reitunterricht',
                ],
                'Bauernhof' => [
                    'Urlaub auf dem Bauernhof',
                    'Kinderbauernhof',
                    'Hofladen',
                ],
                'Campingstellplatz' => [
                    'Wohnmobilstellplatz',
                    'Zeltplatz',
                    'Campingplatz',
                ],
            ],
            'Merkmale' => [
                'Allgemein' => [
                    'Bett & Bike',
                    'Nichtraucher',
                    'Rollstuhlgerecht / barrierefrei',
                    'Familienfreundlich',
                    'Frühstück möglich',
                    'Restaurant im Haus',
                    'Saal / Tagungsraum',
                    'Safe',
                    'WLAN',
                    'ÖPNV-nah',
                    'Ruhige Lage',
                    'Zentrumsnah',
                    'Natur / Heide',
                ],
                'Außenbereich' => [
                    'Balkon',
                    'Terrasse',
                    'Garten / Wiese',
                    'Parkplatz',
                    'Garage',
                    'E-Ladestation',
                    'Fahrradgarage mit Ladestation',
                ],
                'Freizeit & Wellness' => [
                    'Fahrradverleih',
                    'Schwimmbad im Haus',
                    'Sauna',
                    'Spielplatz / Spielgeräte',
                ],
                'Haustiere & Reiten' => [
                    'Hunde erlaubt',
                    'Haustiere erlaubt',
                    'Haustiere auf Anfrage',
                    'Pferdeboxen',
                    'Bett & Box',
                    'Reitpferde',
                    'Ponys',
                ],
                'Ferienwohnung / Ferienhaus' => [
                    'Küche',
                    'Kochnische',
                    'Kombiniertes Wohn-/Schlafzimmer',
                    'Kinderbett',
                    'Waschmaschine',
                    'Trockner',
                    'Geschirrspüler',
                    'TV im Zimmer / FW / FH',
                ],
            ],
            'Zielgruppen' => [
                'Paare',
                'Familien',
                'Gruppen',
                'Alleinreisende',
                'Reiter',
                'Radfahrer',
                'Wanderer',
                'Wohnmobilreisende',
            ],
        ],
    ];

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'pid',
            null,
            InputOption::VALUE_REQUIRED,
            'Seiten-/Ordner-UID, in der die sys_category-Datensätze angelegt werden sollen.',
            '0'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pid = (int)$input->getOption('pid');
        if ($pid < 0) {
            $output->writeln('<error>Die Option --pid muss 0 oder größer sein.</error>');
            return Command::INVALID;
        }

        $created = 0;
        $skipped = 0;
        foreach (self::CATEGORY_TREE as $title => $children) {
            [$created, $skipped] = $this->createCategoryTree($title, $children, $pid, 0, $created, $skipped);
        }

        $output->writeln(sprintf('<info>Fertig: %d Kategorien neu angelegt, %d bereits vorhanden.</info>', $created, $skipped));
        return Command::SUCCESS;
    }

    /**
     * @param array<int|string, mixed> $children
     * @return array{0:int,1:int}
     */
    private function createCategoryTree(string $title, array $children, int $pid, int $parent, int $created, int $skipped, int $sorting = 0): array
    {
        $uid = $this->findCategoryUid($title, $pid, $parent);
        if ($uid === 0) {
            $uid = $this->insertCategory($title, $pid, $parent, $sorting);
            ++$created;
        } else {
            ++$skipped;
        }

        $this->applyDefaultIconClass($uid, $title);

        $sorting = 10;
        foreach ($children as $childTitle => $grandChildren) {
            if (is_int($childTitle)) {
                $childTitle = (string)$grandChildren;
                $grandChildren = [];
            }
            [$created, $skipped] = $this->createCategoryTree((string)$childTitle, (array)$grandChildren, $pid, $uid, $created, $skipped, $sorting);
            $sorting += 10;
        }

        return [$created, $skipped];
    }


    private function applyDefaultIconClass(int $uid, string $title): void
    {
        $iconClass = self::ICON_CLASS_BY_TITLE[$title] ?? '';
        if ($uid <= 0 || $iconClass === '') {
            return;
        }

        try {
            $connection = $this->connectionPool->getConnectionForTable('sys_category');
            $currentValue = $connection->select(
                ['tx_gastgeber_icon_css_class'],
                'sys_category',
                ['uid' => $uid]
            )->fetchOne();

            if (trim((string)($currentValue ?: '')) === '') {
                $connection->update(
                    'sys_category',
                    ['tx_gastgeber_icon_css_class' => $iconClass, 'tstamp' => time()],
                    ['uid' => $uid]
                );
            }
        } catch (\Throwable) {
            // Die Kategorieanlage soll auch funktionieren, falls die Datenbankspalten noch nicht aktualisiert wurden.
        }
    }

    private function findCategoryUid(string $title, int $pid, int $parent): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $uid = $queryBuilder
            ->select('uid')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($parent, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($title))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return (int)($uid ?: 0);
    }

    private function insertCategory(string $title, int $pid, int $parent, int $sorting): int
    {
        $time = time();
        $connection = $this->connectionPool->getConnectionForTable('sys_category');
        $data = [
            'pid' => $pid,
            'title' => $title,
            'parent' => $parent,
            'sorting' => $sorting,
            'crdate' => $time,
            'tstamp' => $time,
            'sys_language_uid' => 0,
            'hidden' => 0,
        ];

        if (isset(self::ICON_CLASS_BY_TITLE[$title])) {
            $data['tx_gastgeber_icon_css_class'] = self::ICON_CLASS_BY_TITLE[$title];
        }

        try {
            $connection->insert('sys_category', $data);
        } catch (\Throwable) {
            unset($data['tx_gastgeber_icon_css_class']);
            $connection->insert('sys_category', $data);
        }

        return (int)$connection->lastInsertId('sys_category');
    }
}
