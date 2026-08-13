<?php

namespace App\Form;

use App\Entity\SiteSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('address', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['rows' => 2],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'required' => false,
            ])
            ->add('publicationDirector', TextType::class, [
                'label' => 'Directeur·rice de la publication',
                'required' => false,
                'help' => 'Affiché dans les mentions légales.',
            ])
            ->add('gaEnabled', CheckboxType::class, [
                'label' => 'Activer Google Analytics',
                'required' => false,
                'help' => 'Le suivi ne démarre qu\'après que le visiteur a accepté le bandeau de consentement.',
            ])
            ->add('gaMeasurementId', TextType::class, [
                'label' => 'Identifiant de mesure (Measurement ID)',
                'required' => false,
                'help' => 'Format G-XXXXXXXXXX, disponible dans Google Analytics 4 sous Administration > Flux de données.',
                'attr' => ['placeholder' => 'G-XXXXXXXXXX'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SiteSettings::class,
        ]);
    }
}
