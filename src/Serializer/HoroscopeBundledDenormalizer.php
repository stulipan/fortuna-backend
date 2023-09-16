<?php

namespace App\Serializer;

use App\Entity\AstrologicalSign;
use App\Entity\HoroscopeBundled;
use App\Entity\HoroscopeFinal;
use App\Entity\HoroscopeRaw;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class HoroscopeBundledDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     * @return HoroscopeBundled
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $object = new HoroscopeBundled();

        if (isset($data['date'])) {
            $date = (new \DateTime($data['date']));
            $object->setDate($date);
        }

        if (isset($data['astrologicalSign'])) {
            $sign = $this->denormalizer->denormalize($data['astrologicalSign'],AstrologicalSign::class, $format, $context);
            $object->setAstrologicalSign($sign);
        }

        if (isset($data['locale'])) {
            $object->setLocale($data['locale']);
        }

        if (isset($data['base'])) {
            $base = $this->denormalizer->denormalize($data['base'],HoroscopeFinal::class, $format, $context);
            $object->setBase($base);
        }
        if (isset($data['addendum']) && $data['addendum'] !== null) {
            $addendum = $this->denormalizer->denormalize($data['addendum'],HoroscopeFinal::class, $format, [
                'astrologicalSign' => $object->getAstrologicalSign(),
                'date' => $object->getDate(),
                'locale' => $object->getLocale(),
            ]);
            $object->setAddendum($addendum);
        }
        if (isset($data['raw'])) {
            $raw = $this->denormalizer->denormalize($data['raw'],HoroscopeRaw::class, $format, $context);
            $object->setRaw($raw);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != HoroscopeBundled::class) {
            return false;
        }
        return true;
    }
}
