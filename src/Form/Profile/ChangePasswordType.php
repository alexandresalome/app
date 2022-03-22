<?php

namespace App\Form\Profile;

use App\Entity\User;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordType extends AbstractType
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('old_password', PasswordType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('new_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The passwords do not match',
                'error_bubbling' => true,
                'first_options' => [
                    'label' => 'New password',
                ],
                'second_options' => [
                    'label' => 'Repeat new password',
                ],
                'mapped' => false,
                'required' => false,
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (PostSubmitEvent $event) {
                $form = $event->getForm();
                /** @var User $user */
                $user = $form->getData();

                $old = $form->get('old_password')->getData();
                $new = $form->get('new_password')->getData();

                if (!$old || !$new) {
                    return;
                }

                if (!$this->userPasswordHasher->isPasswordValid($user, $old)) {
                    $form->addError(new FormError('Bad credentials'));

                    return;
                }

                $password = $this->userPasswordHasher->hashPassword($user, $new);
                $user->setPassword($password);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
