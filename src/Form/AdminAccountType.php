<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ApplicationType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminAccountType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class,$this->getConfiguration('Email',"L'email du User"))
            ->add('roles',ChoiceType::class,[
                'choices' => [
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_ADMIN' => 'ROLE_ADMIN'
                ],
                'multiple' => true
            ])
            ->add('firstName',TextType::class,$this->getConfiguration('Prénom',"Le prénom du User"))
            ->add('lastName',TextType::class,$this->getConfiguration('Nom',"Le nomdu User"))
            ->add('introduction', TextType::class,$this->getConfiguration("Introduction", "Présentation du User"))
            ->add('description', TextareaType::class,$this->getConfiguration("Description détaillée", "La description du User"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
