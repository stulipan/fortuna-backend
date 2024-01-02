<?php

namespace App\Serializer;

use App\Entity\AstrologicalSign;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AstrologicalSignNormalizer  implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return [
//            'id'        => $object->getId(),
//            'name'      => $object->getName(),
////            'startDate' => $object->getStartDate(),
////            'endDate'   => $object->getEndDate(),
//            'slug'      => $object->getSlug(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AstrologicalSign;
    }
}
