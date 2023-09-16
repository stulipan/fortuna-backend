<?php

namespace App\Serializer;

use App\Entity\AstrologicalSign;
use App\Entity\HoroscopeRaw;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class HoroscopeRawDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     * @return HoroscopeRaw
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (isset($data['id'])) {
            $object = $this->em->find(HoroscopeRaw::class, $data['id']);
        } else {
            $object = new HoroscopeRaw();
            $object->setAstrologicalSign($this->em->getRepository(AstrologicalSign::class)->find($data['astrologicalSign']->getId()));
        }
        if (isset($data['content']) && $data['content'] !== null) {
            $object->setContent($data['content']);
        }
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != HoroscopeRaw::class) {
            return false;
        }
        return true;
    }
}
