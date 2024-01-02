<?php

namespace App\Serializer;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TagDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     * @return Tag
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        if (isset($data['id']) && $data['id'] !== null) {
            $object = $this->em->find(Tag::class, $data['id']);
        } else {
            $object = new Tag();
        }

        if (isset($data['name']) && $data['name'] !== null) {
            $object->setName($data['name']);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        if ($type != Tag::class) {
            return false;
        }
        return true;
    }
}
