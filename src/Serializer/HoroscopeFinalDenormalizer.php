<?php

namespace App\Serializer;

use App\Entity\AstrologicalSign;
use App\Entity\HoroscopeFinal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class HoroscopeFinalDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     * @return HoroscopeFinal
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (isset($data['id'])) {
            $object = $this->em->find(HoroscopeFinal::class, $data['id']);

            if (isset($data['content'])) {
                if($data['content']) {
                    $object->setContent($data['content']);
                } else {
                    $this->em->remove($object);
                    $this->em->flush();
                    return null;
                }
            }

        } else {
            if (isset($data['content']) && $data['content']) {
                $object = new HoroscopeFinal();
                $object->setType('addendum');
                $object->setLocale($context['locale']);
                $astrologicalSign = $context['astrologicalSign'];
                $object->setAstrologicalSign($astrologicalSign);
                $object->setDate($context['date']);
                $object->setContent($data['content']);
            } else {
                $object = null;
            }
        }
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != HoroscopeFinal::class) {
            return false;
        }
        return true;
    }
}
