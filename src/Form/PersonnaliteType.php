<?php

namespace App\Form;

use App\Entity\Personnalite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PersonnaliteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'help' => 'Laisser vide pour un nom de scène / mononyme (ex. "Tchif").',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('role', TextareaType::class, [
                'label' => 'Rôle / bio courte',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position (ordre d\'affichage, plus petit = affiché en premier)',
                'required' => true,
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo',
                'required' => false,
                'mapped' => false,
                'help' => 'JPEG, PNG ou WebP, 5 Mo max. Laisser vide pour conserver la photo actuelle.',
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Merci de déposer une image JPEG, PNG ou WebP.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personnalite::class,
        ]);
    }
}
