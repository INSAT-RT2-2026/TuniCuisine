<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\RecipeComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RecipeCommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', TextareaType::class, [
            'label' => 'Your comment',
            'attr' => [
                'rows' => 5,
                'class' => 'recipe-comment-textarea',
                'placeholder' => 'Share your experience with this recipe…',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => RecipeComment::class]);
    }
}
