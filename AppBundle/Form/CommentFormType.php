<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, array(
                'label' => 'Коментар',
                'attr' => array(
                    'class' => 'form-control input_box'
                )
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'ИЗПРАТИ',
                'attr' => array(
                    'id' => 'saveButton'
                )
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Comment',
            'attr' => [
                'class' => 'comment_box',
                'id' => 'comment_box',
            ]
        ));
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
