<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;


class ApplicationType extends AbstractType
{
    protected function getConfiguration(string $label, string $placeholder, array $options=[]): array
    {
        return array_merge_recursive([ // permet de fusionner des tableaux sans écraser attr entre le tableau créer ici et celui $options car fait de manière récursif
                'label' => $label,
                'attr' => [
                    'placeholder' => $placeholder
                ]
            ], $options
        );
    }
}   


?>