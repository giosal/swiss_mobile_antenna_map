<?php

namespace App\Form;

use App\Entity\Antenna;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AntennaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('station')
            ->add('operator')
            ->add('type')
            ->add('power')
            ->add('support3G')
            ->add('support4G')
            ->add('support5G')
            ->add('adaptive')
            ->add('datafileDate', null, [
                'widget' => 'single_text'
            ])
            ->add('installationLimit')
            ->add('latitude')
            ->add('longitude')
            ->add('createdAt', null, [
                'widget' => 'single_text'
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text'
            ])
            ->add('submit', SubmitType::class, [])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Antenna::class,
        ]);
    }
}
