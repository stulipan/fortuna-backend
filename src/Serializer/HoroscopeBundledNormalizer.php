<?php

namespace App\Serializer;

use App\Entity\HoroscopeBundled;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HoroscopeBundledNormalizer  implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
//            'date'              => $object->getDate(),
//            'base'              => $this->normalizer->normalize($object->getBase(), $format, $context),
//            'addendum'          => $this->normalizer->normalize($object->getAddendum(), $format, $context),
//            'astrologicalSign'  => $this->normalizer->normalize($object->getAstrologicalSign(), $format, $context),
//            'locale'            => $object->getLocale(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof HoroscopeBundled;
    }
}
