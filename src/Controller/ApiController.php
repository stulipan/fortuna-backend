<?php

namespace App\Controller;

use App\Entity\ApiError;
use App\Entity\AstrologicalSign;
use App\Entity\Enums;
use App\Entity\HoroscopeBundled;
use App\Entity\HoroscopeFinal;
use App\Entity\HoroscopeRaw;
use App\Entity\EntityValidationError;
use App\Entity\HoroscopeText;
use App\Entity\HoroscopeTextPublished;
use App\Entity\Tag;
use App\Serializer\AstrologicalSignDenormalizer;
use App\Serializer\HoroscopeTextsPublishedDenormalizer;
use App\Serializer\TagDenormalizer;
use App\Services\StulipanPaginator;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use PHPUnit\Framework\MockObject\Api;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends StulipanBaseController
{
    private $em;
    private $encoders;
    private $normalizers;
    private $serializer;
    private $validator;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->em = $entityManager;

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $encoders = [new JsonEncoder()];
        $normalizers = [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            new ObjectNormalizer($classMetadataFactory,null,null, new PhpDocExtractor()),
        ];

        $this->encoders = $encoders;
        $this->normalizers = $normalizers;

        $serializer = new Serializer($normalizers, $encoders);
        $this->serializer = $serializer;

        $this->validator = $validator;
    }

    /**
     * GET COUNT: Retrieve a count of texts
     *
     * @Route("/api/horoscope-texts/count/", name="api-horoscopeTexts-getCount", methods={"GET"})
     */
    public function horoscopeTextCount(Request $request)
    {
        $locale = $request->query->get('locale', 'hu');

        $dateString = $request->query->get('publishDate', null);
        if ($dateString) {
            $publishDate = $this->createDateFromFormat($dateString);
        }

        $tagString = $request->query->get('tag', null);

        $qb = $this->em
            ->getRepository(HoroscopeText::class)
            ->createQueryBuilder('h')

            ->andWhere('h.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('h.id', 'DESC')
        ;

        if (isset($publishDate)) {
            $qb
                ->leftJoin('h.horoscopeTextsPublished', 'htp')
                ->andWhere('htp.publishDate = :publishDate')
                ->setParameter('publishDate', $publishDate)
            ;
        }

        if ($tagString) {
            $qb
                ->leftJoin('h.tags', 't')
                ->andWhere('t.name = :tag')
                ->setParameter('tag', $tagString)
            ;
        }

        $count = (int)$qb->select('COUNT(h.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return new JsonResponse(['count' => $count], 200, []);

//        if ($dateString) {
//            $publishDate = \DateTime::createFromFormat(Enums::DATE_FORMAT, $dateString);
//            if ($publishDate instanceof \DateTime) {
//                $publishDate->setTime(0, 0, 0);
//            }
//
//            $qb = $this->em
//                ->getRepository(HoroscopeText::class)
//                ->createQueryBuilder('h')
//                ->leftJoin('h.horoscopeTextsPublished', 'htp')
//                ->andWhere('htp.publishDate = :publishDate')
//                ->setParameter('publishDate', $publishDate)
//                ->andWhere('h.locale = :locale')
//                ->setParameter('locale', $locale)
//            ;
//            $count = (int)$qb->select('COUNT(h.id)')
//                ->getQuery()
//                ->getSingleScalarResult()
//            ;
//        } else {
//            $count = $this->em->getRepository(HoroscopeText::class)
//                ->count(['locale' => $locale]);
//        }
//
//        $json = $this->serializer->serialize(['count' => $count], 'json');
//        return new JsonResponse($json, 200, [], true);
    }

    /**
     * GET SINGLE: Retrieve a single text
     *
     * @Route("/api/horoscope-texts/{textId}", name="api-horoscopeTexts-getSingle", methods={"GET"})
     */
    public function horoscopeTextGetSingle($textId)
    {
        $horoscopeText = $this->em->getRepository(HoroscopeText::class)
            ->findBy(['id' => $textId])
        ;

        if (!$horoscopeText) {
            $errorResponse = [
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => 'A keresett horoszkópszöveget nem találta.',
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 404, [], true);
        }

        $json = $this->serializer->serialize($horoscopeText, 'json', [
            'groups' => ['horoscopeText', 'horoscopeTextPublished'],
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);
        return new JsonResponse($json, 200, [], true);
    }

    /**
     * GET LIST: Retrieve a list of horoscope texts (HoroscopeText)
     * Parameters:
     *      ?locale=&page=&pageSize=
     *      ?publishDate=
     *      ?tag=
     *
     * @Route("/api/horoscope-texts/", name="api-horoscopeTexts-getList", methods={"GET"})
     *
     */
    public function horoscopeTextGetList(Request $request, StulipanPaginator $paginator)
    {
        $defaultPageSize = 20;
        $locale = $request->query->get('locale', 'hu');

//        $page = $request->query->getInt('page', 1);
        $page = $request->query->get('page');
        $pageSize = $request->query->get('pageSize', $defaultPageSize);

        $offset = 0;
        if ($page) {
            $offset = ($page-1) * $pageSize;
        }

        $dateString = $request->query->get('publishDate', null);
        if ($dateString) {
            $publishDate = \DateTime::createFromFormat(Enums::DATE_FORMAT, $dateString);
            if ($publishDate instanceof \DateTime) {
                $publishDate->setTime(0, 0, 0);
            }
        }

        $tagString = $request->query->get('tag', null);

        $qb = $this->em
            ->getRepository(HoroscopeText::class)
            ->createQueryBuilder('h')

            ->andWhere('h.locale = :locale')
            ->setParameter('locale', $locale)
        ;

        if (isset($publishDate)) {
            $qb
                ->leftJoin('h.horoscopeTextsPublished', 'htp')
                ->andWhere('htp.publishDate = :publishDate')
                ->setParameter('publishDate', $publishDate)
                ->addOrderBy("htp.astrologicalSign", "ASC")
            ;
        }

        if ($tagString) {
            $qb
                ->leftJoin('h.tags', 't')
                ->andWhere('t.name = :tag')
                ->setParameter('tag', $tagString)
            ;
        }

        $qb->addOrderBy('h.id', 'DESC');

//        if (!isset($publishDate)) {
//            $qb->orderBy('h.id', 'DESC');
//        }

        if ($page) {
            if ($pageSize !== null) {
                $qb->setMaxResults($pageSize);
            }

            if ($offset !== null) {
                $qb->setFirstResult($offset);
            }
        }

        $horoscopeTexts = $qb->getQuery()->getResult();



//        // Query or Query Builder
//        // NOT RESULTS!
//        $query = $this->em
//            ->getRepository(HoroscopeText::class)
////            ->findAllQuery($locale)
//            ->findByQuery(['locale' => $locale])
//        ;
//        $paginator->paginate($query, $request->query->getInt('page', 1), $pageSize);
//        $horoscopeTexts = $paginator->getItems();

//        dump($paginator->getTotal());
//        dump($paginator->getLastPage());
//        dump($paginator->getItems());
//        dd($paginator);

        if (empty($horoscopeTexts)) {
            $errorResponse = [
                'error' => 'Resources not found.',
                'message' => 'Nem talált horoszkópszövegeket.',
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 404, [], true);
        }

        $json = $this->serializer->serialize($horoscopeTexts, 'json', [
            'groups' => 'horoscopeText',
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * POST SINGLE: Create a new HoroscopeText
     *
     * @Route("/api/horoscope-texts/", name="api-horoscopeTexts-postSingle", methods={"POST"})
     */
    public function textsPostSingle(Request $request, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        /** @var HoroscopeText $newHoroscopeText */
        $newHoroscopeText = $this->serializer->deserialize($request->getContent(),HoroscopeText::class,'json',[
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);

        $validationErrors = $this->getValidationErrors($newHoroscopeText, $validator);
        if (!empty($validationErrors)) {
            $errorResponse = [
                'error' => 'Object validation error',
                'message' => 'Az új horoszkópszöveg hibás.',
                'object' => $validationErrors
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 422, [],true);
        }

        $this->em->persist($newHoroscopeText);
        $this->em->flush();

        $json = $this->serializer->serialize($newHoroscopeText, 'json', [
            'groups' => 'horoscopeText',
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);
        return new JsonResponse($json, 200, [], true);
    }

    /**
     * PUT SINGLE: Updates a single text
     *
     * @Route("/api/horoscope-texts/{textId}", name="api-horoscopeTexts-putSingle", methods={"PUT"})
     */
    public function putSingleHoroscopeText($textId, Request $request, ValidatorInterface $validator)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            $errorResponse = [
                'error' => 'Unprocessable entity.',
                'message' => 'Hibás JSON-adatok vagy formátum.',
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 422, [], true);
        }

        $horoscopeText = $this->em->getRepository(HoroscopeText::class)
            ->find($textId)
        ;

        if (!$horoscopeText) {
            $errorResponse = [
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => 'A frissíteni kívánt horoszkópszöveget nem találta.',
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 404, [], true);
        }

        /** @var HoroscopeText $newHoroscopeText */
        $newHoroscopeText = $this->serializer->deserialize($request->getContent(),HoroscopeText::class,'json',[
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);

        $validationErrors = $this->getValidationErrors($newHoroscopeText, $validator);
        if (!empty($validationErrors)) {
            $errorResponse = [
                'error' => 'Object validation error',
                'message' => 'Az új horoszkópszöveg hibás.',
                'object' => $validationErrors
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 422, [],true);
        }

        $horoscopeText->setBase($newHoroscopeText->getBase());
        $horoscopeText->setAddendum($newHoroscopeText->getAddendum());

        $this->em->persist($horoscopeText);
        $this->em->flush();

        $json = $this->serializer->serialize($horoscopeText, 'json', [
            'groups' => 'horoscopeText',
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);
        return new JsonResponse($json, 200, [], true);
    }

    /**
     * DELETE SINGLE: Delete a single text
     *
     * @Route("/api/horoscope-texts/{textId}", name="api-horoscopeTexts-deleteSingle", methods={"DELETE"})
     */
    public function deleteSingleHoroscopeText($textId, Request $request, ValidatorInterface $validator)
    {
        $data = $this->em->getRepository(HoroscopeText::class)->find($textId);
        if (!$data) {
            return $this->jsonErrorResponse([
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => 'A törölni kívánt horoszkópszöveg nem található.',
            ], 404);
        }

        $allowRemoval = false;  // Use it to remove horoscopeTexts at will!

        $horoscopeTextPublishedList = $data->getHoroscopeTextsPublished();
        if (!$horoscopeTextPublishedList->isEmpty()) {
            $canBeDeleted = false;
        } else {
            $canBeDeleted = true;
        }

        if ((!isset($canBeDeleted) || !$canBeDeleted) && $allowRemoval) {
            $canBeDeleted = true;
        }

        if (!$canBeDeleted) {
            return $this->jsonErrorResponse([
                'error' => ApiError::UNPROCESSABLE_ENTITY,
                'message' => 'A horoszkópszöveg nem törölhető, mivel már rendelkezik publikálásokkal.',
            ], 422);
        }

        $this->em->remove($data);
        $this->em->flush();
        return new JsonResponse('', 204, [], true);
    }

    /**
     * GET LIST: Retrieve a list of AstrologicalSign items
     *
     * @Route("/api/astrological-signs/", name="api-astrologicalSigns-getList", methods={"GET"})
     */
    public function astrologicalSignsGetList(Request $request)
    {
        $signs = $this->em
            ->getRepository(AstrologicalSign::class)
            ->findAll()
        ;

        if (empty($signs)) {
            $errorResponse = [
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => 'Nem talált csillagjegyeket.',
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 404, [], true);
        }

        $json = $this->serializer->serialize($signs, 'json', [
            'groups' => 'astrologicalSign',
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * POST SINGLE: Create a new HoroscopeTextPublished
     *
     * @Route("/api/horoscope-texts/{textId}/published/", name="api-horoscopeTextPublished-postSingle", methods={"POST"})
     */
    public function postSingleHoroscopeTextPublished($textId, Request $request, ValidatorInterface $validator)
    {
        $horoscopeText = $this->em->getRepository(HoroscopeText::class)
            ->find($textId)
        ;

        if (!$horoscopeText) {
            return $this->jsonErrorResponse([
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => 'A keresett horoszkópszöveg nem található.',
            ], 404);
        }

        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return $this->jsonErrorResponse([
                'error' => ApiError::UNPROCESSABLE_ENTITY,
                'message' => 'Hibás JSON-adatok vagy formátum.',
            ], 422);
        }

        $normalizers = [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            new AstrologicalSignDenormalizer($this->em),
            new HoroscopeTextsPublishedDenormalizer($this->em),
        ];
        $serializer = new Serializer($normalizers, $this->encoders);

        /** @var HoroscopeTextPublished $newHoroscopeTextPublished */
        $newHoroscopeTextPublished = $serializer->deserialize($request->getContent(),HoroscopeTextPublished::class,'json',[
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
            'groups' => 'horoscopeText'
        ]);

        $date = $newHoroscopeTextPublished->getPublishDate();
        $sign = $newHoroscopeTextPublished->getAstrologicalSign();

        // if the publish date is at least one day in the past, don't allow insertion, instead return an error.
        $hasDateTesting = false;
        if ($hasDateTesting) {
            $interval = $date->diff(new \DateTime());
            if ($interval->days >= 1 && $interval->invert == 0) {
                return $this->jsonErrorResponse([
                    'error' => ApiError::UNPROCESSABLE_ENTITY,
                    'message' => 'Múltbéli publikálás nem lehetséges.',
                ], 422);
            }
        }


        $horoscopeTextPublishedList = $horoscopeText->getHoroscopeTextsPublished();
        foreach ($horoscopeTextPublishedList as $publishedItem) {
            if ($publishedItem->getPublishDate()->format(Enums::DATE_FORMAT) == $date->format(Enums::DATE_FORMAT)  && $publishedItem->getAstrologicalSign()->getSlug() == $sign->getSlug() ) {
                return $this->jsonErrorResponse([
                    'error' => ApiError::UNPROCESSABLE_ENTITY,
                    'message' => sprintf('Ez a publikálás már létezik, azaz van már ilyen publishDate (%s) és astrologicalSign (%s).', $date->format(Enums::DATE_FORMAT), $sign->getName()),
                ], 422);
            }
        }

        // Look for other published texts with same date and sign
        $anotherHoroscopeTexts = $this->findHoroscopeTextBy($date, $sign);
        if (!empty($anotherHoroscopeTexts)) {
            return $this->jsonErrorResponse([
                'error' => ApiError::UNPROCESSABLE_ENTITY,
                'message' => sprintf('Van már hasonló publikálás, azaz van már ilyen publishDate (%s) és astrologicalSign (%s).', $date->format(Enums::DATE_FORMAT), $sign->getName()),
            ], 422);
        }

        $newHoroscopeTextPublished->setHoroscopeText($horoscopeText);
        $horoscopeText->addHoroscopeTextPublished($newHoroscopeTextPublished);

        $validationErrors = $this->getValidationErrors($newHoroscopeTextPublished, $validator);
        if (!empty($validationErrors)) {
            $errorResponse = [
                'error' => 'Object validation error',
                'message' => 'Az új publikálás hibás.',
                'object' => $validationErrors
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 422, [],true);
        }

        $this->em->persist($newHoroscopeTextPublished);
        $this->em->flush();
        $this->em->refresh($horoscopeText);

        $json = $this->serializer->serialize($horoscopeText, 'json', [
            'groups' => 'horoscopeText',
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);
        return new JsonResponse($json, 200, [], true);
    }

    /**
     * DELETE SINGLE: Delete a HoroscopeTextPublished
     *
     * @Route("/api/horoscope-texts/{textId}/published/{publishedId}", name="api-horoscopeTextPublished-deleteSingle", methods={"DELETE"})
     */
    public function deleteSingleHoroscopeTextPublished($textId, $publishedId, Request $request, ValidatorInterface $validator)
    {
        $horoscopeText = $this->em->getRepository(HoroscopeText::class)->find($textId);
        if (!$horoscopeText) {
            return $this->jsonErrorResponse([
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => 'A keresett horoszkópszöveg nem található.',
            ], 404);
        }

        $isFound = false;
        $currentDate = new \DateTime();
        $horoscopeTextPublishedList = $horoscopeText->getHoroscopeTextsPublished();
        foreach ($horoscopeTextPublishedList as $published) {
            if ($published->getId() == $publishedId) {

                $dontDeletePastItems = true;
                if ($dontDeletePastItems) {
                    // if the publish date is at least one day in the past, don't allow deletion, instead return an error.
                    $interval = $published->getPublishDate()->diff($currentDate);
                    if ($interval->days >= 1 && $interval->invert == 0) {
                        return $this->jsonErrorResponse([
                            'error' => ApiError::UNPROCESSABLE_ENTITY,
                            'message' => 'Múltbéli publikálás már nem törölhető.',
                        ], 422);
                    }
                }

                $horoscopeText->removeHoroscopeTextPublished($published);
                $this->em->remove($published);
                $isFound = true;
            }
        }

        if ($isFound) {
            $this->em->persist($horoscopeText);
            $this->em->flush();
            return new JsonResponse('', 204, [], true);
        }

        return $this->jsonErrorResponse([
            'error' => ApiError::RESOURCE_NOT_FOUND,
            'message' => 'A keresett publikálás nem található.',
        ], 404);
    }


    /**
     * GET LIST: Retrieve a list of Tag items
     *
     * @Route("/api/tags/", name="api-tags-getList", methods={"GET"})
     */
    public function tagGetList(Request $request)
    {
        $signs = $this->em->getRepository(Tag::class)
            ->findAll()
        ;

        if (empty($signs)) {
//        if (true) {
            return $this->jsonErrorResponse([
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => 'Nem talált cimkéket.',
            ], 404);
        }

        $json = $this->serializer->serialize($signs, 'json', [
            'groups' => 'tags',
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * POST SINGLE: Create a new Tag and assign it to the HoroscopeText
     *
     * @Route("/api/horoscope-texts/{textId}/tags/", name="api-horoscopeTexts-addTag-postNewTag", methods={"POST"})
     */
    public function tagPostSingle($textId, Request $request, ValidatorInterface $validator)
    {
        $horoscopeText = $this->em->getRepository(HoroscopeText::class)
            ->find($textId)
        ;

        if (!$horoscopeText) {
            $errorResponse = [
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => 'A keresett horoszkópszöveg nem található.',
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 404, [], true);
        }

        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            $errorResponse = [
                'error' => 'Unprocessable entity.',
                'message' => 'Hibás JSON-adatok vagy formátum.',
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 422, [], true);
        }

        $normalizers = [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            new AstrologicalSignDenormalizer($this->em),
            new HoroscopeTextsPublishedDenormalizer($this->em),
            new TagDenormalizer($this->em),
        ];
        $serializer = new Serializer($normalizers, $this->encoders);

        /** @var Tag $currentTag */
        $currentTag = $serializer->deserialize($request->getContent(),Tag::class,'json',[
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
            'groups' => 'horoscopeText'
        ]);

        // Check if tag content ('name') is already in db.
        $foundTag = $this->em->getRepository(Tag::class)->findOneBy(['name' => $currentTag->getName()]);
        if ($foundTag) {
            $currentTag = $foundTag;
        } else {
            $validationErrors = $this->getValidationErrors($currentTag, $validator);
            if (!empty($validationErrors)) {
                $errorResponse = [
                    'error' => 'Object validation error',
                    'message' => 'Az új címke hibás.',
                    'object' => $validationErrors
                ];

                $json = json_encode($errorResponse);
                return new JsonResponse($json, 422, [],true);
            }
        }

        $currentTag->addHoroscopeText($horoscopeText);
        $horoscopeText->addTag($currentTag); /// ez kell ez a sor???

        $this->em->persist($horoscopeText);
        $this->em->persist($currentTag);
        $this->em->flush();
        $this->em->refresh($horoscopeText);

        $json = $this->serializer->serialize($horoscopeText, 'json', [
            'groups' => 'horoscopeText',
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);
        return new JsonResponse($json, 200, [], true);
    }


    /**
     * DELETE SINGLE: Remove a Tag from a HoroscopeText, and delete it if not linked to other HoroscopeTexts.
     *
     * @Route("/api/horoscope-texts/{textId}/tags/{tagId}", name="api-horoscopeTexts-removeTag", methods={"DELETE"})
     */
    public function tagRemoveSingle($textId, $tagId, Request $request, ValidatorInterface $validator)
    {
        $horoscopeText = $this->em->getRepository(HoroscopeText::class)->find($textId);
        if (!$horoscopeText) {
            $errorResponse = [
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => 'A keresett horoszkópszöveg nem található.',
            ];

            $json = $this->serializer->serialize($errorResponse, 'json');
            return new JsonResponse($json, 404, [], true);
        }

        $isFound = false;
        $tagList = $horoscopeText->getTags();
        foreach ($tagList as $tag) {
            if ($tag->getId() == $tagId) {
                $horoscopeText->removeTag($tag);
                $isFound = true;
            }
        }

        // Remove Tag if it doesn't belong to any HoroscopeText
        $tag = $this->em->getRepository(Tag::class)->find($tagId);
        $hasHoroscopeText = !$tag->getHoroscopeTexts()->isEmpty();
        if (!$hasHoroscopeText) {
            $this->em->remove($tag);
        }

        if ($isFound) {
            $this->em->persist($horoscopeText);
            $this->em->flush();
            return new JsonResponse('', 204, [], true);
        }

        $errorResponse = [
            'error' => ApiError::RESOURCE_NOT_FOUND,
            'message' => 'A keresett publikálás nem található.',
        ];

        $json = json_encode($errorResponse);
        return new JsonResponse($json, 404, [], true);
    }

    /**
     * GET LIST: Retrieve a list of HoroscopeTextPublished items
     *
     * @Route("/api/horoscope-text-published/", name="api-horoscopeTextPublished-getList", methods={"GET"})
     */
    public function horoscopeTextPublishedGetList(Request $request)
    {
        $dateString = $request->query->get('publishDate', null);

        if ($dateString) {
            $publishDate = \DateTime::createFromFormat(Enums::DATE_FORMAT, $dateString);
            if ($publishDate instanceof \DateTime) {
                $publishDate->setTime(0, 0, 0);
            }

            $publishedTexts = $this->em
                ->getRepository(HoroscopeTextPublished::class)
                ->findBy(['publishDate' => $publishDate], ['astrologicalSign' => 'ASC'])
            ;
        } else {
            $publishedTexts = $this->em
                ->getRepository(HoroscopeTextPublished::class)
                ->findBy([], ['publishDate' => 'ASC', 'astrologicalSign' => 'ASC', ])
//                ->findAll()
            ;
        }

        if (empty($publishedTexts)) {
            $errorResponse = [
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => 'Nem talált publikálásokat.',
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 404, [], true);
        }

        $json = $this->serializer->serialize($publishedTexts, 'json', [
            'groups' => 'horoscopeTextPublished',
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);

        return new JsonResponse($json, 200, [], true);
    }

    /**
     * GET LIST: Retrieve a list of HoroscopeTextPublished items
     *
     * @Route("/api/horoscope-texts/NOT_IN_USE", name="api-horoscopeTexts-getListByPublishDate", methods={"GET"})
     */
    public function horoscopeTextGetListByPublishDate(Request $request)
    {
        $dateString = $request->query->get('publishDate', null);

        if ($dateString) {
            $publishDate = \DateTime::createFromFormat(Enums::DATE_FORMAT, $dateString);
            if ($publishDate instanceof \DateTime) {
                $publishDate->setTime(0, 0, 0);
            }

            $horoscopeTexts = $this->em
                ->getRepository(HoroscopeText::class)
                ->createQueryBuilder('h')
                ->leftJoin('h.horoscopeTextsPublished', 'htp')
                ->andWhere('htp.publishDate = :publishDate')
                ->setParameter('publishDate', $publishDate)
                ->getQuery()
                ->getResult()
            ;
        } else {
            $publishedTexts = $this->em
                ->getRepository(HoroscopeTextPublished::class)
                ->findBy([], ['publishDate' => 'ASC', 'astrologicalSign' => 'ASC', ])
//                ->findAll()
            ;
        }

        if (empty($publishedTexts)) {
            $errorResponse = [
                'error' => ApiError::RESOURCE_NOT_FOUND,
                'message' => 'Nem talált publikálásokat.',
            ];

            $json = json_encode($errorResponse);
            return new JsonResponse($json, 404, [], true);
        }

        $json = $this->serializer->serialize($publishedTexts, 'json', [
            'groups' => 'horoscopeTextPublished',
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
        ]);

        return new JsonResponse($json, 200, [], true);
    }

    private function findHoroscopeTextBy(\DateTime $publishDate, AstrologicalSign $sign)
    {
        if ($publishDate instanceof \DateTime) {
            $publishDate->setTime(0, 0, 0);
        }

        $horoscopeText = $this->em
            ->getRepository(HoroscopeText::class)
            ->createQueryBuilder('h')
            ->leftJoin('h.horoscopeTextsPublished', 'htp')
            ->andWhere('htp.publishDate = :publishDate')
            ->setParameter('publishDate', $publishDate)
            ->andWhere('htp.astrologicalSign = :sign')
            ->setParameter('sign', $sign)
            ->getQuery()
//            ->getOneOrNullResult()
            ->getResult()
        ;

        return $horoscopeText;
    }











    /**
     * @Route("/api/horoscope-final/", name="api-horoscopeFinal", methods={"GET"})
     */
    public function showDates(SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $locale = 'hu';
        $finalHoroscopes = $em->getRepository(HoroscopeFinal::class)->createQueryBuilder('e')
            ->select('e.date')
            ->where('e.locale = :locale')
            ->setParameter('locale', $locale)
            ->groupBy('e.date')
            ->orderBy('e.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;

//        dd($finalHoroscopes);

//        $json = $serializer->serialize($finalHoroscopes, 'json', [
//            'groups' => 'horoscopeList',
//            DateTimeNormalizer::FORMAT_KEY => 'Y-m-d',
//        ]);

        $json = $this->serializer->serialize($finalHoroscopes, 'json', [
            'groups' => 'horoscopeList',
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
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
    public function updateHoroscope(Request $request, SerializerInterface $serializer) //array $bundledHoroscopes,
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
////            new ObjectNormalizer($classMetadataFactory,null,null, new PhpDocExtractor()),
//            new HoroscopeBundledDenormalizer($this->em),
//            new HoroscopeFinalDenormalizer($this->em),
//            new AstrologicalSignDenormalizer($this->em),
//            new ArrayDenormalizer(),
//        ];
//        $serializer = new Serializer($normalizers, [new JsonEncoder()]);


        /** @var HoroscopeBundled $bundled */
        $bundled = $serializer->deserialize($request->getContent(),HoroscopeBundled::class,'json',[
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
//            AbstractNormalizer::OBJECT_TO_POPULATE => $bundled,
//            AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
//            'skip_null_values' => true,
        ]);
//        dd($bundled);

//        $bundled->getBase()->setDate($bundled->getDate());
//        $bundled->getBase()->setAstrologicalSign($bundled->getAstrologicalSign());
//        $bundled->getBase()->setLocale($bundled->getLocale());
//        $bundled->getBase()->setType('base');
//        if ($bundled->getAddendum()) {
//            $bundled->getAddendum()->setDate($bundled->getDate());
//            $bundled->getAddendum()->setAstrologicalSign($bundled->getAstrologicalSign());
//            $bundled->getAddendum()->setLocale($bundled->getLocale());
//            $bundled->getAddendum()->setType('addendum');
//        }


        $errors = $this->getValidationErrors($bundled->getBase(), $this->validator);
        if (!empty($errors)) {
            return new JsonResponse(json_encode($errors), 422, [],true);
        }

//        dd($errors);

        $this->em->persist($bundled->getBase());
        if ($bundled->getAddendum()) {
            $this->em->persist($bundled->getAddendum());
        }
        $this->em->flush();

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizers = [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            new ObjectNormalizer($classMetadataFactory,null,null, new PhpDocExtractor()),

        ];
        $serializer = new Serializer($normalizers, [new JsonEncoder()]);

        $json = $serializer->serialize($bundled, 'json', [
            'groups' => ['bundled'],
            DateTimeNormalizer::FORMAT_KEY => Enums::DATE_FORMAT,
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
                $error = new EntityValidationError($violation->getPropertyPath(), $violation->getMessage());
                $errors[] = $error;
            }
            return $errors;
        }
        return [];
    }
}
