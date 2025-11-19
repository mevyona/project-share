<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['attr' => ['class' => 'form-control'], 'label_attr' => ['class' =>
                'fw-bold']])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label_attr' => ['class' => 'fw-bold'],
                'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe.',
                    ]),
                    new Length([
                        'min' => 12,
                        'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères.',
                    ]),
                    new Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Votre mot de passe doit contenir au moins une lettre majuscule.',
                    ]),
                    new Regex([
                        'pattern' => '/[a-z]/',
                        'message' => 'Votre mot de passe doit contenir au moins une lettre minuscule.',
                    ]),
                    new Regex([
                        'pattern' => '/\d/',
                        'message' => 'Votre mot de passe doit contenir au moins un chiffre.',
                    ]),
                    new Regex([
                        'pattern' => '/[^a-zA-Z0-9]/',
                        'message' => 'Votre mot de passe doit contenir au moins un caractère spécial.',
                    ]),
                    new NotCompromisedPassword([
                        'message' => 'Ce mot de passe a déjà été compromis dans des fuites de données. Merci d’en choisir un autre.',
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, ['attr' => ['class' => 'form-control'], 'label_attr' => ['class' =>
                'fw-bold']])
            ->add('firstname', TextType::class, ['attr' => ['class' => 'form-control'], 'label_attr' => ['class' =>
                'fw-bold']])
            ->add('adresse', TextType::class, ['attr' => ['class' => 'form-control'], 'label_attr' => ['class' =>
                'fw-bold']])
            ->add('ville', TextType::class, ['attr' => ['class' => 'form-control'], 'label_attr' => ['class' =>
                'fw-bold']])
            ->add('code_postal', IntegerType::class, ['attr' => ['class' => 'form-control'], 'label_attr' => ['class' =>
                'fw-bold']])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'data' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
