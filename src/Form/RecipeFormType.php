<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\Region;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

final class RecipeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Recipe name'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false, 'attr' => ['rows' => 4]])
            ->add('region', EntityType::class, [
                'class' => Region::class,
                'choice_label' => 'name',
                'label' => 'Region',
                'placeholder' => 'Select a region',
                'required' => false,
            ])
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulty',
                'choices' => ['Easy' => 'Easy', 'Medium' => 'Medium', 'Hard' => 'Hard'],
                'placeholder' => 'Choose difficulty',
                'required' => false,
            ])
            ->add('prepTime', IntegerType::class, ['label' => 'Prep time (min)', 'required' => false])
            ->add('cookTime', IntegerType::class, ['label' => 'Cook time (min)', 'required' => false])
            ->add('servings', IntegerType::class, ['label' => 'Servings', 'required' => false])
            ->add('imageFile', FileType::class, [
                'label' => 'Photo (JPG, PNG, WebP — max 2 MB)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        extensions: ['jpg', 'jpeg', 'png', 'webp'],
                        extensionsMessage: 'Please upload a JPG, PNG, or WebP image.',
                    ),
                ],
            ])
            ->add('recipeIngredients', CollectionType::class, [
                'entry_type' => RecipeIngredientFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Ingredients',
            ])
            ->add('steps', CollectionType::class, [
                'entry_type' => StepFormType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Steps',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Recipe::class]);
    }
}
