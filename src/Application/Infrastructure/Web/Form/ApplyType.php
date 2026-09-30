<?php

declare(strict_types=1);

namespace App\Application\Infrastructure\Web\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ApplyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'Full name is required.'])],
                'label' => 'Full Name',
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'A valid email is required.']),
                    new Email(['message' => 'A valid email is required.']),
                ],
                'label' => 'Email',
            ])
            ->add('phone', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'Phone is required.'])],
                'label' => 'Phone',
            ])
            ->add('position', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'Position is required.'])],
                'label' => 'Position',
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'label' => 'Notes',
            ])
            ->add('cvText', TextareaType::class, [
                'constraints' => [new NotBlank(['message' => 'CV text is required.'])],
                'label' => 'CV',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
