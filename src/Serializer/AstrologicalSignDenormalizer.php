<?php

namespace App\Serializer;

use App\Entity\AstrologicalSign;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AstrologicalSignDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     * @return AstrologicalSign
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (isset($data['id']) && $data['id'] !== null) {
            $object = $this->em->find(AstrologicalSign::class, $data['id']);
            return $object;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != AstrologicalSign::class) {
            return false;
        }
        return true;
    }
}
