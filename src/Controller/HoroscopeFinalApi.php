<?php

namespace App\Controller;

use App\Entity\Dating;
use App\Entity\Enums;
use App\Entity\HoroscopeBundled;
use App\Entity\HoroscopeFinal;
use App\Entity\HoroscopeRaw;
use App\Entity\ErrorEntity;
use App\Serializer\AstrologicalSignDenormalizer;
use App\Serializer\HoroscopeBundledDenormalizer;
use App\Serializer\HoroscopeBundledNormalizer;
use App\Serializer\HoroscopeFinalDenormalizer;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class HoroscopeFinalApi extends AbstractController
{
    /**
     * @Route("/api/horoscope-final/", name="api-horoscopeFinal", methods={"GET"})
     */
    public function showDates(SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $finalHoroscopes = $em->getRepository(HoroscopeFinal::class)->createQueryBuilder('e')
            ->select('e.date')
            ->groupBy('e.date')
            ->orderBy('e.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        $json = $serializer->serialize($finalHoroscopes, 'json', [
            'groups' => 'horoscopeList',
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d',
        ]);

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * @Route("/api/horoscope-final/{date}/{locale}", name="api-horoscopeFinal-date", methods={"GET"})
     */
    public function showRewritten($date, $locale, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $date = \DateTime::createFromFormat(Enums::DATE_FORMAT, $date);

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
//        dd($baseHoroscopes);

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

        $bundledHoroscopes = [];

        foreach ($baseHoroscopes as $sign => $content) {
            $bundled = new HoroscopeBundled();
            $bundled->setDate($content->getDate());
            $bundled->setLocale($content->getLocale());
            $bundled->setAstrologicalSign($content->getAstrologicalSign());
            $bundled->setBase($content);
            if (!empty($addendumHoroscopes)) {
                foreach ($addendumHoroscopes as $index => $addendum) {
                    if ($content->getAstrologicalSign()->getSlug() == $addendum->getAstrologicalSign()->getSlug()) {
                        $bundled->setAddendum($addendum);
                    }
                }
            }
            $bundled->setRaw($rawHoroscopes[$sign]);

            $bundledHoroscopes[$sign] = $bundled;
        }
//        dd('exit');

//        dd($bundledHoroscopes);

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizers = [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            new ObjectNormalizer($classMetadataFactory,null,null, new PhpDocExtractor()),

        ];
        $serializer = new Serializer($normalizers, [new JsonEncoder()]);

        $json = $serializer->serialize($bundledHoroscopes, 'json', [
            'groups' => ['bundled'],
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d',
        ]);

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * @Route("/api/horoscope-bundled/", name="api-horoscopeBundled-update", methods={"PUT", "OPTIONS"})
     */
    public function updateHoroscope(Request $request, ValidatorInterface $validator, EntityManagerInterface $em, SerializerInterface $serializer) //array $bundledHoroscopes,
    {

//        $locale = 'ro';
        $locale = 'hu';
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new BadRequestHttpException('Invalid JSON');
        }

//        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
//        $normalizers = [
//            new DateTimeNormalizer(),
////            new HoroscopeBundledNormalizer($em),
////            new ObjectNormalizer($classMetadataFactory,null,null, new PhpDocExtractor()),
//            new HoroscopeBundledDenormalizer($em),
//            new HoroscopeFinalDenormalizer($em),
//            new AstrologicalSignDenormalizer($em),
//            new ArrayDenormalizer(),
//        ];
//        $serializer = new Serializer($normalizers, [new JsonEncoder()]);


        $bundled = $serializer->deserialize($request->getContent(),HoroscopeBundled::class,'json',[
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d',
//            AbstractNormalizer::OBJECT_TO_POPULATE => $bundled,
//            AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
//            'skip_null_values' => true,
        ]);
//        dd($bundled);
//        dd($bundled->getBase());

        $errors = $this->getValidationErrors($bundled->getBase(), $validator);
        if (!empty($errors)) {
            return new JsonResponse(json_encode($errors), 422, [],true);
        }

        $em->persist($bundled->getBase());
        if ($bundled->getAddendum()) {
            $em->persist($bundled->getAddendum());
        }
        $em->flush();

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizers = [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            new ObjectNormalizer($classMetadataFactory,null,null, new PhpDocExtractor()),

        ];
        $serializer = new Serializer($normalizers, [new JsonEncoder()]);

        $json = $serializer->serialize($bundled, 'json', [
            'groups' => ['bundled'],
            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d',
        ]);

        return new JsonResponse($json, 200, [], true);
    }





    /**
     * @Route("/api/show/{id}", name="api-horoscopeFinal-get", methods={"GET"})
     */
    public function show($id, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $post = $em->getRepository(HoroscopeFinal::class)->find($id);

        $json = $serializer->serialize($post, 'json', ['groups' => 'horoscopeList']);

        return new JsonResponse($json, 200, [], true);
    }


    /**
     * Returns an associative array of validation errors
     *
     * @param mixed $object
     * @return array            Array of errors, empty otherwise
     */
    protected function getValidationErrors($object, ValidatorInterface $validator) //, $constraints = null
    {
        $violations = $validator->validate($object);
        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $error = new ErrorEntity($violation->getPropertyPath(), $violation->getMessage());
                $errors[] = $error;
            }
            return $errors;
        }
        return [];
    }
}
