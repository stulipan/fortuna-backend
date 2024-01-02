<?php

namespace App\Controller;

use App\Entity\Enums;
use App\Entity\HoroscopeBundled;
use App\Entity\HoroscopeFinal;
use App\Entity\HoroscopeText;
use App\Entity\HoroscopeTextPublished;
use App\Entity\PublishDate;
use App\Entity\Tag;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OperationController extends AbstractController
{
    const PREFIXES = [
        'Remélem jól indult a napod, kedves barátom!',
        'Remélem pihentető hétvégéd volt, kedves barátom!',
        'Meghoztam a mai Horoszkópodat!',
        'Megérkezett a mai horoszkópod!',
        'Szia kedves {{_yourname}}!',
        'Remélem jól indul a hétvégéd!',
        'Remélem jól telik a hétvégéd!',
        'Remélem jól indul a hétvégéd, kedves barátom!',

//        'Üdvözöllek, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Szia, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Szia, kedves {{cuf_9284767|fallback:"barátom"}}! Meghoztam a mai üzeneted.',
//        'Hogy vagy, kedves {{cuf_9284767|fallback:"barátom"}}? Meghoztam a mai horoszkópod.',
//        'Szép reggelt, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Szép jó reggelt, {{cuf_9284767|fallback:"barátom"}}, itt is van a mai horoszkópod!',
//        'Jó reggelt, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Vidám reggelt, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Remélem jól vagy, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Remélem jól indul a napod, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Remélem jól indult a napod, kedves {{cuf_9232715|fallback:"barátom"}}!',
//        'Hogy vagy ma reggel, kedves {{cuf_9284767|fallback:"barátom"}}?',
//        'Hogy vagy ma reggel, kedves {{cuf_9284767|fallback:"barátom"}}? Íme a horoszkópod!',
//        'Meghoztam a mai üzeneted, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Ma is meghoztam a Horoszkópod, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Meg is hoztam a mai Horoszkópod, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Itt a legújabb horoszkópod, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Itt a mai horoszkópod, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Meghoztam a mai Horoszkópodat, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Megérkezett a mai horoszkópod, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Meg is hoztam a hétfői Horoszkópod, {{cuf_9284767|fallback:"barátom"}}!',
//        'Meg is hoztam a keddi Horoszkópod, {{cuf_9284767|fallback:"barátom"}}!',
//        'Meg is hoztam a szerdai Horoszkópod, {{cuf_9284767|fallback:"barátom"}}!',
//        'Meg is hoztam a csütörtöki Horoszkópod, {{cuf_9284767|fallback:"barátom"}}!',
//        'Meg is hoztam a pénteki Horoszkópod, {{cuf_9284767|fallback:"barátom"}}!',
//        'Meg is hoztam a szombati Horoszkópod, {{cuf_9284767|fallback:"barátom"}}!',
//        'Meg is hoztam a vasárnapi Horoszkópod, {{cuf_9284767|fallback:"barátom"}}!',
//        'Szép szombati reggelt, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Remélem jól indul a hétvégéd, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Szép vasárnap reggelt, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Remélem pihentető hétvégéd volt, kedves {{cuf_9284767|fallback:"barátom"}}!',
//        'Hogy telt a hétvégéd, kedves {{cuf_9284767|fallback:"barátom"}}? Remélem jól!',
    ];

    const POSTFIXES = [
        '(A folytatáshoz nyomj a Tovább gombra!)',
//        '(Kérlek nyomj a gombra vagy írj ide valamit, mert csak így tudom biztosan elküldeni a holnapi horoszkópot!)',
//        '(Kérlek nyomj a gombra vagy írj ide valamit, mert csak így tudjuk biztosan elküldeni a holnapi horoszkópot!)',
//        '(Ahhoz, hogy el tudjam biztosan küldeni a holnapi horoszkópot, kérlek nyomj a gombra vagy írj ide valamit.)',
//        '(Kérlek írj ide valamit vagy nyomj a gombra, hogy holnap is biztosan el tudjam küldeni a horoszkópod!)',
//        '(Kérlek írj ide valamit vagy nyomj a gombra, hogy holnap biztosan el tudjam küldeni a horoszkópod!)',
//        '(Kérlek nyomj a gombra vagy írj ide valamit, hogy holnap is biztosan el tudjam küldeni a horoszkópod!)',
//        '(Kérlek nyomj a gombra vagy írj ide valamit, hogy holnap is el tudjam küldeni a horoszkópod!)',

    ];
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @ Route("/operation", name="site-operation")
     */
    public function operation(): Response
    {
        $locale = 'hu';

//        $horoscopes = $this->em
//            ->getRepository(HoroscopeFinal::class)
//            ->createQueryBuilder('hf')
//            ->where('hf.locale = :locale')
//            ->andWhere('hf.type = :type')
//            ->andWhere('hf.date > :targetDate')
//            ->setParameter('locale', 'hu')
//            ->setParameter('type', 'base')
//            ->setParameter('targetDate', (\DateTime::createFromFormat('Y-m-d', '2023-09-16'))->setTime(0,0,0))
//            ->orderBy('hf.astrologicalSign', 'ASC')
//            ->orderBy('hf.date', 'ASC')
//            ->getQuery()
//            ->getResult();
//
//        dd($horoscopes);

//        $cleanedHoroscopes = [];
//        foreach ($horoscopes as $horoscope) {
//            $originalContent = $horoscope->getContent();
//            $cleanedContent = $this->cleanHoroscopeContent($originalContent);
//
//            // Update the entity with the cleaned content
//            $horoscope->setContent($cleanedContent);
//            $this->em->persist($horoscope);
//            $this->em->flush();
//            $cleanedHoroscopes[] = $horoscope;
//        }


        // A CLEAN HOROSCOPE CONTENT nem kell, mert a prefix csak a legelejen volt beleegetve a HoroscopeFinal-be, kesobb mar nem.
        // Ami itt kezdodik, ezt kell lefuttatni (!!!).

        $baseHoroscopes = $this->em
            ->getRepository(HoroscopeFinal::class)
            ->createQueryBuilder('hf')
            ->where('hf.locale = :locale')
            ->andWhere('hf.type = :type')
            ->andWhere('hf.date > :targetDate')
            ->setParameter('locale', 'hu')
            ->setParameter('type', 'base')
            ->setParameter('targetDate', (\DateTime::createFromFormat('Y-m-d', '2023-09-16'))->setTime(0,0,0))
            ->orderBy('hf.astrologicalSign', 'ASC')
            ->orderBy('hf.date', 'ASC')
            ->getQuery()
            ->getResult();

        $addendumHoroscopes = $this->em
            ->getRepository(HoroscopeFinal::class)
            ->createQueryBuilder('hf')
            ->where('hf.locale = :locale')
            ->andWhere('hf.type = :type')
            ->andWhere('hf.date > :targetDate')
            ->setParameter('locale', 'hu')
            ->setParameter('type', 'addendum')
            ->setParameter('targetDate', (\DateTime::createFromFormat('Y-m-d', '2023-09-16'))->setTime(0,0,0))
            ->orderBy('hf.astrologicalSign', 'ASC')
            ->orderBy('hf.date', 'ASC')
            ->getQuery()
            ->getResult();

        $bundledHoroscopes = [];
        foreach ($baseHoroscopes as $sign => $base) {
            $bundled = new HoroscopeBundled();
            $bundled->setDate($base->getDate());
            $bundled->setLocale($base->getLocale());
            $bundled->setAstrologicalSign($base->getAstrologicalSign());
            $bundled->setBase($base);

            if (!empty($addendumHoroscopes)) {
                foreach ($addendumHoroscopes as $index => $addendum) {
                    if ($base->getAstrologicalSign()->getSlug() == $addendum->getAstrologicalSign()->getSlug() &&
                        $base->getDate()->format(Enums::DATE_FORMAT) === $addendum->getDate()->format(Enums::DATE_FORMAT)) {

                        $bundled->setAddendum($addendum);
                    }
                }
            }

            $bundledHoroscopes[] = $bundled;
        }

        $horoscopeTextList = [];
        foreach($bundledHoroscopes as $bundled) {
            $horoscopeText = new HoroscopeText();
            $horoscopeText->setLocale($bundled->getLocale());
            $horoscopeText->setBase($bundled->getBase()->getContent());
            if ($bundled->getAddendum()) {
                if ($bundled->getAddendum()->getContent() === null || trim($bundled->getAddendum()->getContent()) == '') {
                    $horoscopeText->setAddendum(null);
                } else {
                    $horoscopeText->setAddendum($bundled->getAddendum()->getContent());
                }
            }

            // persist
            $this->em->persist($horoscopeText);

            $published = new HoroscopeTextPublished();
            $published->setHoroscopeText($horoscopeText);
            $published->setAstrologicalSign($bundled->getAstrologicalSign());
            $published->setPublishDate($bundled->getDate());

            // persist
            $this->em->persist($published);
            $this->em->flush();

//            $horoscopeTextList[] = $horoscopeText;
        }
//        dd($horoscopeTextList);


        $horoscopeTextList =  $this->em
            ->getRepository(HoroscopeText::class)
            ->findBy(['locale' => 'hu'])
        ;


        return $this->render('operation-show-all.html.twig', [
//            'horoscopes' => $horoscopes,
//            'horoscopes' => $cleanedHoroscopes,
//            'horoscopes' => $bundledHoroscopes,
            'horoscopes' => $horoscopeTextList,
        ]);
    }

    /**
     * @ Route("/operation-addendum-tag", name="site-operation-addendumTag")
     */
    public function addAddendumTag(): Response
    {
        // Get all HoroscopeText entities
        $horoscopeTexts = $this->em->getRepository(HoroscopeText::class)->findAll();

        $array = [];
        $addendumTag = $this->em->getRepository(Tag::class)->findOneBy(['name' => 'addendum']);
        foreach ($horoscopeTexts as $horoscopeText) {
            $addendumContent = $horoscopeText->getAddendum();

            if ($addendumContent === null || trim($addendumContent) === '') {
                // If addendum content is null or empty, set it to null
//                $horoscopeText->setAddendum(null);
            }
            else {
                $tags = $horoscopeText->getTags();

                // Check if the addendum tag is not already associated
                $hasTag = false;
                foreach ($tags as $tag) {
                    if ($tag->getId() === $addendumTag->getId()) {
                        $hasTag = true;
                        break;
                    }
                }

                if (!$hasTag) {
                    $horoscopeText->addTag($addendumTag);
                    $this->em->persist($horoscopeText);
                    $array[] = $horoscopeText;
                }
            }
        }

//        dd($array);
        $this->em->flush();

        return new Response('all good');
    }


//    /**
//     * @Route("/operation2", name="site-operation2")
//     */
//    public function updateAddendumFields(): Response
//    {
//        // Get all HoroscopeText entities
//        $horoscopeTexts = $this->em->getRepository(HoroscopeText::class)->findAll();
//
//        foreach ($horoscopeTexts as $horoscopeText) {
//            $addendumContent = $horoscopeText->getAddendum();
//
//            if ($addendumContent === null || trim($addendumContent) === '') {
//                // If addendum content is null or empty, set it to null
//                $horoscopeText->setAddendum(null);
//            }
//        }
//
//        // Persist the changes to the database
//        $this->em->flush();
//
//        return $this->redirectToRoute('site-operation'); // Replace with an appropriate route
//    }


    private function cleanHoroscopeContent(string $base): string
    {
        $cleanedContent = $base;

        // Loop through the prefixes and remove them
        foreach (self::PREFIXES as $prefix) {
            if (strpos($cleanedContent, $prefix) === 0) {
                // Remove the prefix
                $cleanedContent = substr($cleanedContent, strlen($prefix));
                break; // Stop after removing the first matching prefix
            }
        }

        foreach (self::POSTFIXES as $postfix) {
            if (strpos($cleanedContent, $postfix) !== false) {
                $cleanedContent = str_replace($postfix, '', $cleanedContent);
            }
        }

        // Trim leading and trailing whitespace
        $cleanedContent = trim($cleanedContent);

        return $cleanedContent;
    }
}
