<?php

namespace App\Form;

use App\Entity\ParsingInstance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParsingInstanceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rootName', TextType::class, [
                'attr'     => ['class' => 'w-full p-2 border border-gray-300 rounded-md', 'placeholder' => 'Enter root name here'],
                'required' => false,
            ])
            ->add('jsonInput', TextareaType::class, [
                'mapped'   => false,
                'attr'     => ['rows' => '10', 'class' => 'w-full p-2 border border-gray-300 rounded-md', 'placeholder' => 'Enter JSON here'],
                'required' => false,
            ]);
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParsingInstance::class,
        ]);
    }
}
