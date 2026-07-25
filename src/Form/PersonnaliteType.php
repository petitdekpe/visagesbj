<?php

namespace App\Form;

use App\Entity\Personnalite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
                'empty_data' => '',
                'help' => 'Laisser vide pour un nom de scène / mononyme (ex. "Tchif").',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('role', TextareaType::class, [
                'label' => 'Rôle (résumé court, affiché sur les cartes et la grille)',
                'required' => false,
                'attr' => ['rows' => 2],
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Biographie (page détail de la personnalité)',
                'required' => false,
                'attr' => ['rows' => 8],
                'help' => 'Un paragraphe par ligne vide. Pour la mise en emphase : '
                    .'<code>&lt;strong&gt;texte&lt;/strong&gt;</code> = jaune, '
                    .'<code>&lt;em&gt;texte&lt;/em&gt;</code> = blanc gras.',
                'help_html' => true,
            ])
            ->add('wikipediaUrl', UrlType::class, [
                'label' => 'Page Wikipédia',
                'required' => false,
                'help' => 'Laisser vide si la personnalité n\'a pas de page Wikipédia.',
            ])
            ->add('achievements', TextareaType::class, [
                'label' => 'Réalisations / distinctions (3 maximum, affichées sous le lien Wikipédia)',
                'required' => false,
                'attr' => ['rows' => 4],
                'help' => 'Une par ligne. Format : <code>Texte</code> ou <code>Texte | URL</code> '
                    .'(URL optionnelle — ex. un clip YouTube pour un·e artiste).',
                'help_html' => true,
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
