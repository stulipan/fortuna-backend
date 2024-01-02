<?php

namespace App\Serializer;

use App\Entity\AstrologicalSign;
use App\Entity\HoroscopeTextPublished;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class HoroscopeTextsPublishedDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     * @return HoroscopeTextPublished
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (isset($data['id']) && $data['id'] !== null) {
            $object = $this->em->find(HoroscopeTextPublished::class, $data['id']);
        } else {
            $object = new HoroscopeTextPublished();
        }

        if (isset($data['astrologicalSign'])) {
            $sign = $this->denormalizer->denormalize($data['astrologicalSign'],AstrologicalSign::class, $format, $context);
            $object->setAstrologicalSign($sign);
        }

        if (isset($data['publishDate'])) {
            $publishDate = (new \DateTime($data['publishDate']));
            $object->setPublishDate($publishDate);
        }

        if (isset($data['note'])) {
            $object->setNote($data['note']);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != HoroscopeTextPublished::class) {
            return false;
        }
        return true;
    }
}
