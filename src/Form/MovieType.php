<?php

namespace App\Form;

use App\Entity\Genre;
use App\Entity\Movie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class MovieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('originalTitle')
            ->add('description')
            ->add('releaseYear')
            ->add('runtimeMinutes')
            ->add('director')
            ->add('posterFile', FileType::class, [
                'label' => 'Movie poster',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image(
                        maxSize: '5M',
                        extensions: ['jpg', 'jpeg', 'png', 'webp'],
                    ),
                ],
            ])
//            ->add('createdAt', null, [
//                'widget' => 'single_text'
//            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text'
            ])
            ->add('genres', EntityType::class, [
                'class' => Genre::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Movie::class,
        ]);
    }
}
