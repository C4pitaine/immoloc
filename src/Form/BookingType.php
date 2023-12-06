<?php

namespace App\Form;

use App\Entity\Booking;
use App\Form\ApplicationType;
use App\Form\DataTransformer\FrenchToDateTimeTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BookingType extends ApplicationType
{
    
    public function __construct(
        private FrenchToDateTimeTransformer $transformer
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('startDate', DateType::class, $this->getConfiguration("Date d'arrivée", "La date à laquelle vous comptez arriver",[
            //     "widget" => "single_text"
            // ]))
            // ->add('endDate', DateType::class, $this->getConfiguration("Date de départ du bien", "La date à laquelle vous comptez partir du bien réservé",[
            //     "widget" => "single_text"
            // ]))
            ->add('startDate', TextType::class, $this->getConfiguration("Date d'arrivée", "La date à laquelle vous comptez arriver"))
            ->add('endDate', TextType::class, $this->getConfiguration("Date de départ du bien", "La date à laquelle vous comptez partir du bien réservé"))
            ->add('comment', TextareaType::class, $this->getConfiguration(false, "Si vous avez un commentaire, n'hésitez pas à en faire part",[
                "required" => false
            ]))
        ;

        $builder->get('startDate')->addModelTransformer($this->transformer);
        $builder->get('endDate')->addModelTransformer($this->transformer);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
            'validation_groups' => [
                'Default',
                'Front'
            ]
        ]);
    }
}
