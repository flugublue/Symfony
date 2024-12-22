<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class,[
                'attr' =>['Class' => 'form-control'],
                'label_attr' => ['Class' => 'form-label mt-2']
            ])
            ->add('texte', TextareaType::class, [
                'attr' =>['Class' => 'form-control'],
                'label_attr' => ['Class' => 'form-label mt-2']
            ])
            ->add('public',CheckboxType::class,[
                'attr' =>['Class' => 'form-check-input'],
                'label_attr' => ['Class' => 'form-label mt-1'],
                'required' => false
                ])
            ->add('date', DateTimeType::class ,[
                'attr' =>['Class' => 'form-control'],
                'label_attr' => ['Class' => 'form-label mt-1'],
                'widget' => 'single_text',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label mt-2'],
                'label' => 'Catégorie',
                'required' => false, 
            ])
            ->add('image', FileType::class, [
                'attr' => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label mt-2'],
                'required' => false,
                'data_class' => null,
                'help' => $options['data']->getImage() ? 
                          'Image actuelle : ' . $options['data']->getImage() : null,
            ])
            ->add('deleteImage', CheckboxType::class, [
                'mapped' => false, 
                'required' => false,
                'label' => 'Supprimer l\'image actuelle',
                'attr' => ['class' => 'form-check-input mt-2'],
            ])
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
