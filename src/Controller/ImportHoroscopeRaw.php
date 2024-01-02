<?php

namespace App\Controller;

use App\Entity\AstrologicalSign;
use App\Entity\DailyContent;
use App\Entity\HoroscopeFinal;
use App\Entity\HoroscopeRaw;
use App\Entity\Enums;
use App\Entity\WordingAndConfig;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportHoroscopeRaw extends AbstractController
{
    /**
     * @Route("/", name="site-dashboard")
     */
    public function showDashboard(Request $request, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $today = new \DateTime('today');
        $tomorrow = new \DateTime('tomorrow');
        $afterTomorrow = new \DateTime('now +2 day');

//        $finalHoroscopes = $em->getRepository(HoroscopeFinal::class)->createQueryBuilder('e')
//            ->select('e.date')
////            ->select('e.astrologicalSign')
////            ->where('e.date LIKE :date')
////            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
////            ->andWhere('e.type = :type')
////            ->setParameter(':type', 'base')
//            ->groupBy('e.date')
//            ->orderBy('e.date', 'DESC')
//            ->getQuery()
//            ->getResult()
//        ;
//
//        $query = $em->createQueryBuilder()
//            ->select('hf.date', 'a.name as astrologicalSign', 'hf.content')
//            ->from(HoroscopeFinal::class, 'hf')
//            ->join('hf.astrologicalSign', 'a')
//            ->orderBy('hf.date', 'ASC')
//            ->addOrderBy('a.id', 'ASC')
//            ->getQuery();
//
//        $results = $query->getResult();
//
////        dd($results);
////        dd($finalHoroscopes);


        return $this->render('dashboard.html.twig', [
            'today' => $today,
            'tomorrow' => $tomorrow,
            'afterTomorrow' => $afterTomorrow,
            'finalHoroscopes' => [],
//        'finalHoroscopes' => $finalHoroscopes
        ]);
    }

    /**
     * @Route("/import/{year}/{month}/{day}/{locale}", name="import-from-ezotv")
     */
    public function importFromEzoTV(Request $request, EntityManagerInterface $em, ValidatorInterface $validator,
                                       $year, $month, $day, ?string $locale)
    {
        $date = \DateTime::createFromFormat(Enums::DATE_FORMAT, sprintf('%s-%s-%s', $year, $month, $day));

        $horoscopes = $em->getRepository(HoroscopeRaw::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->getQuery()
            ->getResult()
        ;

        $localeInApiCall = 'hu';
        if ($locale == 'hu' || $locale == 'ro') {
            $localeInApiCall = $locale;
        }

        $horoscopeApiRaw = 'https://hu.ezo.tv/export?type=horoscope&langid=hu&affid=1&year=2023&month=04&day=21&brand=blank-postfix';
        $apiUri = 'https://hu.ezo.tv/export';

        $client = new Client();
        $response = $client->get($apiUri, [
            'query' => [
                'type' => 'horoscope',
                'langid' => $localeInApiCall,
                'affid' => 1,
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'brand' => 'blank-postfix'
            ],
        ]);

//        dd($response->getStatusCode());

        if (200 === $response->getStatusCode()) {
            $result = $response->getBody()->getContents();

            $domDocument = new \DOMDocument();
            $domDocument->loadXml($result);
            $nodeList = $domDocument->getElementsByTagName('sign');

            $crawler = new Crawler();
            $crawler->addDocument($domDocument);
            $crawler->addNodeList($nodeList);

            $horoscopeArray = [];
            foreach (Enums::ZODIAC_SIGNS as $sign) {
//                $crawlerNode = $crawler->filter('[type="aries"]');
                $crawlerNode = $crawler->filter(sprintf('[type="%s"]',$sign));

                foreach ($crawlerNode as $domElement) {

//                    dd($domElement->nodeValue);
                    $horoscopeArray[$sign] = $domElement->nodeValue;
                }
            }

            if (empty($horoscopes)) {
                foreach ($horoscopeArray as $sign => $content) {
                    $horoscope = new HoroscopeRaw();
                    $horoscope->setDate($date);
                    $horoscope->setLocale($locale);

                    $astrologicalSign = $em->getRepository(AstrologicalSign::class)->findOneBy(['slug' => $sign]);
                    $horoscope->setAstrologicalSign($astrologicalSign);
                    $horoscope->setContent($this->beautifyContent($content));

                    $em->persist($horoscope);
                }
                $em->flush();
            } else {
                foreach ($horoscopes as $horoscope) {
                    $horoscope->setContent($this->beautifyContent($horoscopeArray[$horoscope->getAstrologicalSign()->getSlug()]));
                    $em->persist($horoscope);
                }
                $em->flush();
            }

            return $this->redirectToRoute('show-imported', [
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'locale' => $locale
            ]);
        }
        return new Response('NEM OK!');
    }

    /**
     * @ Route("/import/{year}/{month}/{day}/{locale}", name="import-from-ezotv")
     */
    public function importFromEzoTVFromArray(Request $request, EntityManagerInterface $em, ValidatorInterface $validator,
                                            $year, $month, $day, ?string $locale)
    {
        $date = \DateTime::createFromFormat(Enums::DATE_FORMAT, sprintf('%s-%s-%s', $year, $month, $day));

        $horoscopes = $em->getRepository(HoroscopeRaw::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->getQuery()
            ->getResult()
        ;

        $horoscopeArray = [];
        $horoscopeArray['aries'] = "Amíg a saját útját járja, képes lesz megküzdeni az akadályokkal is. Ha azonban Önre erőltetnek egy olyan feladatot, amellyel képtelen azonosulni, akkor vonuljon félre, hogy dönthessen, merre haladjon, így lesz elégedett azzal, amit teljesít. Most nem is az eredmények jelentik az Ön számára a sikert, hanem a harcok, amelyeket meg kell vívnia. De sikerülni fog és a nap végén elégedett lesz!";
        $horoscopeArray['taurus'] = "Tegyen különbséget a valódi problémák és az olyan gondok között, amelyek csak a gondolatait zavarják össze.  Ki kell választania a lényeges kérdéseket, azokkal foglalkozzon, minden más esetben tudjon nemet mondani. Legyen határozott, amivel egyértelművé teszi a környezetében élők számára, hogy vannak kitűzött céljai. És a környezete el fogja fogadni, elismerően fognak Önre tekinteni.";
        $horoscopeArray['gemini'] = "Sokszor már az is nagy segítség lehet, ha valakit meghallgatnak. Önhöz fordulnak tanácsért, hiszen nyugalmat, tudást, magabiztosságot sugároz, és ebből szeretnének erőt meríteni. Nem is kell megoldásokat keresnie, csak csendben figyelni és engedni, hogy a másik fél szavakba önthesse a problémáját. Töretlen népszerűségét annak köszönheti, hogy megadja a keresett válaszokat.";
        $horoscopeArray['cancer'] = "Döntenie kell, az eszére vagy a szívére hallgasson. Jó ha az ösztönei vezérlik. Ha már elindult, ne azt mérlegelje, mi lett volna, ha a másik utat választja, éljen azzal a lehetőséggel, amely mellett letette a voksát. Az így elért eredményeket ugyanúgy a kimagasló teljesítményének köszönheti. Legyen ezért elszánt és határozott, ne sokat habozzon, és remek napja lesz!";
        $horoscopeArray['leo'] = "Ha már eldöntötte, ne habozzon túl sokáig, induljon el. Bár még bizonyos lépéseket nem dolgozott ki, de a lendülete viszi előre. Sikeresen és észrevétlenül veheti azokat az akadályokat is, melyekre nem készült fel. Most van lehetősége megvalósítani ezt a tervét. Tele van energiával, így a határidős kötelezettségeit is képes elvégezni, emiatt sem kell aggódnia, remek napja lesz!";
        $horoscopeArray['virgo'] = "Ön is tudja, a problémát csak úgy lehet megoldani, ha leás a gyökeréig. Ehhez ugyan fel kell tennie kényes kérdéseket, de meg tudja azokat válaszolni, és használja ki az alkalmat, hogy kevesebb a kötelezettsége. Így kellő türelemmel nyithat azok felé, akik elkísérhetik Önt azon az úton, ami meghozza a kívánt eredményeket is. Remek napja lesz!";
        $horoscopeArray['libra'] = "Rögtönöznie kell de gondoljon a céljaira, fogadja el, hogy ezek a elkerülhetetlenek, ha szeretné megvalósítani azokat. Áldozatok nélkül ugyanis nincsenek eredmények, ezt már tudja, ahogy azt is, hogy milyen remek eredményeket tud elérni. Most csak ismét bebizonyíthatja, hogy az elszántsága csodákat képes alkotni!";
        $horoscopeArray['scorpio'] = "Ne habozzon, induljon el! ma egy nagyszerű nap elébe néz, érdemes nekivágni az útnak! A siker nem hullik az ölébe, de meg fogja kapni. Pontosan ismer mindent, magabiztos, rendelkezik a megfelelő tudással. Bízzon a képességeiben, amik olyan sokszor sikerre vitték az életben. És ma sem lesz másként! A tudása, türelme, elszántsága sikerrel zárja majd a napját.";
        $horoscopeArray['sagittarius'] = "Ragaszkodik ahhoz, hogy az irányítást a kezében tartsa. Emiatt könnyű hibázni, és előfordul, hogy azt veszi észre, hogy nem jönnek az eredmények. De ez mind Önön múlik! A mai napon az energiáit fordítsa arra, hogy járja a saját útját, és foglalkozzon olyan kérdésekkel, amelyek közelebb viszik a céljaihoz! Ha így tesz, vezető szerepben marad és csodálatos napja lesz!  ";
        $horoscopeArray['capricorn'] = "Úgy tűnik, mintha az ölébe hullottak volna az eredmények, de a kívülállók nem láthatják, mennyi munkát fektetett abba, hogy most csak könnyedén besétáljon a célba. Fontos, hogy Ön tisztában van azzal, mennyi lemondás árán ért el idáig. Most pedig minden oka megvan arra, hogy ünnepeljen, nagyon is megérdemli. Remek napja lesz, élvezze ki!";
        $horoscopeArray['aquarius'] = "Ne engedje, hogy becsukódjon Ön előtt az ajtó! Ügyes szervezéssel megoldhatja, hogy éljen a lehetőséggel, de a kötelezettségeit is elvégzi. Fogadja el a felkínált segítséget! Ne kapkodjon, lépésről lépésre haladjon, így el fogja kerülni az olyan akadályokat, amelyek lelassítanák, esetleg meg is hiúsítanák az elképzeléseit, az álmait. Remek nap elébe néz!";
        $horoscopeArray['pisces'] = "Vegye észre a lehetőségeket! Legyen nyitott és fogékony az újdonságokra, mert ezek által léphet előre. El kell indulnia az úton, ha valóban fontosnak tartja a célt, amelyet kitűzött erre a napra. Bár úgy tűnik, van még ideje, de bizonyos folyamatoknak be kell érnie. A határozottsága és a tapasztalata azonban pontosan megmutatja, mit is kell tennie és sikeres lesz.";
//
//        $horoscopeArray['aries'] = "";
//        $horoscopeArray['taurus'] = "";
//        $horoscopeArray['gemini'] = "";
//        $horoscopeArray['cancer'] = "";
//        $horoscopeArray['leo'] = "";
//        $horoscopeArray['virgo'] = "";
//        $horoscopeArray['libra'] = "";
//        $horoscopeArray['scorpio'] = "";
//        $horoscopeArray['sagittarius'] = "";
//        $horoscopeArray['capricorn'] = "";
//        $horoscopeArray['aquarius'] = "";
//        $horoscopeArray['pisces'] = "";


        if (empty($horoscopes)) {
                foreach ($horoscopeArray as $sign => $content) {
                    $horoscope = new HoroscopeRaw();
                    $horoscope->setDate($date);
                    $horoscope->setLocale($locale);

                    $astrologicalSign = $em->getRepository(AstrologicalSign::class)->findOneBy(['slug' => $sign]);
                    $horoscope->setAstrologicalSign($astrologicalSign);
                    $horoscope->setContent($this->beautifyContent($content));

                    $em->persist($horoscope);
                }
                $em->flush();
            } else {
                foreach ($horoscopes as $horoscope) {
                    $horoscope->setContent($this->beautifyContent($horoscopeArray[$horoscope->getAstrologicalSign()->getSlug()]));
                    $em->persist($horoscope);
                }
                $em->flush();
            }

            return $this->redirectToRoute('show-imported', [
                'year' => $year,
                'month' => $month,
                'day' => $day,
                'locale' => $locale
            ]);
//        }
//        return new Response('NEM OK!');
    }

    private function beautifyContent(string $content)
    {
        $text = preg_replace('/(?<!Dvs)\./', ".\n\n", $content);
//        $text = preg_replace('/([.?!])\s+/', "$1\n\n", $content);
        $stringsToRemove = ['</p><p>', '&lt;p&gt;'];

        $newText = strstr($text, $stringsToRemove[0], true);
        if ($newText === false) {
            $newText = strstr($text, $stringsToRemove[1], true);
        }

        // Ha egyiket sem talalta meg a stringToRemove-bol
        if ($newText === false) {
            $newText = $text;
        }

        return $newText;
    }

    /**
     * @Route("/show-imported/{year}/{month}/{day}/{locale}", name="show-imported")
     */
    public function showImportedHoroscope(Request $request, EntityManagerInterface $em, $year, $month, $day, ?string $locale)
    {
        $date = \DateTime::createFromFormat(Enums::DATE_FORMAT, sprintf('%s-%s-%s', $year, $month, $day));
        $horoscopes = $em->getRepository(HoroscopeRaw::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->getQuery()
            ->getResult()
        ;

        return $this->render('show-imported-horoscope.html.twig', [
            'date' => $date,
            'locale' => $locale,
            'horoscopes' => $horoscopes,
            'signs' => Enums::ZODIAC_SIGNS,
            'csillagjegyek' => Enums::CSILLAGJEGYEK,
        ]);
    }

    /**
     * @Route("/show-rewritten/{year}/{month}/{day}/{locale}", name="show-rewritten")
     */
    public function showRewritten(Request $request, EntityManagerInterface $em, $year, $month, $day, $locale)
    {
        $date = \DateTime::createFromFormat(Enums::DATE_FORMAT, sprintf('%s-%s-%s', $year, $month, $day));
        $baseHoroscopes = $em->getRepository(HoroscopeFinal::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->andWhere('e.type = :type')
            ->setParameter(':type', 'base')
            ->orderBy('e.astrologicalSign', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $addendumHoroscopes = $em->getRepository(HoroscopeFinal::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->andWhere('e.type = :type')
            ->setParameter(':type', 'addendum')
            ->orderBy('e.astrologicalSign', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        $rawHoroscopes = $em->getRepository(HoroscopeRaw::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->orderBy('e.astrologicalSign', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $this->render('show-rewritten-horoscope.html.twig', [
            'date' => $date,
            'locale' => $locale,
            'baseHoroscopes' => $baseHoroscopes,
            'addendumHoroscopes' => $addendumHoroscopes,
            'rawHoroscopes' => $rawHoroscopes,
            'signs' => Enums::ZODIAC_SIGNS,
            'csillagjegyek' => Enums::CSILLAGJEGYEK,
        ]);
    }

    /**
     * @Route("/show-rewritten-sign/{year}/{month}/{day}/{locale}/{sign}", name="show-rewritten-sign")
     */
    public function showRewrittenSign(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, $year, $month, $day, $sign, $locale)
    {
        $date = \DateTime::createFromFormat(Enums::DATE_FORMAT, sprintf('%s-%s-%s', $year, $month, $day));

        $astrologicalSign = $em->getRepository(AstrologicalSign::class)->findOneBy(['slug' => $sign]);
        // Extract the horoscope for given 'date' and 'sign' from db

        $baseHoroscope = $em->getRepository(HoroscopeFinal::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->andWhere('e.astrologicalSign = :sign')
            ->setParameter(':sign', $astrologicalSign->getId())
            ->andWhere('e.type = :type')
            ->setParameter(':type', 'base')
            ->getQuery()
            ->getResult()
        ;
        if (empty($baseHoroscope)) {
            throw new NotFoundHttpException('HIBA: The $horoscopes array is empty! Nem talalt ilyen HoroscopeFinal-t!');
        }
        $addendumHoroscope = $em->getRepository(HoroscopeFinal::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->andWhere('e.astrologicalSign = :sign')
            ->setParameter(':sign', $astrologicalSign->getId())
            ->andWhere('e.type = :type')
            ->setParameter(':type', 'addendum')
            ->getQuery()
            ->getResult()
        ;

        if (empty($addendumHoroscope)) {
            $addendumHoroscope = null;
//            throw new NotFoundHttpException('HIBA: The $horoscopes array is empty! Nem talalt ilyen HoroscopeFinal-t!');
        }

        $rawHoroscopes = $em->getRepository(HoroscopeRaw::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->andWhere('e.astrologicalSign = :sign')
            ->setParameter(':sign', $astrologicalSign->getId())
            ->orderBy('e.astrologicalSign', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $this->render('admin/show-rewritten-sign.twig', [
            'date' => $date,
            'locale' => $locale,
            'sign' => $sign,
            'csillagjegyek' => Enums::CSILLAGJEGYEK,
            'baseHoroscopes' => $baseHoroscope,
            'addendumHoroscopes' => $addendumHoroscope,
            'rawHoroscopes' => $rawHoroscopes,
        ]);
    }

    /**
     * @Route("/rewrite/{year}/{month}/{day}/{locale}/{sign}", name="rewrite-horoscope")
     */
    public function rewriteHoroscope(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, $year, $month, $day, $sign, $locale)
    {
        $date = \DateTime::createFromFormat(Enums::DATE_FORMAT, sprintf('%s-%s-%s', $year, $month, $day));

        $astrologicalSign = $em->getRepository(AstrologicalSign::class)->findOneBy(['slug' => $sign]);
        // Extract the horoscope for given 'date' and 'sign' from db
        $horoscopes = $em->getRepository(HoroscopeRaw::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->andWhere('e.astrologicalSign = :sign')
            ->setParameter(':sign', $astrologicalSign->getId())
            ->getQuery()
            ->getResult()
        ;

        if (empty($horoscopes)) {
//            throw new \Exception('HIBA: The $horoscopes array is empty !');
            throw new NotFoundHttpException('HIBA: The $horoscopes array is empty!');
        }

        $horoscope = $horoscopes[0];
        $tegezosCsillagjegy = $this->fetchHoroscope($horoscope->getContent(), $locale);  // ChatGPT

        $base = $tegezosCsillagjegy;
        $addendum = '';

        if (!WordingAndConfig::IS_SINGLE) {
            $split = $this->splitText($tegezosCsillagjegy);
            $base = $split[0];
            $addendum = $split[1];
        }

        $base = ""
//            .WordingAndConfig::PREFIX.PHP_EOL
            .$base//.PHP_EOL
//            .WordingAndConfig::POSTFIX
            ;

        $base = preg_replace('/([.?!])\s+/', "$1\n\n", $base);
        $addendum = preg_replace('/([.?!])\s+/', "$1\n\n", $addendum);

        $baseHoroscope = $em->getRepository(HoroscopeFinal::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->andWhere('e.astrologicalSign = :sign')
            ->setParameter(':sign', $astrologicalSign->getId())
            ->andWhere('e.type = :type')
            ->setParameter(':type', 'base')
            ->getQuery()
            ->getResult()
        ;
        if (!empty($baseHoroscope)) {
            $baseHoroscope = $baseHoroscope[0];
        }
        if (!$baseHoroscope) {
            $baseHoroscope = new HoroscopeFinal();
            $baseHoroscope->setDate($date);
            $baseHoroscope->setLocale($locale);
            $baseHoroscope->setType('base'); // base and addendum
            $baseHoroscope->setAstrologicalSign($astrologicalSign);
        }
        $baseHoroscope->setContent($base);
        $em->persist($baseHoroscope);

        $addendumHoroscope = $em->getRepository(HoroscopeFinal::class)->createQueryBuilder('e')
            ->where('e.date LIKE :date')
            ->setParameter(':date', $date->format(Enums::DATE_FORMAT)."%") // LIKE '2023-04-21%'
            ->andWhere('e.locale = :locale')
            ->setParameter(':locale', $locale)
            ->andWhere('e.astrologicalSign = :sign')
            ->setParameter(':sign', $astrologicalSign->getId())
            ->andWhere('e.type = :type')
            ->setParameter(':type', 'addendum')
            ->getQuery()
            ->getResult()
        ;
        if (!empty($addendumHoroscope)) {
            $addendumHoroscope = $addendumHoroscope[0];
        }
        if ($addendum) {
            if (!$addendumHoroscope) {
                $addendumHoroscope = new HoroscopeFinal();
                $addendumHoroscope->setDate($date);
                $addendumHoroscope->setLocale($locale);
                $addendumHoroscope->setType('addendum'); // base and addendum
                $addendumHoroscope->setAstrologicalSign($astrologicalSign);
            }
            $addendumHoroscope->setContent($addendum);
            $em->persist($addendumHoroscope);
        }

        $em->flush();

        return $this->redirectToRoute('show-rewritten-sign', [
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'sign' => $sign,
            'locale' => $locale,
        ]);
    }

    /**
     * @Route("/list", name="site-list-signs")
     */
    public function listSigns()
    {
        $date = (new \DateTime('today'));
        return $this->render('tegezos-horoszkop.twig', [
            'signs' => Enums::ZODIAC_SIGNS,
            'date' => $date,
        ]);
    }

    /**
     * @Route("/fetch-horoscope", name="site-fetch-horoscope")
     */
    public function fetchHoroscope($horoscopeText, $locale)
    {

        if ($horoscopeText == "" || !$horoscopeText) {
            $horoscopeText = "Ön ezt a szöveget olvassa éppen.";
        }

//        return "Ne hagyd ki az ajánlatot csak azért, mert ismeretlen területen kellene dolgoznod. Gondold át, milyen tapasztalataidat tudod felhasználni, és kikhez fordulhatsz tanácsért, ha elakadsz. Légy nyitott és tekintsd ezt a feladatot lehetőségként, ahol sokat tanulhatsz, és olyan tudást szerezhetsz, amit csak a gyakorlatban lehet megszerezni. Az alkalom most adott neked, hogy továbbléphess. Ne félj a kihívástól, hanem vedd fel a kesztyűt, és légy nyitott az új lehetőségekre. Ha bátorságot mutatsz, akkor olyan dolgokat érhetsz el, amiket korábban nem is gondoltál volna. Az új területen való munka lehetőséget ad arra, hogy bővítsd a tudásodat és fejleszd a képességeidet. Ne hagyd ki ezt az alkalmat, mert ez lehet az újabb lépés a karrieredben.";

        $apiKey = $_ENV['CGPT_KEY'];

        $prompt = sprintf(
" 
Lépés 1: A háromszoros idézőjelekkel határolt szöveget írd át tegezőbe, azaz használj tegező formát.
Lépés 2: Az előző lépésben kinyert szöveg alapján írj egy összefoglalót. Ez az összefoglaló is legyen tegező, majd kérlek fűzd hozzá az előző szöveghez.
Mind a két lépés eredményét mutasd meg. 
Íme a szöveg: 
\"\"\"%s\"\"\"
", $horoscopeText);

//        $prompt = sprintf(
//"
//
//Kérlek fogalmazd át ugyanazon a nyelven az alábbi szöveget.
//Legyen részletesebb az eredetinél és tegeződjél benne.
//Amikor újra írod a szöveget figyelj arra, hogy a teljes hossza majd ne haladja meg az 1000 karaktert!
//Az is fontos, hogy az újraírt szöveg kötelezően legalább 8 mondatot tartalmazzon, de ne többet 10 mondatnál.
//Az újrafogalmazáskor, használj tegező formát, kijelentő mód jelen idő egyes szám 2. személy határozott ragozásban. Hadd adjak erre, egy példát:
//
//>>Ha fáradtan ébredsz, nem kell erőltetni semmit.
//
//Engedd, hogy az árral sodródj, és találj remek programokat. Lazítsál most egy kicsit!
//
//Ne támassz elvárásokat magaddal szemben, csak figyelj a szeretteidre, és ne idegeskedj körülöttük.
//
//Ha pedig kedveskedni tudsz nekik, akkor az otthonod is békésebb és meghittebb lesz.<<
//
//További segítség gyanánt adok egy másik példát. Ez volt az eredeti szöveg:
//>>Át kell lendülnie a holtponton, onnantól már minden könnyedén megy majd. Addig azonban rögös út vezet, ráadásul a környezetében élőkkel sem találja a közös hangot.
//Végül jön egy olyan eredmény, amely még Önt is meglepi és már képes hinni a további sikerekben.
//A kitartása elnyeri a jutalmát, mert minden olyan célját eléri végül, melyet erre a napra betervezett.
//Valóban csak a kezdet volt nehéz.<<
//
//Ez lett az átírás után:
//>>Át kell lendülnöd a holtponton és onnantól már minden könnyedén megy majd.
//
//Addig rögös út vár rád, nem lesz minden sima, és talán nehézségekbe ütközöl az emberekkel való kommunikációban is.
//
//Azonban az eredmény végül meglepő lesz, és képes leszel hinni a további sikerekben.
//
//Ha kitartó vagy, akkor elnyered a jutalmadat.
//
//Ne feledd, hogy csak a kezdet volt nehéz, és ha kitartasz, akkor elérheted mindazokat a célokat, amiket erre a napra tűztél ki.
//
//Soha ne add fel, és bízz magadban!<<
//
//Íme a szöveg:
//>>%s<<
//", $horoscopeText);

        if ($locale == 'ro') {
            $prompt = sprintf(
"
Pasul 1: Rescrie textul de mai jos delimitat de ghilimelele triple într-o formă de tutuit, adică folosește forma de adresare 'tu'.
Pasul 2: Lipește la textul obținut în pasul anterior un rezumat scris pe baza acestuia, rezumatul să fie, de asemenea, tutuit, adică în formă de adresare 'tu'.
Iată textul: 
\"\"\"%s\"\"\"
                ", $horoscopeText);
        }

        if (WordingAndConfig::IS_SINGLE) {
//            Az újrafogalmazáskor, használj tegező formát, kijelentő mód jelen idő egyes szám 2. személy határozott ragozásban.
//            Kérlek írd át tegezősbe az alábbi szöveget. Használj tegező formát.
//            Az alábbi szöveget eredeti nyelvén kérlek írd át tegező formát használva.
//            Fogd az alábbi szöveget, állapítsd meg a nyelvét, majd kérlek írd át tegezősbe ezen a nyelven. Használj tegező formát. Nekem csak az átírt szöveg kell, minden mást szedjél ki belőle.


            // Csak FORTUNA:
            // Kérlek írd át tegezőbe az alábbi szöveget, azaz használj tegező formát. Egyes szavak helyett használj szinonímákat.
            // Kérlek írd át tegezőbe az alábbi szöveget (Ön -> te, Önök -> ti), azaz használj tegező formát, az igéket ennel megfelelően ragozd. Néhány főnév és ige helyett használj hasonló jellentéssel bíró szinonímákat.


            $prompt = sprintf(
                "
                        
                        Te egy asztrológus vagy és leírod nekem, tegezősen a napi horoszkópomat. A napi horoszkóp szövege adott, lásd lent, ezt a szöveget fogod nekem leírni tegezősen. Az asztrológus soha nem része az elmondott szövegnek, nem része a horoszkópomnak, ezért zárd ki az ilyeneket: \"velem\", \"nekem\", \"velünk\", \"nekünk\", \"vagyunk\", \"igyekszünk\" stb.     
                        Íme a szöveg: 
                        >>%s<<    
                        ", $horoscopeText);

            if ($locale == 'ro') {
                $prompt = sprintf(
                    "
                        Te rog să rescrii următorul text tutuind, adică la persoana a doua. În loc de Dvs. folosește tu, și adaptează conjugarea la asta, iar în loc de 'să îți' folosește versiunea scurtă 'să-ți'.   
                        Iată textul: 
                        >>%s<<    
                        ", $horoscopeText);
            }


        }

//        dd($prompt);

        // Írd újra az alábbi szöveget részletesebben, kb. 600 és 700 karakter hosszú legyen és tegező formát használj.
        // Írd át a lenti szöveget tegezősre, továbbá gondoskodj arról is, hogy körülbelül kétszer hosszabb legyen az eredetinél. Második személy egyes számban írd meg.

        $client = new Client(['headers' => ['Authorization' => 'Bearer '.$apiKey]]);

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'json' => [
                'model'=> 'gpt-3.5-turbo',
                'messages'=> [
                    [
                        "role" => "user",
                        "content" => $prompt
                    ]
                ],
//                'max_tokens' => 50,
                'temperature' => 0.5,
                'n' => 1,
                'stop' => ['\n']
            ]
        ]);

        $result = json_decode($response->getBody(), true);

        $rewrittenText = $result['choices'][0];
        $rewrittenText = $rewrittenText['message']['content'];

        return $rewrittenText;
    }

    private function splitText($text) {
        $sentences = preg_split('/(?<=[.?!])\s+/', $text);
        $totalSentences = count($sentences);
        $halfSentences = round($totalSentences / 1.9);
        $first = implode(' ', array_slice($sentences, 0, $halfSentences));
        $second = implode(' ', array_slice($sentences, $halfSentences));
        return array($first, $second);
    }
}
