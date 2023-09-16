<?php

namespace App\Serializer;

use App\Entity\HoroscopeFinal;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HoroscopeFinalNormalizer  implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
//            'id'                => $object->getId(),
//            'date'              => $object->getDate(),
//            'type'              => $object->getType(),
//            'dailyContent'      => $object->getDailyContent(),
//            'astrologicalSign'  => $this->normalizer->normalize($object->getAstrologicalSign(), $format, $context),
//            'content'           => $object->getContent(),
//            'locale'            => $object->getLocale(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof HoroscopeFinal;
    }
}
