<?php

namespace App\Form;

use App\Entity\ParsingInstance;
use App\Enum\ExportTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportParsingInstanceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // exportType select type
        $builder->add('exportType', ChoiceType::class, [
            'label'    => 'Export type',
            'required' => true,
            'choices'  => ExportTypeEnum::getChoices(),
        ])->add('prefix', TextType::class, [
            'label'    => 'Prefix',
            'required' => false,
        ]);
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
//            'data_class' => ParsingInstance::class,
        ]);
    }
}
