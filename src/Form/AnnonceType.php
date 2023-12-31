<?php

namespace App\Form;

use App\Entity\Ad;
use App\Form\ApplicationType;
use App\Form\ImageType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AnnonceType extends ApplicationType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [ //nom de l'input , type text puis tableau d'option
                'label' => "Titre", // on lui donne l'option label
                'attr' => [ // on lui donne l'option attr et dedans on met un tableau d'option
                    'placeholder' => "Titre de votre annonce", // on lui donne l'option placeholder ( on pourrait lui donner directement des classes dedans ( exemple : 'class' => 'form-control')
                ]
            ])
            ->add('slug',TextType::class, $this->getConfiguration('Slug','Adresse web (automatique)',[ // même chose que pour add(title) mais fait avec une function qu'on a crée ( getConfiguration())
                'required' => false // permet de ne pas devoir remplir le champ pour pouvoir envoyer le form ( completez ce champ est désactivé)
            ]))
            ->add('coverImage', UrlType::class, $this->getConfiguration("Url de l'image","Donnez l'adresse de votre image"))
            ->add('introduction', TextType::class,$this->getConfiguration("Introduction","Donnez une description globale de votre annonce"))
            ->add('content',TextareaType::class,$this->getConfiguration("Description détaillée","Donnez une description de votre bien"))
            ->add('rooms',IntegerType::class,$this->getConfiguration("Nombre de chambre","Donnez le nombre de chambres disponibles"))
            ->add('price',MoneyType::class,$this->getConfiguration("Prix par nuit","Indiquez le prix que vous voulez pour une nuit"))
            // pour les images associès à l'annonce : 
            ->add('images', CollectionType::class, [
                'entry_type' => ImageType::class, // on vient chercher le formulaire qu'on a créer pour les images
                'allow_add' => true, // pour le data_prototype
                'allow_delete' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
